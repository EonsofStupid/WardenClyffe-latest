# Caddy edge image for Wardenclyffe.
# Includes Cloudflare DNS-01 support for wildcard/customer-domain TLS.
FROM caddy:2.11.2-builder AS builder

RUN xcaddy build v2.11.2 \
    --with github.com/caddy-dns/cloudflare@v0.2.1 \
    --with github.com/mholt/caddy-ratelimit@v0.1.0

FROM caddy:2.11.2-alpine

COPY --from=builder /usr/bin/caddy /usr/bin/caddy

RUN /usr/bin/caddy list-modules | grep -E "(cloudflare|ratelimit)"

