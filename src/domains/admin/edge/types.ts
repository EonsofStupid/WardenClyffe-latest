// Admin · edge — types mirroring the Go contract (internal/edge).
export type PublicIPRole = "ingress" | "egress" | "exit" | "reserved";
export type PublicIPStatus = "active" | "reserved" | "released";

export type PublicIP = {
  id: string;
  address: string;
  provider: string | null;
  host_id: string | null;
  role: PublicIPRole;
  status: PublicIPStatus;
  label: string | null;
  note: string | null;
  created_at: string;
  updated_at: string;
};

export type CreatePublicIP = {
  address: string;
  role: PublicIPRole;
  status?: PublicIPStatus;
  provider?: string;
  label?: string;
  note?: string;
};

export type UpdatePublicIP = Partial<{
  role: PublicIPRole;
  status: PublicIPStatus;
  provider: string;
  host_id: string;
  label: string;
  note: string;
}>;
