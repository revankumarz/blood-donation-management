# Blood Donation Management System - Installation Guide

## Prerequisites

Before you begin, ensure you have the following installed:
- **XAMPP** (or any other Apache + MySQL + PHP stack)
  - PHP 7.4 or higher
  - MySQL 5.7 or higher
  - Apache Web Server

## Installation Steps

### 1. Setup the Project

1. Copy the project folder to your XAMPP `htdocs` directory:
   ```
   C:\xampp\htdocs\dbms-project\
   ```

2. Make sure all files are in place:
   - `config.php`
   - `database.sql`
   - `index.html`
   - `login.php`
   - `register.php`
   - All dashboard files
   - CSS files

### 2. Start XAMPP Services

1. Open **XAMPP Control Panel**
2. Start **Apache** service
3. Start **MySQL** service

### 3. Create the Database

#### Option 1: Using phpMyAdmin (Recommended for beginners)

1. Open your web browser and go to: `http://localhost/phpmyadmin`
2. Click on the **"SQL"** tab at the top
3. Open the `database.sql` file from the project folder
4. Copy all the contents of `database.sql`
5. Paste it into the SQL query box in phpMyAdmin
6. Click **"Go"** to execute the SQL

#### Option 2: Using MySQL Command Line

1. Open Command Prompt/Terminal
2. Navigate to MySQL bin folder:
   ```bash
   cd C:\xampp\mysql\bin
   ```
3. Login to MySQL:
   ```bash
   mysql -u root -p
   ```
   (Press Enter if no password is set)
4. Run the SQL file:
   ```sql
   source C:\xampp\htdocs\dbms-project\database.sql
   ```

### 4. Verify Database Configuration

Open `config.php` and verify the database credentials:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');  // Change this if you have a password
define('DB_NAME', 'blood_donation_system');
```

**If your MySQL has a password**, update the `DB_PASS` value accordingly.

### 5. Access the Website

Open your web browser and navigate to:
```
http://localhost/dbms-project/
```

You should see the BloodLife homepage.

## Default Login Credentials

### Admin Account
- **Username:** `admin`
- **Password:** `admin123`
- **Admin Passkey (for new admin registration):** `admin123`

**IMPORTANT:** Change the admin password and passkey after first login in a production environment!

## Testing the System

### Register a New Donor

1. Go to: `http://localhost/dbms-project/register.php`
2. Select **"Donor"** as the role
3. Fill in all required fields:
   - Username (e.g., `john_doe`)
   - Email (e.g., `john@example.com`)
   - Password (min 6 characters)
   - Full Name
   - Date of Birth
   - Gender
   - Blood Type
   - Phone Number
   - Address (Street, City, Pincode)
4. Click **"Register"**
5. Login with the new credentials at: `http://localhost/dbms-project/login.php`

### Register a New Hospital

1. Go to: `http://localhost/dbms-project/register.php`
2. Select **"Hospital"** as the role
3. Fill in all required fields:
   - Username (e.g., `city_hospital`)
   - Email (e.g., `hospital@example.com`)
   - Password (min 6 characters)
   - Hospital Name
   - Phone Number
   - Address (Street, City, Pincode)
4. Click **"Register"**
5. Login with the new credentials

### Test Admin Functions

1. Login as admin: `http://localhost/dbms-project/login.php`
   - Username: `admin`
   - Password: `admin123`
2. You'll be redirected to the Admin Dashboard
3. Try the following:
   - View blood inventory
   - Manage donors
   - Manage hospitals
   - Update blood inventory
   - View blood requests

### Test Donor Functions

1. Login as a donor
2. You'll be redirected to the Donor Dashboard
3. Try the following:
   - Schedule a blood donation appointment
   - View donation history
   - Check eligibility status

### Test Hospital Functions

1. Login as a hospital
2. You'll be redirected to the Hospital Dashboard
3. Try the following:
   - Submit a blood request
   - View current blood inventory
   - Cancel pending requests

## Features Overview

### Admin Dashboard
- View overall statistics (total donors, hospitals, donations)
- Manage blood inventory (add/remove stock)
- View and manage blood requests
- View donor and hospital registrations
- Generate reports
- System logs and audit trail

### Donor Dashboard
- View personal donation statistics
- Schedule donation appointments
- View donation history
- Check eligibility status
- Receive notifications

### Hospital Dashboard
- Submit blood requests with urgency levels
- View current blood inventory
- Track request status
- Cancel pending requests
- Find available donors

## Common Issues and Solutions

### Issue 1: "Connection failed" error
**Solution:** Make sure MySQL service is running in XAMPP Control Panel

### Issue 2: "Access denied for user 'root'"
**Solution:** Check your MySQL password in `config.php`

### Issue 3: Database tables not created
**Solution:** Re-run the `database.sql` file in phpMyAdmin

### Issue 4: Page shows blank
**Solution:** Check if Apache service is running in XAMPP

### Issue 5: CSS not loading
**Solution:**
- Clear browser cache
- Check file paths in HTML/PHP files
- Ensure all CSS files are in the correct directory

## Security Recommendations for Production

1. **Change Default Credentials:**
   - Change admin password immediately
   - Update the `ADMIN_PASSKEY` in `config.php`

2. **Database Security:**
   - Set a strong MySQL root password
   - Create a dedicated MySQL user for the application
   - Update `config.php` with new credentials

3. **File Permissions:**
   - Restrict write permissions on sensitive files
   - Keep `config.php` read-only

4. **HTTPS:**
   - Use SSL/TLS certificates for production
   - Enable HTTPS in Apache configuration

5. **Input Validation:**
   - The system includes basic sanitization
   - Consider additional validation for production use

6. **Session Security:**
   - Configure session timeout
   - Use secure session cookies

## Database Schema

The system includes the following tables:

1. **users** - Main user authentication table
2. **admins** - Admin-specific information
3. **donors** - Donor profiles and details
4. **hospitals** - Hospital information
5. **blood_inventory** - Current blood stock levels
6. **donations** - Donation records
7. **blood_requests** - Hospital blood requests
8. **appointments** - Scheduled donation appointments
9. **notifications** - User notifications
10. **system_logs** - Audit trail and activity logs

## Support

For issues or questions:
1. Check the main README.md file
2. Review this installation guide
3. Check XAMPP error logs in `C:\xampp\apache\logs\error.log`
4. Check MySQL error logs

## Next Steps

After successful installation:

1. Change default admin password
2. Register test donors and hospitals
3. Add initial blood inventory
4. Test the complete workflow:
   - Donor registration → Appointment → Donation → Inventory update
   - Hospital registration → Blood request → Request fulfillment

## System Requirements

- **Minimum:**
  - PHP 7.4+
  - MySQL 5.7+
  - 1GB RAM
  - 100MB Disk Space

- **Recommended:**
  - PHP 8.0+
  - MySQL 8.0+
  - 2GB RAM
  - 500MB Disk Space

Enjoy using the Blood Donation Management System!
