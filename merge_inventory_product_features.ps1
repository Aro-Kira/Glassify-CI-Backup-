# PowerShell script to selectively merge Inventory and Product Management features from sean-branch to main-aro

Write-Host "Starting selective merge from sean-branch to main-aro..." -ForegroundColor Green

# Check current branch
$currentBranch = git branch --show-current
Write-Host "Current branch: $currentBranch" -ForegroundColor Yellow

# Switch to main-aro
Write-Host "`nSwitching to main-aro branch..." -ForegroundColor Cyan
git checkout main-aro
if ($LASTEXITCODE -ne 0) {
    Write-Host "Failed to switch to main-aro. Please ensure the branch exists." -ForegroundColor Red
    exit 1
}

Write-Host "Successfully switched to main-aro" -ForegroundColor Green

# List of files to copy from sean-branch
$filesToCopy = @(
    # Inventory Management files
    "application/controllers/api/Inventory_api.php",
    "application/models/Inventory_model.php",
    "application/controllers/InventCon.php",
    
    # Product Management files
    "application/controllers/ShopCon.php",
    "application/models/Product_model.php"
)

Write-Host "`nCopying files from sean-branch..." -ForegroundColor Cyan

foreach ($file in $filesToCopy) {
    Write-Host "  - Copying $file..." -ForegroundColor Yellow
    git checkout sean-branch -- $file
    if ($LASTEXITCODE -eq 0) {
        Write-Host "    ✓ Successfully copied $file" -ForegroundColor Green
    } else {
        Write-Host "    ✗ Failed to copy $file" -ForegroundColor Red
    }
}

Write-Host "`nFiles copied successfully!" -ForegroundColor Green
Write-Host "`nNote: You may need to manually update routes.php to add inventory and product routes if they don't exist." -ForegroundColor Yellow
Write-Host "Routes should include:" -ForegroundColor Yellow
Write-Host "  - Inventory routes (lines 183-207)" -ForegroundColor Yellow
Write-Host "  - Product routes (lines 91-92)" -ForegroundColor Yellow

Write-Host "`nSelective merge complete!" -ForegroundColor Green

