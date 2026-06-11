import { TextField, Label, Input } from "react-aria-components";
import "./Field.css";

export interface FieldProps {
  label: string;
  value: string;
  onChange: (v: string) => void;
  placeholder?: string;
  type?: "text" | "password" | "email";
}

/** Field — labelled text input (RAC TextField). */
export function Field({ label, value, onChange, placeholder, type = "text" }: FieldProps) {
  return (
    <TextField className="ui-field" value={value} onChange={onChange} type={type}>
      <Label className="ui-field__label">{label}</Label>
      <Input className="ui-field__input" placeholder={placeholder} />
    </TextField>
  );
}
