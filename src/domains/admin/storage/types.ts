// Clyffe storage domain — types colocated with the domain.
// Mirrors the Go contract in services/storage-broker-client/internal/contract
// so the UI and Go stay parallel (React is the dumb view; Go drives).

export type Tier = "starter" | "pro" | "scale";
export type ReplicationMode = "shared" | "replicated";
export type VolumeState =
  | "requested"
  | "provisioning"
  | "active"
  | "suspended"
  | "deprovisioning"
  | "failed";

export interface VolumeSpec {
  tenant_id: string;
  tier: Tier;
  capacity_gb: number;
  replication: ReplicationMode;
  replication_factor: number;
  region?: string;
}

export interface Volume {
  id: string;
  tenant_id: string;
  spec: VolumeSpec;
  state: VolumeState;
  node_id?: string;
  data_dir?: string;
  s3_endpoint?: string;
  bucket?: string;
  mount_hint?: string;
  message?: string;
  created_at: string;
  updated_at: string;
}

export interface MountGrant {
  volume_id: string;
  protocol: "s3" | "fuse";
  s3_endpoint?: string;
  access_key?: string;
  secret_key?: string;
  bucket?: string;
  expires_at: string;
}

export interface PurchaseInput {
  tenant_id: string;
  tier: Tier;
  region?: string;
}

// Catalog mirrors contract.TierCatalog — the purchasable plans.
export interface TierInfo {
  tier: Tier;
  label: string;
  capacityGb: number;
  blurb: string;
}

export const TIERS: readonly TierInfo[] = [
  { tier: "starter", label: "Starter", capacityGb: 50, blurb: "50 GB · single-node" },
  { tier: "pro", label: "Pro", capacityGb: 250, blurb: "250 GB · 2× replicated" },
  { tier: "scale", label: "Scale", capacityGb: 1000, blurb: "1 TB · 3× replicated" },
] as const;
