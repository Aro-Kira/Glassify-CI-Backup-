@echo off
REM ============================================================================
REM Glassify Database Setup Script for Windows
REM ============================================================================
REM This batch file helps you set up the Glassify database easily
REM ============================================================================

echo.
echo ========================================
echo Glassify Database Setup
echo ========================================
echo.

REM Check if MySQL is in the default XAMPP location
set MYSQL_PATH=C:\xampp\mysql\bin\mysql.exe
set SQL_FILE=%~dp0setup_database_with_data.sql

if not exist "%MYSQL_PATH%" (
    echo ERROR: MySQL not found at %MYSQL_PATH%
    echo.
    echo Please update MYSQL_PATH in this batch file to point to your MySQL installation.
    echo.
    pause
    exit /b 1
)

if not exist "%SQL_FILE%" (
    echo ERROR: SQL file not found: %SQL_FILE%
    echo.
    echo Please make sure setup_database_with_data.sql is in the same directory as this batch file.
    echo.
    pause
    exit /b 1
)

echo This script will:
echo - Create the database 'latest_glassifydb'
echo - Create all required tables
echo - Insert sample data
echo.
echo WARNING: If the database already exists, some operations may fail.
echo.
set /p CONFIRM="Do you want to continue? (Y/N): "

if /i not "%CONFIRM%"=="Y" (
    echo Setup cancelled.
    pause
    exit /b 0
)

echo.
echo Enter MySQL root password (press Enter if no password):
set /p MYSQL_PASS="Password: "

if "%MYSQL_PASS%"=="" (
    echo.
    echo Running setup with no password...
    "%MYSQL_PATH%" -u root < "%SQL_FILE%"
) else (
    echo.
    echo Running setup with password...
    "%MYSQL_PATH%" -u root -p%MYSQL_PASS% < "%SQL_FILE%"
)

if %ERRORLEVEL% EQU 0 (
    echo.
    echo ========================================
    echo Database setup completed successfully!
    echo ========================================
    echo.
    echo Default login credentials:
    echo - Admin: admin@glassify.com / password123
    echo - Sales Rep: queen@gmail.com / password123
    echo - Customer: john.doe@example.com / password123
    echo.
    echo IMPORTANT: Change passwords after first login!
    echo.
) else (
    echo.
    echo ========================================
    echo ERROR: Database setup failed!
    echo ========================================
    echo.
    echo Please check:
    echo 1. MySQL is running in XAMPP
    echo 2. MySQL credentials are correct
    echo 3. You have permission to create databases
    echo.
    echo You can also try importing the SQL file manually using phpMyAdmin.
    echo.
)

pause
