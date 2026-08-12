@echo off
setlocal
ssh capsule.clyffy.ai -t "cd /workspace/WardenClyffe-latest && bash scripts/agents/warden-agent-stream.sh codex capsule"
