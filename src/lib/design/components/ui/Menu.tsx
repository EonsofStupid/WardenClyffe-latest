import { MenuTrigger, Menu as AriaMenu, MenuItem, Popover } from "react-aria-components";
import type { ReactNode } from "react";
import "./Menu.css";

export interface MenuOption {
  id: string;
  label: ReactNode;
}

export interface MenuProps {
  /** the pressable that opens the menu (e.g. a Button) */
  trigger: ReactNode;
  items: MenuOption[];
  onAction?: (id: string) => void;
}

/** Menu — RAC popover menu. */
export function Menu({ trigger, items, onAction }: MenuProps) {
  return (
    <MenuTrigger>
      {trigger}
      <Popover className="ui-popover">
        <AriaMenu className="ui-menu" onAction={(k) => onAction?.(String(k))}>
          {items.map((i) => (
            <MenuItem key={i.id} id={i.id} className="ui-menuitem">
              {i.label}
            </MenuItem>
          ))}
        </AriaMenu>
      </Popover>
    </MenuTrigger>
  );
}
