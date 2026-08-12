@echo off
setlocal
set TARGET=ssh-remote+devstation.clyffy.ai
set WORKSPACE=/workspace/warden-storage/projects/shippin-platform
code --remote %TARGET% %WORKSPACE%
