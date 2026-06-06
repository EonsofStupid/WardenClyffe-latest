@echo off
setlocal
set TARGET=ssh-remote+devstation.clyffy.ai
set WORKSPACE=/workspace/WardenClyffe-latest
cursor --remote %TARGET% %WORKSPACE%
