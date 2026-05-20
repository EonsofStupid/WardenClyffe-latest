# WardenClyffeNet Docker Container — secure mesh VPN
#
# Runs in --network host mode with /dev/net/tun. Pre-built static musl
# binaries are placed under dist/<arch>/ by the publish workflow; this
# Dockerfile copies the right one based on TARGETARCH.
#
# Usage:
#   docker run -d --name wardenclyffenet --network host \
#     --cap-add NET_ADMIN --device /dev/net/tun:/dev/net/tun \
#     -v wardenclyffenet-config:/etc/wardenclyffenet \
#     -e WARDENCLYFFENET_JOIN_TOKEN=<token> \
#     ghcr.io/wardenclyffesoftwaresystemsltd/wardenclyffenet:latest

FROM debian:bookworm-slim

ARG TARGETARCH

RUN apt-get update && apt-get install -y --no-install-recommends \
    iproute2 \
    iptables \
    procps \
    ca-certificates \
    && rm -rf /var/lib/apt/lists/*

RUN mkdir -p /etc/wardenclyffenet /var/run/wardenclyffenet

COPY dist/${TARGETARCH}/wardenclyffenet /usr/local/bin/wardenclyffenet
COPY dist/${TARGETARCH}/wardenclyffenetctl /usr/local/bin/wardenclyffenetctl
COPY docker/entrypoint.sh /entrypoint.sh

RUN chmod +x /usr/local/bin/wardenclyffenet /usr/local/bin/wardenclyffenetctl /entrypoint.sh

EXPOSE 9600/udp
EXPOSE 9601/udp

VOLUME ["/etc/wardenclyffenet"]

ENTRYPOINT ["/entrypoint.sh"]
