@echo off
setlocal
ssh devstation.clyffy.ai -t "bash /workspace/warden-storage/projects/wardenclyffe/scripts/agents/warden-agent-stream.sh claude devstation /workspace/warden-storage/projects/Master-Clyffy"
