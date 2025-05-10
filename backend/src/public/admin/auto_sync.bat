@echo off
:loop
php "%~dp0sync_chat_data.php"
timeout /t 60 /nobreak
goto loop 