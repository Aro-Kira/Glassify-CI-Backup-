@echo off
echo Starting selective merge from sean-branch to main-aro...
echo.

echo Step 1: Switching to main-aro branch...
git checkout main-aro
if errorlevel 1 (
    echo ERROR: Failed to switch to main-aro branch
    pause
    exit /b 1
)

echo.
echo Step 2: Copying Inventory Management files...
git checkout sean-branch -- application/controllers/api/Inventory_api.php
git checkout sean-branch -- application/models/Inventory_model.php
git checkout sean-branch -- application/controllers/InventCon.php

echo.
echo Step 3: Copying Product Management files...
git checkout sean-branch -- application/controllers/ShopCon.php
git checkout sean-branch -- application/models/Product_model.php

echo.
echo Selective merge complete!
echo.
echo Please check the files and commit if everything looks good:
echo   git status
echo   git diff
echo   git add .
echo   git commit -m "Selectively merge Inventory and Product Management from sean-branch"
echo.
pause

