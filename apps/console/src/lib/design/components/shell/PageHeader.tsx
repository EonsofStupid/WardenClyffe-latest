import type { ReactNode } from "react";
import "./shell.css";

export interface PageHeaderProps {
  title: ReactNode;
  subtitle?: ReactNode;
  actions?: ReactNode;
}

/** PageHeader — consistent page title + subtitle + right-aligned actions. */
export function PageHeader({ title, subtitle, actions }: PageHeaderProps) {
  return (
    <header className="ui-pageheader">
      <div className="ui-pageheader__text">
        <h1 className="ui-pageheader__title">{title}</h1>
        {subtitle && <p className="ui-pageheader__sub">{subtitle}</p>}
      </div>
      {actions && <div className="ui-pageheader__actions">{actions}</div>}
    </header>
  );
}
