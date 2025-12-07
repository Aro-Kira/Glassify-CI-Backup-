@echo off
REM Database Export Script for Glassify-CI
REM This script exports the glassify-test database to a SQL file

echo ========================================
echo Glassify-CI Database Export Tool
echo ========================================
echo.

REM Set paths
set MYSQL_BIN=C:\xampp\mysql\bin
set PROJECT_PATH=%~dp0
set EXPORT_FILE=%PROJECT_PATH%glassify-test-export-%date:~-4,4%%date:~-10,2%%date:~-7,2%-%time:~0,2%%time:~3,2%%time:~6,2%.sql
set EXPORT_FILE=%EXPORT_FILE: =0%

REM Database credentials (from your config)
set DB_USER=admin_glassify
set DB_NAME=glassify-test

echo Exporting database: %DB_NAME%
echo Export file: %EXPORT_FILE%
echo.

REM Check if MySQL bin directory exists
if not exist "%MYSQL_BIN%" (
    echo ERROR: MySQL bin directory not found at %MYSQL_BIN%
    echo Please update MYSQL_BIN path in this script.
    pause
    exit /b 1
)

REM Export database
echo Please enter your MySQL password for user: %DB_USER%
"%MYSQL_BIN%\mysqldump.exe" -u %DB_USER% -p %DB_NAME% > "%EXPORT_FILE%"

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ========================================
    echo Export completed successfully!
    echo ========================================
    echo File saved to: %EXPORT_FILE%
    echo.
    echo You can now share this file with your groupmate.
) else (
    echo.
    echo ========================================
    echo Export failed! Please check your credentials.
    echo ========================================
)

pause


