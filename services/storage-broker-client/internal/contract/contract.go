// Package contract is the versioned Go<->Rust storage boundary.
//
// The web/control plane (Go) speaks these types; the local/data plane
// (wardenclyffedisk, Rust) is driven over a network/CLI contract — never via
// cgo/FFI. This package is the single source of truth for that seam, so the
// Driver implementation (internal/disk) and the application service
// (internal/volume) cannot drift from the wire shape.
package contract

import (
	"context"
	"time"
)

// ContractVersion is bumped on any breaking change to these types.
const ContractVersion = "1"

// Tier is a purchased plan. The catalog (code-owned, not AI) maps it to
// concrete capacity + replication.
type Tier string

const (
	TierStarter Tier = "starter"
	TierPro     Tier = "pro"
	TierScale   Tier = "scale"
)

// ReplicationMode mirrors wardenclyffedisk's replication modes.
type ReplicationMode string

const (
	ReplicationShared     ReplicationMode = "shared"     // single leader, followers sync
	ReplicationReplicated ReplicationMode = "replicated" // quorum writes
)

// VolumeState is the lifecycle of a managed per-tenant disk.
type VolumeState string

const (
	StateRequested      VolumeState = "requested"
	StateProvisioning   VolumeState = "provisioning"
	StateActive         VolumeState = "active"
	StateSuspended      VolumeState = "suspended"
	StateDeprovisioning VolumeState = "deprovisioning"
	StateFailed         VolumeState = "failed"
)

// VolumeSpec is desired state, derived from a purchase.
type VolumeSpec struct {
	TenantID          string          `json:"tenant_id"`
	Tier              Tier            `json:"tier"`
	CapacityGB        int             `json:"capacity_gb"`
	Replication       ReplicationMode `json:"replication"`
	ReplicationFactor int             `json:"replication_factor"`
	Region            string          `json:"region,omitempty"`
}

// Volume is a managed per-tenant disk = one wardenclyffedisk node projection.
type Volume struct {
	ID         string      `json:"id"`
	TenantID   string      `json:"tenant_id"`
	Spec       VolumeSpec  `json:"spec"`
	State      VolumeState `json:"state"`
	NodeID     string      `json:"node_id,omitempty"`     // wardenclyffedisk node id
	DataDir    string      `json:"data_dir,omitempty"`    // chunk store path on the node
	S3Endpoint string      `json:"s3_endpoint,omitempty"` // data-plane S3 URL
	Bucket     string      `json:"bucket,omitempty"`      // tenant bucket
	MountHint  string      `json:"mount_hint,omitempty"`  // how the customer maps it
	Message    string      `json:"message,omitempty"`     // last driver message
	CreatedAt  time.Time   `json:"created_at"`
	UpdatedAt  time.Time   `json:"updated_at"`
}

// MountGrant is short-lived credentials to map a volume to a workstation,
// replacing the static shared SMB box. Protocol is the customer's choice of
// the surfaces wardenclyffedisk exposes.
type MountGrant struct {
	VolumeID   string    `json:"volume_id"`
	Protocol   string    `json:"protocol"` // "s3" | "fuse"
	S3Endpoint string    `json:"s3_endpoint,omitempty"`
	AccessKey  string    `json:"access_key,omitempty"`
	SecretKey  string    `json:"secret_key,omitempty"`
	Bucket     string    `json:"bucket,omitempty"`
	ExpiresAt  time.Time `json:"expires_at"`
}

// Driver is the seam to the Rust disk. Implementations MUST be deterministic
// (code/scripts), never AI in the control path.
type Driver interface {
	Provision(ctx context.Context, spec VolumeSpec) (*Volume, error)
	Status(ctx context.Context, volumeID string) (*Volume, error)
	Deprovision(ctx context.Context, volumeID string) error
	GrantMount(ctx context.Context, volumeID, protocol string) (*MountGrant, error)
}

// TierCatalog maps a purchased tier to a concrete spec. Code-owned, reviewed in
// git — the deterministic source of truth for what a purchase yields.
var TierCatalog = map[Tier]VolumeSpec{
	TierStarter: {Tier: TierStarter, CapacityGB: 50, Replication: ReplicationShared, ReplicationFactor: 1},
	TierPro:     {Tier: TierPro, CapacityGB: 250, Replication: ReplicationReplicated, ReplicationFactor: 2},
	TierScale:   {Tier: TierScale, CapacityGB: 1000, Replication: ReplicationReplicated, ReplicationFactor: 3},
}

// SpecForTier returns a spec for a tenant + tier from the catalog.
func SpecForTier(tenantID string, tier Tier, region string) (VolumeSpec, bool) {
	base, ok := TierCatalog[tier]
	if !ok {
		return VolumeSpec{}, false
	}
	base.TenantID = tenantID
	base.Region = region
	return base, true
}
