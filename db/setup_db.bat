@echo off
echo Setting up insurance system database...
mysql -u root -p < setup.sql
echo Database setup complete!
pause