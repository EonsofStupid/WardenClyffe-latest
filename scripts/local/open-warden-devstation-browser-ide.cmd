@echo off
setlocal
start "Warden Devstation Code Tunnel" ssh -N code.devstation.clyffy.ai
timeout /t 2 /nobreak >nul
start "" "http://127.0.0.1:18080/?folder=/workspace/warden-storage/projects/WardenClyffe-latest"
