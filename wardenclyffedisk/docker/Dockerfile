# WardenClyffeDisk Docker Container — distributed FUSE filesystem
#
# Runs in --network host mode with /dev/fuse. Pre-built static musl binaries
# are placed under dist/<arch>/ by the publish workflow; this Dockerfile
# copies the right one based on TARGETARCH.
#
# Usage:
#   docker run -d --name wardenclyffedisk --network host \
#     --cap-add SYS_ADMIN --device /dev/fuse:/dev/fuse \
#     --security-opt apparmor:unconfined \
#     -v wardenclyffedisk-config:/etc/wardenclyffedisk \
#     -v wardenclyffedisk-data:/var/lib/wardenclyffedisk \
#     -v /mnt/wardenclyffedisk:/mnt/wardenclyffedisk:rshared \
#     -e WARDENCLYFFEDISK_ROLE=client \
#     -e WARDENCLYFFEDISK_PEERS=<leader_ip:8550> \
#     ghcr.io/wardenclyffesoftwaresystemsltd/wardenclyffedisk:latest

FROM debian:bookworm-slim

ARG TARGETARCH

RUN apt-get update && apt-get install -y --no-install-recommends \
    fuse3 \
    procps \
    ca-certificates \
    && rm -rf /var/lib/apt/lists/*

RUN mkdir -p /etc/wardenclyffedisk /var/lib/wardenclyffedisk /mnt/wardenclyffedisk

COPY dist/${TARGETARCH}/wardenclyffedisk    /usr/local/bin/wardenclyffedisk
COPY dist/${TARGETARCH}/wardenclyffediskctl /usr/local/bin/wardenclyffediskctl
COPY docker/entrypoint.sh /entrypoint.sh

RUN chmod +x /usr/local/bin/wardenclyffedisk /usr/local/bin/wardenclyffediskctl /entrypoint.sh

EXPOSE 8550/tcp
EXPOSE 9878/tcp

VOLUME ["/etc/wardenclyffedisk", "/var/lib/wardenclyffedisk"]

ENTRYPOINT ["/entrypoint.sh"]
