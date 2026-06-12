import {
  Select as AriaSelect, SelectValue, Button as AriaButton,
  Popover, ListBox, ListBoxItem, Label, type Key,
} from "react-aria-components";
import "./Select.css";

export interface SelectOption { id: string; label: string }
export interface SelectProps {
  label: string;
  selectedKey: string;
  onSelect: (key: string) => void;
  options: SelectOption[];
}

/** Select — labelled dropdown (RAC Select). */
export function Select({ label, selectedKey, onSelect, options }: SelectProps) {
  return (
    <AriaSelect
      className="ui-select"
      selectedKey={selectedKey}
      onSelectionChange={(k: Key | null) => k != null && onSelect(String(k))}
    >
      <Label className="ui-select__label">{label}</Label>
      <AriaButton className="ui-select__button"><SelectValue /><span aria-hidden>▾</span></AriaButton>
      <Popover className="ui-select__popover">
        <ListBox>
          {options.map((o) => (
            <ListBoxItem key={o.id} id={o.id} className="ui-select__option">{o.label}</ListBoxItem>
          ))}
        </ListBox>
      </Popover>
    </AriaSelect>
  );
}
