@echo off
echo ========================================
echo Glassify-CI - Network Setup Helper
echo ========================================
echo.
echo Finding your IP address...
echo.
ipconfig | findstr /i "IPv4"
echo.
echo ========================================
echo Use the IP address above to access from other devices
echo Example: http://YOUR_IP_ADDRESS/Glassify-CI/
echo ========================================
echo.
pause
