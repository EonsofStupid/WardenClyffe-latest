// Package dbadmin is WardenClyffe's own Go-based, Supabase-inspired Postgres
// management layer. It powers the UI-driven data browser: schemas, tables,
// columns, rows, and safe read-only SQL. It only exposes the WardenClyffe
// schemas and never executes mutating SQL.
package dbadmin

import (
	"context"
	"encoding/hex"
	"errors"
	"fmt"

	"github.com/jackc/pgx/v5"
	"github.com/jackc/pgx/v5/pgxpool"
)

// normalize converts driver-native values into JSON-friendly forms:
// uuid ([16]byte) -> canonical string, raw bytes -> string.
func normalize(v any) any {
	switch t := v.(type) {
	case [16]byte:
		s := hex.EncodeToString(t[:])
		return s[0:8] + "-" + s[8:12] + "-" + s[12:16] + "-" + s[16:20] + "-" + s[20:32]
	case []byte:
		return string(t)
	default:
		return v
	}
}

// managedSchemas is the allow-list the data browser may inspect.
var managedSchemas = map[string]bool{
	"warden_core": true, "warden_infra": true, "warden_audit": true,
	"clyffe_core": true, "clyffe_support": true, "clyffe_kb": true,
	"clyffe_crm": true, "ai_bridge": true,
}

var ErrForbiddenSchema = errors.New("schema is not managed by warden")
var ErrNotFound = errors.New("not found")

type Store struct{ db *pgxpool.Pool }

func NewStore(db *pgxpool.Pool) *Store { return &Store{db: db} }

type Schema struct {
	Name   string `json:"name"`
	Tables int    `json:"tables"`
}

type Table struct {
	Schema   string `json:"schema"`
	Name     string `json:"name"`
	Columns  int    `json:"columns"`
	RowsEst  int64  `json:"rows_estimate"`
	SizeByte int64  `json:"size_bytes"`
}

type Column struct {
	Name     string  `json:"name"`
	Type     string  `json:"type"`
	Nullable bool    `json:"nullable"`
	Default  *string `json:"default"`
	IsPK     bool    `json:"is_primary_key"`
	Position int     `json:"position"`
}

// ListSchemas returns the managed schemas with their table counts.
func (s *Store) ListSchemas(ctx context.Context) ([]Schema, error) {
	rows, err := s.db.Query(ctx, `
		SELECT n.nspname,
		       count(c.relname) FILTER (WHERE c.relkind IN ('r','p'))
		FROM pg_namespace n
		LEFT JOIN pg_class c ON c.relnamespace = n.oid
		WHERE n.nspname = ANY($1)
		GROUP BY n.nspname
		ORDER BY n.nspname`, managedSchemaList())
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	out := []Schema{}
	for rows.Next() {
		var s Schema
		if err := rows.Scan(&s.Name, &s.Tables); err != nil {
			return nil, err
		}
		out = append(out, s)
	}
	return out, rows.Err()
}

// ListTables returns tables in a managed schema with column counts, estimated
// row counts (from the planner stats) and on-disk size.
func (s *Store) ListTables(ctx context.Context, schema string) ([]Table, error) {
	if !managedSchemas[schema] {
		return nil, ErrForbiddenSchema
	}
	rows, err := s.db.Query(ctx, `
		SELECT c.relname,
		       (SELECT count(*) FROM information_schema.columns col
		          WHERE col.table_schema = n.nspname AND col.table_name = c.relname),
		       GREATEST(c.reltuples::bigint, 0),
		       pg_total_relation_size(c.oid)
		FROM pg_class c
		JOIN pg_namespace n ON n.oid = c.relnamespace
		WHERE n.nspname = $1 AND c.relkind IN ('r','p')
		ORDER BY c.relname`, schema)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	out := []Table{}
	for rows.Next() {
		t := Table{Schema: schema}
		if err := rows.Scan(&t.Name, &t.Columns, &t.RowsEst, &t.SizeByte); err != nil {
			return nil, err
		}
		out = append(out, t)
	}
	return out, rows.Err()
}

// GetColumns returns the column definitions for a managed table.
func (s *Store) GetColumns(ctx context.Context, schema, table string) ([]Column, error) {
	if !managedSchemas[schema] {
		return nil, ErrForbiddenSchema
	}
	rows, err := s.db.Query(ctx, `
		SELECT col.column_name,
		       col.data_type,
		       (col.is_nullable = 'YES'),
		       col.column_default,
		       col.ordinal_position,
		       COALESCE(pk.is_pk, false)
		FROM information_schema.columns col
		LEFT JOIN (
		    SELECT kcu.column_name, true AS is_pk
		    FROM information_schema.table_constraints tc
		    JOIN information_schema.key_column_usage kcu
		      ON kcu.constraint_name = tc.constraint_name
		     AND kcu.table_schema = tc.table_schema
		    WHERE tc.constraint_type = 'PRIMARY KEY'
		      AND tc.table_schema = $1 AND tc.table_name = $2
		) pk ON pk.column_name = col.column_name
		WHERE col.table_schema = $1 AND col.table_name = $2
		ORDER BY col.ordinal_position`, schema, table)
	if err != nil {
		return nil, err
	}
	defer rows.Close()
	out := []Column{}
	for rows.Next() {
		var c Column
		if err := rows.Scan(&c.Name, &c.Type, &c.Nullable, &c.Default, &c.Position, &c.IsPK); err != nil {
			return nil, err
		}
		out = append(out, c)
	}
	if err := rows.Err(); err != nil {
		return nil, err
	}
	if len(out) == 0 {
		return nil, ErrNotFound
	}
	return out, nil
}

