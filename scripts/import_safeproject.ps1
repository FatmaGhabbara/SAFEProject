# PowerShell script to create the database and import the SQL file
# Usage: open PowerShell and run this script. You will be prompted for the MySQL root password (press Enter if none).

$mysqlPath = 'C:\xampp\mysql\bin\mysql.exe'
$dbName = 'safeproject_db'
$sqlFile = 'C:\xampp\htdocs\SAFEProject\database\init_complete.sql'

Write-Host "Creating database `$dbName` (if it doesn't exist)..."
& $mysqlPath -u root -p -e "CREATE DATABASE IF NOT EXISTS \`$dbName\` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

Write-Host "Importing SQL file into `$dbName`. You will be prompted for the MySQL password (press Enter if none)."
cmd /c "`"$mysqlPath`" -u root -p $dbName < `"$sqlFile`""

Write-Host "Import finished. Verify in phpMyAdmin or by running 'SHOW TABLES;' on the database."
