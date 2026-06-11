import { Button as AriaButton, type ButtonProps as AriaButtonProps } from "react-aria-components";
import { cx, type Tone, type Size } from "../../utils/cx";
import "./Button.css";

export type ButtonVariant = "solid" | "soft" | "outline" | "ghost";

export interface ButtonProps extends AriaButtonProps {
  variant?: ButtonVariant;
  tone?: Tone;
  size?: Size;
}

/** Button — RAC button with variant × tone × size. Colors come from the
 *  semantic tone engine (OKLCH); no per-button bespoke CSS. */
export function Button({ variant = "solid", tone = "brand", size = "md", className, ...props }: ButtonProps) {
  return (
    <AriaButton
      {...props}
      data-variant={variant}
      data-tone={tone}
      data-size={size}
      className={cx("ui-button", typeof className === "string" ? className : undefined)}
    />
  );
}
