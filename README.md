# Insurance Telemetry System

A comprehensive vehicle insurance management system with telemetry integration.

## Setup Instructions

1. Database Setup:
   ```bash
   mysql -u root -p < db/setup.sql
   ```

2. Configuration:
   - Copy `config.php.example` to `config.php`
   - Update database credentials and other settings

3. File Permissions:
   ```bash
   chmod 755 uploads/
   chmod 644 access.htaccess
   ```

4. Dependencies:
   ```bash
   composer install
   ```

## Directory Structure
