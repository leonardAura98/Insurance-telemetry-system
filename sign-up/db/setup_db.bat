@echo off
echo Setting up insurance system database...
mysql -u root -p < create_database.sql
mysql -u root -p insurance_system < sample_data.sql
echo Database setup complete!
pause 