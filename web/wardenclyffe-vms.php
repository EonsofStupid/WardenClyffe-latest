<?php
$page_title = '🖥️ Virtual Machines — WardenClyffe Docs';
$page_desc = 'Create, manage, and run KVM/QEMU virtual machines on any WardenClyffe or Proxmox node — ISO boot, VNC console, disk management, and WardenClyffeNet networking.';
$active = 'wardenclyffe-vms.php';
include 'includes/head.php';
?>
<body>
<div class="wiki-layout">
    <?php include 'includes/sidebar.php'; ?>
    <main class="wiki-content">

            <div class="content-section">
                <h2>Overview</h2>
                <img src="images/screenshots/vms.png" alt="WardenClyffe virtual machine management with USB/PCI passthrough" class="screenshot" loading="lazy" style="border-radius:12px;border:1px solid var(--border-color);margin:1.5rem 0;">
                <p>WardenClyffe lets you create and manage KVM/QEMU virtual machines directly from the web dashboard. VMs work on <strong>native WardenClyffe nodes</strong> (any Linux server with KVM support), <strong>libvirt/virsh nodes</strong>, and <strong>Proxmox VE nodes</strong> &mdash; all managed through a single interface.</p>

                <h3>Key Features</h3>
                <ul>
                    <li><strong>Create VMs</strong> &mdash; Specify CPU cores, memory, disk size, and boot ISO</li>
                    <li><strong>ISO &amp; IMG boot</strong> &mdash; Boot from ISO (CD-ROM) or .img files (virtual USB)</li>
                    <li><strong>Import disk images</strong> &mdash; Import .img, .qcow2, .vmdk, .vdi, or .vhd files as ready-to-run VMs</li>
                    <li><strong>VNC console</strong> &mdash; Graphical console access from the browser (works with Windows, Linux, and any OS)</li>
                    <li><strong>Disk management</strong> &mdash; Resize disks, add extra storage volumes, choose disk bus (virtio, IDE, SATA)</li>
                    <li><strong>Multi-NIC support</strong> &mdash; Add multiple network interfaces for firewalls (OPNsense, pfSense), routers, and multi-homed servers</li>
                    <li><strong>Physical NIC passthrough</strong> &mdash; Pass a host network adapter directly to a VM for raw L2 access (e.g. Starlink, dedicated WAN)</li>
                    <li><strong>WardenClyffeNet networking</strong> &mdash; Assign a WardenClyffeNet IP to make the VM reachable across your cluster</li>
                    <li><strong>Autostart</strong> &mdash; Configure VMs to start automatically when the host boots</li>
                    <li><strong>Windows support</strong> &mdash; IDE/SATA disk bus and e1000 network adapter for Windows compatibility, VirtIO drivers ISO support</li>
                    <li><strong>Libvirt integration</strong> &mdash; On systems with libvirtd, WardenClyffe auto-discovers and manages existing VMs via virsh &mdash; no reinstallation needed</li>
                    <li><strong>Proxmox integration</strong> &mdash; On Proxmox nodes, VMs are created and managed via the PVE API with full feature parity</li>
                </ul>
            </div>

            <div class="content-section">
                <h2>Creating a VM</h2>
                <p>Navigate to a node in the sidebar, then click <strong>VMs</strong>. Click <strong>Create VM</strong> and fill in:</p>

                <table>
                    <thead>
                        <tr><th>Field</th><th>Description</th><th>Default</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Name</strong></td>
                            <td>A unique name for the VM (no spaces)</td>
                            <td>&mdash;</td>
                        </tr>
                        <tr>
                            <td><strong>CPU Cores</strong></td>
                            <td>Number of virtual CPU cores</td>
                            <td>2</td>
                        </tr>
                        <tr>
                            <td><strong>Memory (MB)</strong></td>
                            <td>RAM allocation in megabytes</td>
                            <td>2048</td>
                        </tr>
                        <tr>
                            <td><strong>Disk Size (GB)</strong></td>
                            <td>OS disk size &mdash; uses qcow2 format on native, Proxmox storage on PVE</td>
                            <td>20</td>
                        </tr>
                        <tr>
                            <td><strong>Boot Media</strong></td>
                            <td>Path to an ISO or .img file for installation (see below)</td>
                            <td>&mdash;</td>
                        </tr>
                        <tr>
                            <td><strong>Import Image</strong></td>
                            <td>Path to an existing disk image to use as the OS disk &mdash; skips creating an empty disk (see below)</td>
                            <td>&mdash;</td>
                        </tr>
                        <tr>
                            <td><strong>Disk Bus</strong></td>
                            <td><code>virtio</code> (fastest, Linux), <code>ide</code> or <code>sata</code> (Windows compatibility)</td>
                            <td>virtio</td>
                        </tr>
                        <tr>
                            <td><strong>Network Model</strong></td>
                            <td><code>virtio</code> (fastest), <code>e1000</code> (Windows), <code>e1000e</code> (newer Windows), <code>rtl8139</code> (legacy)</td>
                            <td>virtio</td>
                        </tr>
                        <tr>
                            <td><strong>Extra NICs</strong></td>
                            <td>Additional network interfaces (net1, net2, ...) with model, bridge, and MAC &mdash; see <a href="#multi-nic">Multi-NIC</a> below</td>
                            <td>none</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="content-section">
                <h2>Attaching an ISO</h2>
                <p>ISOs provide the installation media for your VM &mdash; like inserting a CD. The ISO is attached as a virtual CD-ROM drive and the VM boots from it.</p>

                <h3>Native WardenClyffe Nodes</h3>
                <p>Upload or download the ISO to any path on the server, then enter the full path:</p>
                <div class="code-block">
                    <div class="code-header"><span class="code-lang">bash</span><button class="copy-btn" onclick="copyCode(this)">Copy</button></div>
                    <pre><code># Download an ISO
