@echo off
setlocal
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0preflight-warden-devstation-secure-workspace.ps1" %*
