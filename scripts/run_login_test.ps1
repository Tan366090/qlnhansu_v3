Write-Host "Running login tests..."
Write-Host ""

Set-Location $PSScriptRoot
php public/auto_test_login_cli.php

Write-Host ""
Write-Host "Press any key to exit..."
$null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown") 