@echo off
REM Helper: Backup DB and run clear_orders.php
REM WARNING: This will run mysqldump with the credentials you provide and then execute the clear script.

setlocal enabledelayedexpansion
set /p DB_HOST=DB host [localhost]:
if "%DB_HOST%"=="" set DB_HOST=localhost
set /p DB_USER=DB user:
set /p DB_NAME=DB name:
set /p DB_PASS=DB password (input hidden not supported here):

REM Create timestamp
for /f "tokens=1-3 delims=/ " %%a in ("%date%") do (
  set yyyy=%%c
  set mm=%%a
  set dd=%%b
)
for /f "tokens=1-3 delims=:." %%a in ("%time%") do (
  set hh=%%a
  set min=%%b
  set ss=%%c
)
set TIMESTAMP=%yyyy%%mm%%dd%_%hh%%min%%ss%
set BACKUP_FILE=%~dp0backup_full_db_%TIMESTAMP%.sql

echo Backing up database %DB_NAME% to %BACKUP_FILE% ...
mysqldump -h %DB_HOST% -u %DB_USER% -p%DB_PASS% %DB_NAME% > "%BACKUP_FILE%"
if %ERRORLEVEL% neq 0 (
  echo mysqldump failed. Check credentials and that mysqldump is in PATH.
  pause
  exit /b 1
)

echo Backup complete.

echo Running clear_orders.php (will DELETE orders)...
php "%~dp0clear_orders.php" --confirm
if %ERRORLEVEL% neq 0 (
  echo clear_orders.php returned error.
  pause
  exit /b 1
)

echo Done. Please verify the site and rollback from the backup if needed.
pause
