#!/usr/bin/env bash
set -euo pipefail

usage() {
  cat <<'USAGE'
Usage:
  upsert-cloudflare-a-record.sh --zone-id ZONE_ID --name FQDN --target IP [--proxied] [--apply]

Environment:
  CLOUDFLARE_API_TOKEN must contain a Cloudflare token with DNS edit access.

Examples:
  scripts/dns/upsert-cloudflare-a-record.sh \
    --zone-id 40bb8e4477b430c77dbb6c81b3fb6e5f \
    --name ssh.clyffy.ai \
    --target 104.176.44.101

  scripts/dns/upsert-cloudflare-a-record.sh \
    --zone-id 40bb8e4477b430c77dbb6c81b3fb6e5f \
    --name ssh.clyffy.ai \
    --target 104.176.44.101 \
    --apply
USAGE
}

zone_id=""
record_name=""
target_ip=""
proxied=false
apply=false

while [[ $# -gt 0 ]]; do
  case "$1" in
    --zone-id)
      zone_id="${2:-}"
      shift 2
      ;;
    --name)
      record_name="${2:-}"
      shift 2
      ;;
    --target)
      target_ip="${2:-}"
      shift 2
      ;;
    --proxied)
      proxied=true
      shift
      ;;
    --apply)
      apply=true
      shift
      ;;
    -h|--help)
      usage
      exit 0
      ;;
    *)
      echo "Unknown argument: $1" >&2
      usage >&2
      exit 2
      ;;
  esac
done

if [[ -z "$zone_id" || -z "$record_name" || -z "$target_ip" ]]; then
  usage >&2
  exit 2
fi

token="${CLOUDFLARE_API_TOKEN:-}"

jq -n \
  --arg action "$([[ "$apply" == true ]] && echo apply || echo dry-run)" \
  --arg zone_id "$zone_id" \
  --arg record_name "$record_name" \
  --arg target_ip "$target_ip" \
  --argjson proxied "$proxied" \
  --argjson token_present "$([[ -n "$token" ]] && echo true || echo false)" \
  '{
    action: $action,
    zone_id: $zone_id,
    record_name: $record_name,
    target_ip: $target_ip,
    proxied: $proxied,
    token_present: $token_present
  }'

if [[ "$apply" != true ]]; then
  echo "Dry run only. Re-run with --apply after route readiness is verified."
  exit 0
fi

if [[ -z "$token" ]]; then
  echo "Missing CLOUDFLARE_API_TOKEN." >&2
  exit 1
fi

payload="$(
  jq -n \
    --arg name "$record_name" \
    --arg content "$target_ip" \
    --argjson proxied "$proxied" \
    '{
      type: "A",
      name: $name,
      content: $content,
      ttl: 1,
      proxied: $proxied,
      comment: "WardenClyffe managed DNS record"
    }'
)"

encoded_name="$(python3 - <<PY
from urllib.parse import quote
print(quote("$record_name", safe=""))
PY
)"

base_url="https://api.cloudflare.com/client/v4/zones/${zone_id}/dns_records"
existing="$(
  curl -fsS \
    -H "Authorization: Bearer ${token}" \
    -H "Content-Type: application/json" \
    "${base_url}?type=A&name=${encoded_name}"
)"

record_id="$(jq -r '.result[0].id // empty' <<<"$existing")"

if [[ -n "$record_id" ]]; then
  result="$(
    curl -fsS -X PUT \
      -H "Authorization: Bearer ${token}" \
      -H "Content-Type: application/json" \
      --data "$payload" \
      "${base_url}/${record_id}"
  )"
  verb="updated"
else
  result="$(
    curl -fsS -X POST \
      -H "Authorization: Bearer ${token}" \
      -H "Content-Type: application/json" \
      --data "$payload" \
      "$base_url"
  )"
  verb="created"
fi

jq --arg verb "$verb" '{
  result: $verb,
  success,
  name: .result.name,
  type: .result.type,
  content: .result.content,
  proxied: .result.proxied,
  id: .result.id
}' <<<"$result"
