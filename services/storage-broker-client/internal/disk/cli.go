// Package disk implements contract.Driver by invoking the deterministic
// volume scripts (bin/wardenclyffedisk-volume*). The scripts own the actual
// wardenclyffedisk lifecycle (render config.toml, systemctl, bucket, creds) and
// emit JSON; Go parses it. No AI in this path — pure code + scripts.
package disk

import (
	"context"
	"encoding/json"
	"fmt"
	"os/exec"
	"strconv"
	"time"

	"github.com/wardenclyffe/storage-broker-client/internal/contract"
)

type CLIDriver struct {
	bin     string // path to wardenclyffedisk-volume
	timeout time.Duration
}

func NewCLIDriver(binDir string) *CLIDriver {
	return &CLIDriver{bin: binDir + "/wardenclyffedisk-volume", timeout: 120 * time.Second}
}

func (d *CLIDriver) run(ctx context.Context, out any, args ...string) error {
	ctx, cancel := context.WithTimeout(ctx, d.timeout)
	defer cancel()
	cmd := exec.CommandContext(ctx, d.bin, append(args, "--json")...)
	raw, err := cmd.Output()
	if err != nil {
		return fmt.Errorf("%s %v: %w", d.bin, args, err)
	}
	if out == nil {
		return nil
	}
	return json.Unmarshal(raw, out)
}

func (d *CLIDriver) Provision(ctx context.Context, spec contract.VolumeSpec) (*contract.Volume, error) {
	var v contract.Volume
	err := d.run(ctx, &v,
		"provision",
		"--tenant", spec.TenantID,
		"--tier", string(spec.Tier),
		"--capacity-gb", strconv.Itoa(spec.CapacityGB),
		"--replication", string(spec.Replication),
		"--replication-factor", strconv.Itoa(spec.ReplicationFactor),
		"--region", spec.Region,
	)
	if err != nil {
		return nil, err
	}
	return &v, nil
}

func (d *CLIDriver) Status(ctx context.Context, volumeID string) (*contract.Volume, error) {
	var v contract.Volume
	if err := d.run(ctx, &v, "status", "--id", volumeID); err != nil {
		return nil, err
	}
	return &v, nil
}

func (d *CLIDriver) Deprovision(ctx context.Context, volumeID string) error {
	return d.run(ctx, nil, "deprovision", "--id", volumeID)
}

func (d *CLIDriver) GrantMount(ctx context.Context, volumeID, protocol string) (*contract.MountGrant, error) {
	var g contract.MountGrant
	if err := d.run(ctx, &g, "grant-mount", "--id", volumeID, "--protocol", protocol); err != nil {
		return nil, err
	}
	return &g, nil
}
