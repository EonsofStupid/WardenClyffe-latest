@echo off
setlocal
ssh devstation.clyffy.ai -t "cd /workspace/warden-storage/projects/WardenClyffe-latest && bash scripts/agents/warden-agent-stream.sh claude devstation"
