export type ProxmoxStatus = {
  configured: boolean;
  host: string;
  port: number;
  node: string;
  reachable: boolean;
  version?: Record<string, unknown>;
  error?: string;
  message: string;
};

export type ProxmoxGuest = {
  node: string;
  kind: "qemu" | "lxc" | string;
  vmid: number;
  name: string;
  status: string;
  cpus?: number;
  maxmem?: number;
  maxdisk?: number;
  uptime?: number;
  template?: number;
  stable_label: string;
};

export type GuestActionResult = {
  action_request_id: string;
  kind: string;
  node: string;
  guest_kind: string;
  vmid: number;
  upid?: string;
  status: string;
  exit_status?: string;
  error?: string;
};