sudo mkdir -p /opt/isos
sudo wget -O /opt/isos/ubuntu-24.04.iso https://releases.ubuntu.com/24.04/ubuntu-24.04-live-server-amd64.iso

# Then enter in WardenClyffe:
/opt/isos/ubuntu-24.04.iso</code></pre>
                </div>

                <h3>Proxmox Nodes</h3>
                <p>On Proxmox, ISOs must be uploaded to a storage first (via the Proxmox web UI or CLI), then referenced using the Proxmox storage format:</p>
                <div class="code-block">
                    <div class="code-header"><span class="code-lang">text</span></div>
                    <pre><code>local:iso/ubuntu-24.04-live-server-amd64.iso</code></pre>
                </div>
                <p>You can also upload ISOs via the Proxmox web UI at <strong>Datacenter &rarr; Storage &rarr; ISO Images &rarr; Upload</strong>.</p>

                <h3>Detaching an ISO</h3>
                <p>After installing the OS, remove the ISO so the VM boots from disk:</p>
                <ol>
                    <li>Stop the VM</li>
                    <li>Go to VM Settings</li>
                    <li>Clear the ISO path field</li>
                    <li>Start the VM &mdash; it will now boot from the disk</li>
                </ol>
            </div>

            <div class="content-section">
                <h2>Booting from .img Files</h2>
                <p>Some operating systems distribute disk images as <code>.img</code> files instead of ISOs (e.g. Raspberry Pi OS, Alpine cloud images, OpenWrt).
                    WardenClyffe attaches these as a <strong>virtual USB drive</strong> so the VM can boot and install from them, just like plugging in a USB stick.</p>
                <p>Use the <strong>Boot Media</strong> field and enter the path to the <code>.img</code> file:</p>
                <div class="code-block">
                    <div class="code-header"><span class="code-lang">text</span></div>
                    <pre><code>/opt/isos/alpine-virt-3.20.0-x86_64.img</code></pre>
                </div>
                <p>WardenClyffe auto-detects the file type: <code>.iso</code> files are attached as CD-ROM, <code>.img</code> and <code>.raw</code> files are attached as USB.</p>
            </div>

            <div class="content-section">
                <h2>Importing Disk Images</h2>
                <p>If you have a pre-built disk image (a cloud image, an exported VM, or a downloaded appliance), you can import it directly
                    as the OS disk instead of installing from scratch. WardenClyffe converts it to qcow2 format automatically.</p>

                <h3>Supported Formats</h3>
                <table>
                    <thead>
                        <tr><th>Format</th><th>Extension</th><th>Common Sources</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>Raw</td><td><code>.img</code>, <code>.raw</code></td><td>Cloud images (Ubuntu, Debian, Fedora), Raspberry Pi OS</td></tr>
                        <tr><td>QCOW2</td><td><code>.qcow2</code></td><td>KVM/QEMU exports, OpenStack images</td></tr>
                        <tr><td>VMDK</td><td><code>.vmdk</code></td><td>VMware exports</td></tr>
                        <tr><td>VDI</td><td><code>.vdi</code></td><td>VirtualBox exports</td></tr>
                        <tr><td>VHD</td><td><code>.vhd</code>, <code>.vhdx</code></td><td>Hyper-V exports, Azure images</td></tr>
                    </tbody>
                </table>

                <h3>How to Import</h3>
                <ol>
                    <li>Download or copy the disk image to the server</li>
                    <li>Create a new VM and enter the path in the <strong>Import Image</strong> field</li>
                    <li>Set the disk size to at least the size of the image (it will be expanded if larger)</li>
                    <li>Leave the Boot Media field empty &mdash; the imported image is already a bootable disk</li>
                </ol>

                <div class="code-block">
                    <div class="code-header"><span class="code-lang">bash</span><button class="copy-btn" onclick="copyCode(this)">Copy</button></div>
                    <pre><code># Example: download an Ubuntu cloud image
