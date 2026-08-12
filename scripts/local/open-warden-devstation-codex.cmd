@echo off
setlocal
ssh devstation.clyffy.ai -t "cd /workspace/warden-storage/projects/shippin-platform && bash scripts/agents/warden-agent-stream.sh codex devstation"
