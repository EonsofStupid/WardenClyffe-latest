@echo off
setlocal
ssh -o BatchMode=yes devstation.clyffy.ai "printf 'user='; whoami; printf 'host='; hostname; printf 'groups='; id -nG"
ssh -o BatchMode=yes hades@devstation.clyffy.ai "printf 'hades_user='; whoami; printf 'hades_host='; hostname; printf 'hades_groups='; id -nG"