sudo mkdir -p /opt/images
sudo wget -O /opt/images/ubuntu-24.04-cloud.img https://cloud-images.ubuntu.com/noble/current/noble-server-cloudimg-amd64.img

# Then enter in WardenClyffe Import Image field:
/opt/images/ubuntu-24.04-cloud.img</code></pre>
                </div>

                <div class="info-box">
                    <p>&#x1F4A1; <strong>Migrating from other platforms?</strong> Export your VM from VMware (.vmdk), VirtualBox (.vdi), or Hyper-V (.vhd) and import it directly into WardenClyffe. The disk is converted to qcow2 automatically.</p>
                </div>
            </div>

            <div class="content-section">
                <h2>Console Access</h2>
                <p>VMs provide a <strong>VNC graphical console</strong> &mdash; unlike containers which use a text terminal. This works with any operating system including Windows.</p>
                <ul>
                    <li>Click the <strong>Console</strong> button on any running VM to open the VNC viewer in a new window</li>
                    <li>The viewer supports keyboard, mouse, and clipboard</li>
                    <li><strong>Ctrl+Alt+Del</strong> button available in the toolbar for Windows login screens</li>
                    <li>Fullscreen mode available</li>
                </ul>

                <div class="info-box">
                    <p>&#x1F4A1; <strong>VR Server Room:</strong> In WardenClyffe&rsquo;s 3D Server Room view, you can access VM consoles directly in VR &mdash; point at a VM server unit and pull the trigger to open an in-scene VNC display.</p>
                </div>
            </div>

            <div class="content-section">
                <h2>Windows VMs</h2>
                <p>Creating a Windows VM requires specific settings for compatibility:</p>
                <table>
                    <thead>
                        <tr><th>Setting</th><th>Recommended Value</th><th>Why</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Disk Bus</strong></td>
                            <td><code>ide</code> or <code>sata</code></td>
                            <td>Windows installer doesn&rsquo;t include VirtIO drivers by default</td>
                        </tr>
                        <tr>
                            <td><strong>Network Model</strong></td>
                            <td><code>e1000</code></td>
                            <td>Windows has built-in Intel e1000 drivers</td>
                        </tr>
                        <tr>
                            <td><strong>Drivers ISO</strong></td>
                            <td>Optional VirtIO drivers ISO</td>
                            <td>If using virtio disk bus, attach the <a href="https://fedorapeople.org/groups/virt/virtio-win/direct-downloads/stable-virtio/virtio-win.iso" target="_blank">VirtIO Windows drivers ISO</a> as a secondary CD-ROM</td>
                        </tr>
                    </tbody>
                </table>
                <p>After installing Windows with IDE/e1000, you can optionally install VirtIO drivers inside Windows and switch to virtio for better performance.</p>
            </div>

            <div class="content-section">
                <h2>VM Settings</h2>
                <p>To modify a VM, stop it first, then open <strong>VM Settings</strong>:</p>
                <ul>
                    <li><strong>CPU &amp; Memory</strong> &mdash; Change core count and RAM allocation</li>
                    <li><strong>Disk Resize</strong> &mdash; Increase the OS disk size (cannot shrink)</li>
                    <li><strong>Extra Disks</strong> &mdash; Add additional storage volumes</li>
                    <li><strong>Network Adapter</strong> &mdash; Change the primary NIC model (virtio, e1000, rtl8139)</li>
                    <li><strong>WardenClyffeNet IP</strong> &mdash; Assign a WardenClyffeNet IP for cross-node access</li>
                    <li><strong>Additional NICs</strong> &mdash; Add, remove, or modify extra network interfaces</li>
                    <li><strong>ISO</strong> &mdash; Attach or detach installation media</li>
                    <li><strong>BIOS Type</strong> &mdash; Switch between SeaBIOS (legacy) and OVMF (UEFI)</li>
                    <li><strong>Autostart</strong> &mdash; Toggle automatic start on host boot</li>
                </ul>
            </div>

            <div class="content-section">
                <h2>VM Lifecycle</h2>
                <table>
                    <thead>
                        <tr><th>Action</th><th>Description</th></tr>
                    </thead>
                    <tbody>
                        <tr><td><strong>Start</strong></td><td>Boot the VM. Uses <code>qm start</code> (Proxmox), <code>virsh start</code> (libvirt), or launches QEMU directly (native).</td></tr>
                        <tr><td><strong>Stop</strong></td><td>Immediately stops the VM. Uses <code>qm stop</code> (Proxmox), <code>virsh destroy</code> (libvirt), or kills the QEMU process (native).</td></tr>
                        <tr><td><strong>Reboot</strong></td><td>Graceful restart via ACPI (Proxmox only).</td></tr>
                        <tr><td><strong>Delete</strong></td><td>Removes the VM and its configuration. On libvirt, disk files are preserved. On Proxmox and native, disk files are also removed.</td></tr>
                    </tbody>
                </table>
            </div>

            <div class="content-section">
                <h2>Storage</h2>
                <p>VM disk images are stored as <strong>qcow2</strong> files on native WardenClyffe nodes:</p>
                <ul>
                    <li>Default location: <code>/var/lib/wardenclyffe/vms/</code></li>
                    <li>Each VM has a config JSON and one or more disk images</li>
                    <li>Extra disks can be added for additional storage</li>
                </ul>
                <p>On Proxmox, storage is managed by PVE and can use local-lvm, ZFS, Ceph, NFS, or any configured Proxmox storage backend.</p>
            </div>

            <div class="content-section">
                <h2>Networking</h2>
                <p>Every VM has a <strong>primary NIC</strong> (net0) configured during creation. You can also add <strong>extra NICs</strong> for multi-homed configurations.</p>
                <ul>
                    <li><strong>User-mode networking</strong> &mdash; Default. The VM gets NAT access to the host network via QEMU&rsquo;s built-in DHCP. No bridge needed.</li>
                    <li><strong>WardenClyffeNet IP</strong> &mdash; Assign an IP in VM Settings to create a TAP interface on WardenClyffeNet, making the VM reachable across your cluster mesh.</li>
                    <li><strong>Bridge mode</strong> &mdash; Attach a NIC to a host bridge (e.g. <code>br0</code>, <code>vmbr1</code>) for direct L2 network access. The VM gets its own IP on the physical network.</li>
                    <li><strong>Proxmox</strong> &mdash; On PVE nodes, networking is managed via Proxmox bridges (<code>vmbr0</code>, <code>vmbr1</code>, etc.).</li>
                </ul>
            </div>

            <div class="content-section">
                <h2 id="multi-nic">Multiple Network Interfaces (Multi-NIC)</h2>
                <p>VMs can have multiple network interfaces &mdash; essential for firewalls like <strong>OPNsense</strong> and <strong>pfSense</strong> which need separate WAN and LAN interfaces, or for routers, load balancers, and multi-homed servers.</p>

                <h3>How It Works</h3>
                <p>The primary NIC (<strong>net0</strong>) is configured in the main Network section. Additional NICs (<strong>net1</strong>, <strong>net2</strong>, ...) are added in the <strong>Additional Network Interfaces</strong> section on the Network &amp; Boot tab.</p>
                <p>Each extra NIC has the following settings:</p>
                <table>
                    <thead>
                        <tr><th>Setting</th><th>Description</th><th>Default</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Model</strong></td>
                            <td>NIC hardware model: <code>virtio</code> (fastest), <code>e1000</code> (broadest OS support), <code>e1000e</code>, <code>rtl8139</code></td>
                            <td>virtio</td>
                        </tr>
                        <tr>
                            <td><strong>Mode</strong></td>
                            <td><code>Bridge</code> &mdash; attach to a named bridge. <code>Physical NIC</code> &mdash; passthrough a host interface directly (see below).</td>
                            <td>Bridge</td>
                        </tr>
                        <tr>
                            <td><strong>Bridge</strong></td>
                            <td>Host bridge to attach to (e.g. <code>br0</code>, <code>vmbr1</code>). Only visible in Bridge mode. Leave empty for user-mode (NAT) networking.</td>
                            <td>empty (user-mode)</td>
                        </tr>
                        <tr>
                            <td><strong>Physical NIC</strong></td>
                            <td>Select a host network interface from the dropdown. Only visible in Physical NIC mode. WardenClyffe auto-creates a dedicated bridge.</td>
                            <td>&mdash;</td>
                        </tr>
                        <tr>
                            <td><strong>MAC Address</strong></td>
                            <td>Hardware address for the NIC. Auto-generated if left empty.</td>
                            <td>auto</td>
                        </tr>
                    </tbody>
                </table>

                <h3>Adding Extra NICs</h3>
                <ol>
                    <li>Open <strong>Create VM</strong> or <strong>VM Settings</strong> (VM must be stopped)</li>
                    <li>Go to the <strong>Network &amp; Boot</strong> tab</li>
                    <li>Click <strong>+ Add NIC</strong> in the Additional Network Interfaces section</li>
                    <li>Choose the NIC model, enter a bridge name (or leave blank), and optionally set a MAC</li>
                    <li>Repeat for each additional NIC needed</li>
                    <li>Click <strong>Create</strong> or <strong>Save</strong></li>
                </ol>

                <h3>Physical NIC Passthrough</h3>
                <p>Physical NIC passthrough lets you give a VM <strong>direct access to a host network adapter</strong> without manually configuring bridges. This is ideal for:</p>
                <ul>
                    <li><strong>Starlink or dedicated WAN</strong> &mdash; Pass the Starlink adapter to OPNsense so it can run DHCP and manage the connection</li>
                    <li><strong>Multi-homed servers</strong> &mdash; Attach VMs directly to specific physical networks</li>
                    <li><strong>Firewall WAN/LAN separation</strong> &mdash; Give each firewall interface its own physical NIC</li>
                </ul>
                <p>When you select <strong>Physical NIC</strong> mode, WardenClyffe:</p>
                <ol>
                    <li>Shows a dropdown of available physical interfaces on the host (with driver and link speed)</li>
                    <li>Auto-creates a dedicated bridge for the selected interface (<code>vmbr{N}</code> on Proxmox, <code>br-pt-{name}</code> on native)</li>
                    <li>Flushes any host IP from the interface (the VM takes over)</li>
                    <li>Attaches the VM&rsquo;s NIC to the bridge</li>
                </ol>
                <p>If the host later reboots, Proxmox bridges are persisted via pvesh. On native nodes, the bridge is recreated automatically when the VM starts.</p>

                <h3>Example: OPNsense Firewall with Starlink</h3>
                <p>OPNsense requires at least two NICs &mdash; one for WAN (internet) and one for LAN (internal network). Here&rsquo;s how to set it up with a Starlink adapter:</p>
                <ol>
                    <li>Create a VM with 2 CPU cores, 2048 MB RAM, 20 GB disk</li>
                    <li>Attach the OPNsense ISO as boot media</li>
                    <li><strong>Add NIC (WAN)</strong> &mdash; Set mode to <strong>Physical NIC</strong>, select your Starlink adapter from the dropdown</li>
                    <li><strong>Add NIC (LAN)</strong> &mdash; Set mode to <strong>Physical NIC</strong>, select your LAN adapter</li>
                    <li>Start the VM and install OPNsense</li>
                    <li>OPNsense will detect both interfaces &mdash; assign WAN to the Starlink NIC and LAN to the other</li>
                    <li>OPNsense gets a DHCP address from Starlink and serves your LAN</li>
                </ol>

                <div class="info-box">
                    <p>&#x1F4A1; <strong>Proxmox users:</strong> Use <code>vmbr0</code>, <code>vmbr1</code>, etc. as bridge names in Bridge mode, or use Physical NIC mode for automatic bridge creation. WardenClyffe creates the Proxmox bridge and registers it for persistence across reboots.</p>
                </div>

                <div class="info-box">
                    <p>&#x1F4A1; <strong>Native WardenClyffe nodes:</strong> In Bridge mode, use standard Linux bridge names (e.g. <code>br0</code>, <code>br-lan</code>). In Physical NIC mode, WardenClyffe handles everything &mdash; no manual bridge configuration needed.</p>
                </div>

                <h3>How Bridged NICs Work Internally</h3>
                <p>For each extra NIC with a bridge specified (or Physical NIC passthrough), WardenClyffe:</p>
                <ol>
                    <li>Creates a TAP interface (e.g. <code>tap-myvm-1</code>) on the host</li>
                    <li>Attaches the TAP to the bridge (<code>ip link set tap-myvm-1 master br1</code>)</li>
                    <li>Passes the TAP to QEMU as <code>-netdev tap,id=net1,ifname=tap-myvm-1</code></li>
                    <li>On VM stop, the TAP is cleaned up automatically</li>
                </ol>
                <p>If no bridge is specified, the NIC uses QEMU user-mode networking (NAT through the host, no bridge needed).</p>
            </div>

            <div class="content-section">
                <h2 id="passthrough">USB/PCI Device Passthrough</h2>
                <p>WardenClyffe supports passing USB devices, GPUs, NVMe drives, network cards, and any other PCI device directly to a VM. The VM gets exclusive, near-native access to the hardware &mdash; essential for GPU compute, NVMe storage, and dedicated network interfaces.</p>

                <h3>Supported Devices</h3>
                <ul>
                    <li><strong>GPUs</strong> &mdash; NVIDIA, AMD, and Intel GPUs for gaming, AI/ML inference, or transcoding (e.g. Plex hardware transcoding)</li>
                    <li><strong>NVMe drives</strong> &mdash; Pass an entire NVMe SSD to a VM for native storage performance</li>
                    <li><strong>USB devices</strong> &mdash; USB drives, dongles, hardware security keys, serial adapters, Zigbee/Z-Wave sticks</li>
                    <li><strong>Network cards</strong> &mdash; Dedicated NICs for firewall VMs, storage appliances, or network testing</li>
                    <li><strong>Any PCI device</strong> &mdash; Coral TPU, capture cards, HBAs, RAID controllers, and more</li>
                </ul>

                <h3>IOMMU Group Detection</h3>
                <p>WardenClyffe automatically scans your system&rsquo;s IOMMU groups and shows which devices can be passed through safely. Each PCI device belongs to an IOMMU group &mdash; all devices in the same group must be passed through together. WardenClyffe:</p>
                <ul>
                    <li>Detects whether IOMMU is enabled (<code>intel_iommu=on</code> or <code>amd_iommu=on</code>)</li>
                    <li>Lists all IOMMU groups with their member devices</li>
                    <li>Warns if a group contains devices that cannot be safely detached (e.g. the host&rsquo;s boot disk controller)</li>
                    <li>Shows the current driver binding for each device (e.g. <code>nvidia</code>, <code>nvme</code>, <code>vfio-pci</code>)</li>
                </ul>

                <h3>Three-Backend Support</h3>
                <p>Device passthrough works on all three VM backends:</p>
                <table>
                    <thead>
                        <tr><th>Backend</th><th>PCI Passthrough</th><th>USB Passthrough</th><th>How It Works</th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Native QEMU</strong></td>
                            <td>Yes</td>
                            <td>Yes</td>
                            <td>WardenClyffe binds the device to <code>vfio-pci</code> and passes it via QEMU <code>-device vfio-pci</code></td>
                        </tr>
                        <tr>
                            <td><strong>Libvirt</strong></td>
                            <td>Yes</td>
                            <td>Yes</td>
                            <td>Devices are added to the VM&rsquo;s XML definition as <code>&lt;hostdev&gt;</code> entries</td>
                        </tr>
                        <tr>
                            <td><strong>Proxmox VE</strong></td>
                            <td>Yes</td>
                            <td>Yes</td>
                            <td>Uses Proxmox API to add <code>hostpciN</code> and <code>usbN</code> device entries</td>
                        </tr>
                    </tbody>
                </table>

                <h3>Conflict Detection</h3>
                <p>Before passing a device through, WardenClyffe checks for conflicts:</p>
                <ul>
                    <li><strong>Driver conflicts</strong> &mdash; Warns if a device is currently in use by a host driver (e.g. the NVIDIA driver has the GPU)</li>
                    <li><strong>IOMMU group sharing</strong> &mdash; Warns if other devices in the same group would be affected</li>
                    <li><strong>Already attached</strong> &mdash; Prevents passing the same device to multiple VMs</li>
                    <li><strong>Boot-critical devices</strong> &mdash; Refuses to detach the host&rsquo;s primary disk controller or console GPU</li>
                </ul>

                <h3>How to Pass Through a Device</h3>
                <ol>
                    <li>Ensure IOMMU is enabled in your BIOS/UEFI and kernel parameters (<code>intel_iommu=on</code> or <code>amd_iommu=on</code>)</li>
                    <li>Open <strong>Create VM</strong> or <strong>VM Settings</strong> (VM must be stopped)</li>
                    <li>Go to the <strong>Hardware</strong> tab</li>
                    <li>Click <strong>+ Add PCI Device</strong> or <strong>+ Add USB Device</strong></li>
                    <li>Select the device from the dropdown &mdash; WardenClyffe shows the device name, IOMMU group, and current driver</li>
                    <li>Click <strong>Create</strong> or <strong>Save</strong></li>
                </ol>
                <p>WardenClyffe handles driver unbinding and VFIO binding automatically. On VM stop, devices are released back to the host.</p>
            </div>

            <div class="content-section">
                <h2>Libvirt Integration</h2>
                <p>On systems with <strong>libvirtd</strong> running, WardenClyffe automatically detects and manages all libvirt VMs &mdash; no adoption or import step needed. VMs appear in the dashboard just like they do on Proxmox.</p>

                <h3>How It Works</h3>
                <p>WardenClyffe checks for a running libvirt daemon on startup (via <code>virsh uri</code>). If detected, all VM operations are delegated to virsh:</p>
                <table>
                    <thead>
                        <tr><th>Operation</th><th>Command</th></tr>
                    </thead>
                    <tbody>
                        <tr><td>List VMs</td><td><code>virsh list --all</code> + <code>virsh dominfo</code></td></tr>
                        <tr><td>Start</td><td><code>virsh start</code></td></tr>
                        <tr><td>Stop</td><td><code>virsh destroy</code></td></tr>
                        <tr><td>Create</td><td><code>virt-install</code></td></tr>
                        <tr><td>Delete</td><td><code>virsh undefine</code> (disk files preserved)</td></tr>
                        <tr><td>Edit CPU/RAM</td><td><code>virsh setvcpus</code> / <code>virsh setmaxmem</code></td></tr>
                        <tr><td>Autostart</td><td><code>virsh autostart</code></td></tr>
                        <tr><td>VNC console</td><td><code>virsh vncdisplay</code></td></tr>
                    </tbody>
                </table>

                <h3>Migrating from Libvirt to WardenClyffe</h3>
                <p>If you have existing VMs managed by libvirtd, there&rsquo;s nothing to migrate. Install WardenClyffe and your VMs appear automatically in the dashboard. WardenClyffe manages them through virsh without modifying your libvirt configuration.</p>

                <h3>Detection Priority</h3>
                <p>WardenClyffe auto-detects the virtualisation platform in this order:</p>
                <ol>
                    <li><strong>Proxmox VE</strong> &mdash; If <code>pct</code> (Proxmox Container Toolkit) is installed, WardenClyffe uses the Proxmox API</li>
                    <li><strong>Libvirt</strong> &mdash; If <code>virsh uri</code> succeeds (libvirtd is running), WardenClyffe uses virsh commands</li>
                    <li><strong>Native QEMU</strong> &mdash; Otherwise, WardenClyffe manages QEMU directly (no hypervisor layer)</li>
                </ol>
            </div>

            <div class="content-section">
                <h2>Requirements</h2>
                <ul>
                    <li><strong>KVM support</strong> &mdash; The host CPU must support hardware virtualisation (Intel VT-x or AMD-V). Check with: <code>grep -c vmx /proc/cpuinfo</code></li>
                    <li><strong>QEMU</strong> &mdash; Installed automatically by WardenClyffe when creating your first VM</li>
                    <li><strong>Libvirt (optional)</strong> &mdash; If libvirtd is running, WardenClyffe manages VMs through virsh. Install with: <code>apt install libvirt-daemon-system virt-install</code></li>
                    <li><strong>Proxmox VE (optional)</strong> &mdash; On Proxmox nodes, VMs are managed via the PVE API (no additional setup needed)</li>
                </ul>
            </div>

<div class="page-nav"><a href="wardenclyffe-containers.php" class="prev">&larr; Container Management</a><a href="wardenclyffe-storage.php" class="next">Storage &amp; Disks &rarr;</a></div>

    </main>
<?php include 'includes/footer.php'; ?>
