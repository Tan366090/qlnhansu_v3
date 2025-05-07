@echo off
echo Starting database update process...

REM Set paths
set MYSQL_PATH=C:\xampp\mysql\bin\mysql
set SQL_FILE=%~dp0database\qlnhansu.sql
set DB_NAME=qlnhansu

REM Check if MySQL is running
netstat -an | find "3306" > nul
if errorlevel 1 (
    echo MySQL is not running. Please start XAMPP MySQL service first.
    pause
    exit /b 1
)

REM Create database if not exists
echo Creating database if not exists...
"%MYSQL_PATH%" -u root -e "CREATE DATABASE IF NOT EXISTS %DB_NAME%;"

if errorlevel 1 (
    echo Failed to create database.
    pause
    exit /b 1
)

REM Import SQL file
echo Importing SQL file...
"%MYSQL_PATH%" -u root %DB_NAME% < "%SQL_FILE%"

if errorlevel 1 (
    echo Failed to import SQL file.
    pause
    exit /b 1
)

echo Database update completed successfully!
pause 