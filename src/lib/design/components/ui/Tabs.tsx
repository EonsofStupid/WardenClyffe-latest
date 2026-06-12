import { Tabs as AriaTabs, TabList, Tab, TabPanel } from "react-aria-components";
import type { ReactNode } from "react";
import "./Tabs.css";

export interface TabItem {
  id: string;
  label: ReactNode;
  content: ReactNode;
}

export interface TabsProps {
  items: TabItem[];
  selectedKey?: string;
  onSelectionChange?: (key: string) => void;
}

/** Tabs — RAC tabs. Controlled via selectedKey/onSelectionChange. */
export function Tabs({ items, selectedKey, onSelectionChange }: TabsProps) {
  return (
    <AriaTabs
      className="ui-tabs"
      selectedKey={selectedKey}
      onSelectionChange={(k) => onSelectionChange?.(String(k))}
    >
      <TabList className="ui-tablist" aria-label="Sections">
        {items.map((i) => (
          <Tab key={i.id} id={i.id} className="ui-tab">
            {i.label}
          </Tab>
        ))}
      </TabList>
      {items.map((i) => (
        <TabPanel key={i.id} id={i.id} className="ui-tabpanel">
          {i.content}
        </TabPanel>
      ))}
    </AriaTabs>
  );
}
