import "./Table.css";

export interface DataTableProps {
  columns: string[];
  rows: Record<string, unknown>[];
  render: (value: unknown, column: string) => React.ReactNode;
  emptyText?: string;
}

/** DataTable — dense, scrollable data grid for the Supabase-style browser. */
export function DataTable({ columns, rows, render, emptyText = "No rows." }: DataTableProps) {
  return (
    <div className="ui-table__wrap">
      <table className="ui-table">
        <thead>
          <tr>{columns.map((c) => <th key={c}>{c}</th>)}</tr>
        </thead>
        <tbody>
          {rows.map((r, i) => (
            <tr key={i}>{columns.map((c) => <td key={c}>{render(r[c], c)}</td>)}</tr>
          ))}
        </tbody>
      </table>
      {rows.length === 0 && <div className="ui-table__empty">{emptyText}</div>}
    </div>
  );
}