// RowPage is a page of rows for a table.
type RowPage struct {
	Columns []string         `json:"columns"`
	Rows    []map[string]any `json:"rows"`
	Total   int64            `json:"total"`
	Limit   int              `json:"limit"`
	Offset  int              `json:"offset"`
}

// ListRows returns a safe, paginated page of rows. The schema/table are
// validated against the catalog and quoted; values are never interpolated.
func (s *Store) ListRows(ctx context.Context, schema, table string, limit, offset int) (*RowPage, error) {
	if !managedSchemas[schema] {
		return nil, ErrForbiddenSchema
	}
	if err := s.assertTableExists(ctx, schema, table); err != nil {
		return nil, err
	}
	if limit <= 0 || limit > 200 {
		limit = 50
	}
	if offset < 0 {
		offset = 0
	}
	ident := pgx.Identifier{schema, table}.Sanitize()

	var total int64
	if err := s.db.QueryRow(ctx, fmt.Sprintf(`SELECT count(*) FROM %s`, ident)).Scan(&total); err != nil {
		return nil, err
	}

	rows, err := s.db.Query(ctx, fmt.Sprintf(`SELECT * FROM %s ORDER BY 1 LIMIT $1 OFFSET $2`, ident), limit, offset)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	fds := rows.FieldDescriptions()
	cols := make([]string, len(fds))
	for i, fd := range fds {
		cols[i] = string(fd.Name)
	}

	page := &RowPage{Columns: cols, Rows: []map[string]any{}, Total: total, Limit: limit, Offset: offset}
	for rows.Next() {
		vals, err := rows.Values()
		if err != nil {
			return nil, err
		}
		m := make(map[string]any, len(cols))
		for i, c := range cols {
			m[c] = normalize(vals[i])
		}
		page.Rows = append(page.Rows, m)
	}
	return page, rows.Err()
}

// QueryResult is the result of a read-only ad-hoc query.
type QueryResult struct {
	Columns []string         `json:"columns"`
	Rows    []map[string]any `json:"rows"`
	Count   int              `json:"count"`
}

// RunReadOnly executes sql inside a READ ONLY transaction with a statement
// timeout. Any mutating statement (INSERT/UPDATE/DDL) fails at the database.
func (s *Store) RunReadOnly(ctx context.Context, sql string) (*QueryResult, error) {
	tx, err := s.db.BeginTx(ctx, pgx.TxOptions{AccessMode: pgx.ReadOnly})
	if err != nil {
		return nil, err
	}
	defer tx.Rollback(ctx)

	if _, err := tx.Exec(ctx, `SET LOCAL statement_timeout = '5s'`); err != nil {
		return nil, err
	}

	rows, err := tx.Query(ctx, sql)
	if err != nil {
		return nil, err
	}
	defer rows.Close()

	fds := rows.FieldDescriptions()
	cols := make([]string, len(fds))
	for i, fd := range fds {
		cols[i] = string(fd.Name)
	}
	res := &QueryResult{Columns: cols, Rows: []map[string]any{}}
	for rows.Next() {
		vals, err := rows.Values()
		if err != nil {
			return nil, err
		}
		m := make(map[string]any, len(cols))
		for i, c := range cols {
			m[c] = normalize(vals[i])
		}
		res.Rows = append(res.Rows, m)
	}
	if err := rows.Err(); err != nil {
		return nil, err
	}
	res.Count = len(res.Rows)
	return res, nil
}

func (s *Store) assertTableExists(ctx context.Context, schema, table string) error {
	var ok bool
	err := s.db.QueryRow(ctx, `
		SELECT EXISTS (
		  SELECT 1 FROM pg_class c JOIN pg_namespace n ON n.oid=c.relnamespace
		  WHERE n.nspname=$1 AND c.relname=$2 AND c.relkind IN ('r','p'))`,
		schema, table).Scan(&ok)
	if err != nil {
		return err
	}
	if !ok {
		return ErrNotFound
	}
	return nil
}

func managedSchemaList() []string {
	out := make([]string, 0, len(managedSchemas))
	for k := range managedSchemas {
		out = append(out, k)
	}
	return out
}
