import { useEffect, useState } from "react";
import { api, cell, type SchemaInfo, type TableInfo, type ColumnInfo, type RowPage, type QueryResult } from "../../../../lib/api";
import { Card, DataTable, Button, Badge } from "../../../../lib/design";

// Our own Go-backed, Supabase-inspired data browser.
export function DataBrowserView() {
  const [schemas, setSchemas] = useState<SchemaInfo[]>([]);
  const [schema, setSchema] = useState<string>("");
  const [tables, setTables] = useState<TableInfo[]>([]);
  const [table, setTable] = useState<string>("");
  const [cols, setCols] = useState<ColumnInfo[]>([]);
  const [page, setPage] = useState<RowPage | null>(null);
  const [sql, setSql] = useState("select kind, state, count(*) from shippin_infra.resources group by 1,2;");
  const [qres, setQres] = useState<QueryResult | null>(null);
  const [qerr, setQerr] = useState<string | null>(null);

  useEffect(() => { api.schemas().then((r) => { setSchemas(r.items); if (r.items[0]) setSchema(r.items[0].name); }); }, []);
  useEffect(() => { if (schema) api.tables(schema).then((r) => { setTables(r.items); setTable(""); setPage(null); setCols([]); }); }, [schema]);

  async function openTable(t: string) {
    setTable(t);
    const [c, p] = await Promise.all([api.columns(schema, t), api.rows(schema, t, 50, 0)]);
    setCols(c.columns);
    setPage(p);
  }

  async function runQuery() {
    try { setQerr(null); setQres(await api.query(sql)); }
    catch (e) { setQerr((e as Error).message); setQres(null); }
  }

  const pkSet = new Set(cols.filter((c) => c.is_primary_key).map((c) => c.name));

  return (
    <div>
      <h1 className="page-title">Data</h1>
      <p className="page-sub">Self-hosted Postgres, managed by our own Go layer — schemas, tables, rows, and read-only SQL.</p>

      <div style={{ display: "grid", gridTemplateColumns: "clamp(11rem,16vw,15rem) 1fr", gap: "1rem", alignItems: "start" }}>
        {/* schema + table tree */}
        <Card title="Schemas">
          <div style={{ display: "flex", flexDirection: "column", gap: "0.25rem" }}>
            {schemas.map((s) => (
              <button key={s.name} className="navitem" data-active={s.name === schema} onClick={() => setSchema(s.name)}>
                {s.name} <span className="muted">· {s.tables}</span>
              </button>
            ))}
          </div>
        </Card>

        <div style={{ display: "grid", gap: "1rem" }}>
          <Card title={`Tables in ${schema || "—"}`}>
            <div className="row">
              {tables.map((t) => (
                <button key={t.name} className="navitem" data-active={t.name === table} onClick={() => openTable(t.name)}>
                  {t.name} <span className="muted">· {t.columns} cols · ~{t.rows_estimate} rows</span>
                </button>
              ))}
              {tables.length === 0 && <span className="muted">No tables.</span>}
            </div>
          </Card>

          {table && (
            <Card title={`${schema}.${table}`} action={<span className="muted">{page ? `${page.total} rows` : ""}</span>}>
              <div className="row" style={{ marginBottom: "0.75rem", gap: "0.5rem" }}>
                {cols.map((c) => (
                  <Badge key={c.name} dot={false} tone={c.is_primary_key ? "success" : "neutral"}>{c.name}</Badge>
                ))}
              </div>
              {page && (
                <DataTable
                  columns={page.columns}
                  rows={page.rows}
                  render={(v, col) => {
                    if (v === null || v === undefined) return <span className="ui-null">null</span>;
                    const s = cell(v);
                    return pkSet.has(col) ? <span className="ui-pk">{s}</span> : s;
                  }}
                />
              )}
            </Card>
          )}

          <Card title="SQL runner (read-only)" action={<Button onClick={runQuery}>Run</Button>}>
            <textarea
              value={sql} onChange={(e) => setSql(e.target.value)} spellCheck={false}
              className="mono"
              style={{ width: "100%", minHeight: "5rem", background: "var(--wc-bg-elev)", color: "var(--wc-text)",
                       border: "1px solid var(--wc-border)", borderRadius: "var(--wc-radius-sm)", padding: "0.5rem", fontSize: "var(--wc-fs-xs)" }}
            />
            {qerr && <p className="muted" style={{ color: "var(--wc-danger)" }}>{qerr}</p>}
            {qres && <div style={{ marginTop: "0.75rem" }}>
              <DataTable columns={qres.columns} rows={qres.rows} render={(v) => v === null ? <span className="ui-null">null</span> : cell(v)} />
            </div>}
          </Card>
        </div>
      </div>
    </div>
  );
}
