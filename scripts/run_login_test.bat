@echo off
echo Running login tests...
echo.

cd /d %~dp0
php public/auto_test_login_cli.php

echo.
echo Press any key to exit...
pause > nul 