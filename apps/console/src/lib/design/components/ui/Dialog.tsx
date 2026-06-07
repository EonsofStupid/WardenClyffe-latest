import { DialogTrigger, Modal, ModalOverlay, Dialog as AriaDialog, Heading } from "react-aria-components";
import type { ReactNode } from "react";
import "./Dialog.css";

export interface DialogProps {
  title: ReactNode;
  /** the pressable that opens the dialog (e.g. a Button) */
  trigger: ReactNode;
  /** body; receives close() so actions can dismiss */
  children: ReactNode | ((close: () => void) => ReactNode);
}

/** Dialog — RAC modal dialog with overlay. */
export function Dialog({ title, trigger, children }: DialogProps) {
  return (
    <DialogTrigger>
      {trigger}
      <ModalOverlay className="ui-modal-overlay">
        <Modal className="ui-modal">
          <AriaDialog className="ui-dialog">
            {({ close }) => (
              <>
                <Heading slot="title" className="ui-dialog__title">
                  {title}
                </Heading>
                <div className="ui-dialog__body">
                  {typeof children === "function" ? children(close) : children}
                </div>
              </>
            )}
          </AriaDialog>
        </Modal>
      </ModalOverlay>
    </DialogTrigger>
  );
}
