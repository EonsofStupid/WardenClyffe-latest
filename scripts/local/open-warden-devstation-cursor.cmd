@echo off
setlocal
set TARGET=ssh-remote+devstation.clyffy.ai
set WORKSPACE=/workspace/warden-storage/projects/shippin-platform
cursor --remote %TARGET% %WORKSPACE%
