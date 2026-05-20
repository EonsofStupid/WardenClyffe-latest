# WardenClyffeDisk

🐺💾 **Distributed File System for Linux**

WardenClyffeDisk is a distributed file system that provides easy-to-use shared and replicated storage across Linux machines. Built on the same proven consensus mechanisms as [WardenClyffeScale](https://github.com/wardenclyffesoftwaresystemsltd/WardenClyffeScale).

## Features

- **Node Roles**: Leader, Follower, Client (mount-only), or Auto-election
- **Two Operating Modes**:
  - **Shared Mode**: Simple shared storage with single leader
  - **Replicated Mode**: Data replicated across N nodes for high availability
- **Auto-Discovery**: UDP multicast for automatic peer discovery on LAN
- **Client Mode**: Mount filesystem remotely without local storage
- **Easy Setup**: Interactive installer with configuration prompts
- **Content-Addressed Storage**: Automatic deduplication via SHA256 hashing
- **FUSE-Based**: Mount as a regular directory
- **Chunk-Based**: Large files split for efficient transfer and sync
- **S3-Compatible API**: Optional S3 gateway — access WardenClyffeDisk storage via any S3 client
- **IBM Power Ready**: Pure Rust dependencies, builds natively on ppc64le

## Quick Install

```bash
curl -sSL https://raw.githubusercontent.com/wardenclyffesoftwaresystemsltd/WardenClyffeScale/main/wardenclyffedisk/setup.sh | bash
```

The installer will prompt you for:
- **Node ID** — Unique identifier (defaults to hostname)
- **Role** — auto, leader, follower, or client
- **Bind IP address** — IP to listen on (auto-detected)
- **Discovery** — Auto-discovery, manual peers, or standalone
- **Mount path** — Where to mount the filesystem

## Node Roles

| Role | Storage | Replication | Use Case |
|------|---------|-------------|----------|
| **Leader** | ✅ Yes | Broadcasts to followers | Primary write node |
| **Follower** | ✅ Yes | Receives from leader | Read replicas, failover |
| **Client** | ❌ No | None (mount-only) | Access shared drive remotely |
| **Auto** | ✅ Yes | Dynamic election | Default - lowest ID becomes leader |

> 💡 **Client Mode**: Perfect for workstations that just need to access the shared filesystem without storing data locally.

## Manual Installation

### Prerequisites

- Linux with FUSE3 support
- Rust toolchain

> ⚠️ **Proxmox Users**: If running in an LXC container, you must enable FUSE in the container options: `Options → Features → FUSE`

```bash
# Ubuntu/Debian
sudo apt install libfuse3-dev fuse3

# Fedora/RHEL
sudo dnf install fuse3-devel fuse3
```

### Build

```bash
git clone https://github.com/wardenclyffesoftwaresystemsltd/WardenClyffeScale.git
cd WardenClyffeScale/wardenclyffedisk
cargo build --release
sudo cp target/release/wardenclyffedisk /usr/local/bin/
```

## Usage

### Initialize Data Directory

```bash
wardenclyffedisk init -d /var/lib/wardenclyffedisk
```

### Mount Filesystem

```bash
# Foreground (for testing)
sudo wardenclyffedisk mount -m /mnt/wardenclyffedisk

# As a service
sudo systemctl start wardenclyffedisk
```

### Check Status

```bash
wardenclyffedisk status
```

## Configuration

Edit `/etc/wardenclyffedisk/config.toml`:

```toml
[node]
id = "node1"
role = "auto"    # auto, leader, follower, or client
bind = "0.0.0.0:9500"
data_dir = "/var/lib/wardenclyffedisk"

[cluster]
# Auto-discovery (recommended for LAN)
discovery = "udp://239.255.0.1:9501"

# Or manual peers
# peers = ["192.168.1.10:9500", "192.168.1.11:9500"]

[replication]
mode = "shared"      # or "replicated"
factor = 3           # Copies for replicated mode
chunk_size = 4194304 # 4MB

[mount]
path = "/mnt/wardenclyffedisk"
allow_other = true

# Optional: S3-compatible API
[s3]
enabled = true
bind = "0.0.0.0:9878"
# access_key = "your-access-key"   # optional auth
# secret_key = "your-secret-key"   # optional auth
```

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    Linux Applications                        │
├─────────────────────────────────────────────────────────────┤
│                   mount /mnt/wardenclyffedisk                        │
├─────────────────────────────────────────────────────────────┤
│                     FUSE (fuser)                             │
├─────────────────────────────────────────────────────────────┤
│                   WardenClyffeDisk Core                              │
│   ┌───────────┐  ┌───────────┐  ┌─────────────────────────┐ │
│   │ File Index│  │  Chunks   │  │ Replication Engine      │ │
│   │ (metadata)│  │ (SHA256)  │  │ (leader election)       │ │
│   └───────────┘  └───────────┘  └─────────────────────────┘ │
│   ┌─────────────────────────────────────────────────────────┐ │
│   │             S3-Compatible API (optional)                │ │
│   │        ListBuckets / Get / Put / Delete Objects          │ │
│   └─────────────────────────────────────────────────────────┘ │
├─────────────────────────────────────────────────────────────┤
│      Network Layer: Discovery + Peer + Protocol              │
└─────────────────────────────────────────────────────────────┘
```

## S3-Compatible API

WardenClyffeDisk can optionally expose an S3-compatible REST API, allowing any S3 client to read and write files.

### Enabling S3

Add to `/etc/wardenclyffedisk/config.toml`:

```toml
[s3]
enabled = true
bind = "0.0.0.0:9878"
```

### How It Maps

| WardenClyffeDisk | S3 |
|----------|----|
| Top-level directory | Bucket |
| File in directory | Object |
| Nested directory | Object key prefix |

### Supported Operations

| Operation | Method | Path |
|-----------|--------|------|
| ListBuckets | GET | `/` |
| CreateBucket | PUT | `/bucket` |
| DeleteBucket | DELETE | `/bucket` |
| HeadBucket | HEAD | `/bucket` |
| ListObjectsV2 | GET | `/bucket?prefix=...` |
| GetObject | GET | `/bucket/key` |
| PutObject | PUT | `/bucket/key` |
| DeleteObject | DELETE | `/bucket/key` |
| HeadObject | HEAD | `/bucket/key` |

### Example

```bash
# Using AWS CLI
aws --endpoint-url http://localhost:9878 s3 ls
aws --endpoint-url http://localhost:9878 s3 cp file.txt s3://mybucket/file.txt
aws --endpoint-url http://localhost:9878 s3 ls s3://mybucket/

# Using curl
curl http://localhost:9878/mybucket/myfile.txt
curl -X PUT --data-binary @file.txt http://localhost:9878/mybucket/file.txt
```

Both FUSE and S3 access the **same underlying data** — files written via FUSE are instantly visible through S3 and vice versa.

## Leader Failover

WardenClyffeDisk automatically handles leader failures with fast failover:

1. **Heartbeat Detection** — Nodes monitor the leader with 2-second timeout
2. **Automatic Election** — Lowest node ID becomes the new leader
3. **Seamless Transition** — Followers continue serving reads during failover

### How Failover Works

```
Initial State:
  node-a (leader) ←→ node-b (follower) ←→ node-c (follower)

node-a goes down:
  ❌ node-a         node-b detects timeout (2s)
                    node-b becomes leader (next lowest ID)

node-a returns:
  node-a syncs from node-b (gets missed changes)
  node-a becomes leader again (lowest ID)
```

### Deterministic Election

- No voting or consensus delay
- Lowest node ID always wins
- Explicit role overrides: `role = "leader"` or `role = "follower"`

## Sync and Catchup

When a node starts or recovers from downtime, it automatically syncs with the leader:

1. **Version Tracking** — Every write increments the index version
2. **Delta Sync** — Follower sends "my version is X, give me changes since X"
3. **Incremental Updates** — Only modified/new/deleted files are transferred

### Example Sync Flow

```
Follower (version 45) → Leader: "SyncRequest(from_version=45)"
Leader (version 50)   → Follower: "SyncResponse(entries=[5 changes])"
Follower applies 5 changes, now at version 50
```

This ensures efficient catchup — a node that was down briefly only receives missed changes, not the entire index.

## Write Replication

When the leader writes a file:

1. **Local Write** — Leader stores chunks and updates index locally
2. **Broadcast** — Leader sends index update and chunks to all followers
3. **Apply** — Followers update their local index and store chunks

```
Application writes file.txt
        ↓
Leader: store chunks + update index (version++)
        ↓
Broadcast: IndexUpdate + StoreChunk to all followers
        ↓
Followers: apply updates, store chunks locally
```

No quorum required — lowest node ID is always leader.

## Read Caching

Followers cache chunks locally for fast reads:

1. **Cache Hit** — Chunk exists locally → serve immediately
2. **Cache Miss** — Request chunk from leader → cache locally → return

```
Follower read:
  chunk exists locally? → return data (fast)
  chunk missing? → fetch from leader → cache → return
```

## Client Mode (Thin Client)

Client mode mounts the filesystem without storing any data locally:

| Aspect | Leader/Follower | Client |
|--------|-----------------|--------|
| Local Storage | ✅ Stores data | ❌ No local storage |
| Reads | Local | Forwarded to leader |
| Writes | Local (leader) or forwarded | Forwarded to leader |
| Use Case | Data nodes | Workstations, containers |

### How Client Works

```
Application → /mnt/shared/file.txt
                    ↓
            WardenClyffeDisk Client
                    ↓ (network)
            Leader Node → reads/writes data
                    ↓
            Response → Application
```

Client mode is ideal for:
- Workstations accessing shared files
- Containers needing cluster access
- Read-heavy applications with low latency needs

## Commands

### wardenclyffedisk (service)

| Command | Description |
|---------|-------------|
| `wardenclyffedisk init` | Initialize data directory |
| `wardenclyffedisk mount -m PATH` | Mount the filesystem |
| `wardenclyffedisk unmount -m PATH` | Unmount the filesystem |
| `wardenclyffedisk status` | Show node configuration |

### wardenclyffediskctl (control utility)

| Command | Description |
|---------|-------------|
| `wardenclyffediskctl status` | Show live status from running service |
| `wardenclyffediskctl list servers` | List all discovered servers in the cluster |
| `wardenclyffediskctl stats` | Live cluster statistics (refreshes every second) |

## Systemd Service

```bash
# Start
sudo systemctl start wardenclyffedisk

# Status
sudo systemctl status wardenclyffedisk

# Logs
sudo journalctl -u wardenclyffedisk -f

# Enable at boot
sudo systemctl enable wardenclyffedisk
```

## Multi-Node Example

### Server 1 (will become leader - lowest ID)
```toml
[node]
id = "node-a"
role = "auto"
```

### Server 2-N (will become followers)
```toml
[node]
id = "node-b"  # Higher ID = follower
role = "auto"
```

### Workstation (client only - no storage)
```toml
[node]
id = "desktop"
role = "client"
```

## License

[MIT License](../LICENSE) — Free to use, modify, and distribute without restriction.

© 2024-2026 WardenClyffe Software Systems Ltd

---

*We hand code and use AI to assist with the development of this software.*
