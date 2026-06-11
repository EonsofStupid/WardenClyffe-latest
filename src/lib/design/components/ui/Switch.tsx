import { Switch as AriaSwitch } from "react-aria-components";
import type { ReactNode } from "react";
import "./Switch.css";

export interface SwitchProps {
  children?: ReactNode;
  isSelected?: boolean;
  defaultSelected?: boolean;
  isDisabled?: boolean;
  onChange?: (isSelected: boolean) => void;
}

/** Switch — RAC toggle with a token-styled track/thumb. */
export function Switch({ children, isSelected, defaultSelected, isDisabled, onChange }: SwitchProps) {
  return (
    <AriaSwitch
      className="ui-switch"
      isSelected={isSelected}
      defaultSelected={defaultSelected}
      isDisabled={isDisabled}
      onChange={onChange}
    >
      <span className="ui-switch__track">
        <span className="ui-switch__thumb" />
      </span>
      {children && <span className="ui-switch__label">{children}</span>}
    </AriaSwitch>
  );
}
