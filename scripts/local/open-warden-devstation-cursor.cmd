@echo off
setlocal
set TARGET=ssh-remote+devstation.clyffy.ai
set WORKSPACE=/workspace/warden-storage/projects/WardenClyffe-latest
cursor --remote %TARGET% %WORKSPACE%
