#!/bin/bash
# WardenClyffeDisk Client Reset Script
# Cleans up stuck FUSE mounts and wipes local cache data.
# WARNING: Only run this on CLIENT machines. Running on a Leader/Follower will cause DATA LOSS.

if [ "$EUID" -ne 0 ]; then
  echo "Please run as root (sudo)"
  exit 1
fi

echo "========================================================"
echo "WARNING: This script will WIPE all data in /var/lib/wardenclyffedisk"
echo "If this is a LEADER or FOLLOWER node, you will LOSE DATA."
echo "Only proceed if this is a CLIENT node (cache only)."
echo "========================================================"
read -p "Are you sure you want to proceed? (y/N) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Aborted."
    exit 1
fi

echo "Stopping any WardenClyffeDisk processes..."
pkill -f wardenclyffedisk || true
sleep 2
pkill -9 -f wardenclyffedisk || true

echo "Unmounting WardenClyffeDisk mount point..."
umount -f /mnt/wardenclyffedisk 2>/dev/null || fusermount -u -z /mnt/wardenclyffedisk 2>/dev/null
if mount | grep -q "wardenclyffedisk"; then
    echo "Error: Failed to unmount /mnt/wardenclyffedisk. Please check open handles (lsof)."
    exit 1
fi

echo "Wiping local cache (/var/lib/wardenclyffedisk)..."
if [ -d "/var/lib/wardenclyffedisk" ]; then
    rm -rf /var/lib/wardenclyffedisk/*
    echo "Cache cleared."
else
    echo "No cache found at /var/lib/wardenclyffedisk (already clean?)"
fi

echo "Reset complete. please restart wardenclyffedisk now."
