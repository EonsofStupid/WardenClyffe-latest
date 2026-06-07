import type { ReactNode } from "react";
import "./shell.css";

export interface NavItemProps {
  children: ReactNode;
  active?: boolean;
  onPress?: () => void;
}

/** NavItem — sidebar navigation entry. Uses aria-current for the active route. */
export function NavItem({ children, active = false, onPress }: NavItemProps) {
  return (
    <button
      type="button"
      className="ui-navitem"
      data-active={active}
      aria-current={active ? "page" : undefined}
      onClick={onPress}
    >
      {children}
    </button>
  );
}
