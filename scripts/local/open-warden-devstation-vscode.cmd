@echo off
setlocal
set TARGET=ssh-remote+devstation.clyffy.ai
set WORKSPACE=/workspace/warden-storage/projects/WardenClyffe-latest
code --remote %TARGET% %WORKSPACE%
