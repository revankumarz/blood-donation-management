# Quick Start Guide - BloodLife System

## 5-Minute Setup

### Step 1: Start XAMPP (1 minute)
1. Open XAMPP Control Panel
2. Click "Start" on **Apache**
3. Click "Start" on **MySQL**
4. Wait for green indicators

### Step 2: Create Database (2 minutes)
1. Open browser: `http://localhost/phpmyadmin`
2. Click **SQL** tab at the top
3. Open file: `database.sql` (in project folder)
4. Copy ALL contents
5. Paste in SQL box
6. Click **Go** button
7. Wait for "Success" message

### Step 3: Test the Website (2 minutes)
1. Open browser: `http://localhost/dbms-project/`
2. You should see the BloodLife homepage

## Quick Test

### Login as Admin
1. Go to: `http://localhost/dbms-project/login.php`
2. Enter:
   - Username: `admin`
   - Password: `admin123`
3. Click Login
4. You're in the Admin Dashboard!

### Register a Donor
1. Go to: `http://localhost/dbms-project/register.php`
2. Select Role: **Donor**
3. Fill in the form:
   - Username: `testdonor`
   - Email: `donor@test.com`
   - Password: `123456`
   - Full Name: `Test Donor`
   - Date of Birth: (any past date)
   - Gender: `Male`
   - Blood Type: `O+`
   - Phone: `1234567890`
   - Street: `123 Main St`
   - City: `Test City`
   - Pincode: `12345`
4. Click **Register**
5. Login with: `testdonor` / `123456`

### Register a Hospital
1. Go to: `http://localhost/dbms-project/register.php`
2. Select Role: **Hospital**
3. Fill in the form:
   - Username: `testhospital`
   - Email: `hospital@test.com`
   - Password: `123456`
   - Hospital Name: `City General Hospital`
   - Phone: `9876543210`
   - Street: `456 Hospital Rd`
   - City: `Test City`
   - Pincode: `12345`
4. Click **Register**
5. Login with: `testhospital` / `123456`

## Common Workflows

### As Admin - Add Blood to Inventory
1. Login as admin
2. Click **Blood Inventory** in sidebar
3. Select Blood Type: `O+`
4. Select Action: **Add Stock**
5. Enter Quantity: `2000` ml
6. Click **Update Inventory**

### As Donor - Schedule Appointment
1. Login as donor
2. In the dashboard, find "Schedule Blood Donation Appointment"
3. Select a future date
4. Select a time
5. Click **Schedule Appointment**

### As Hospital - Request Blood
1. Login as hospital
2. Find "Request Blood" form
3. Fill in:
   - Blood Type: `O+`
   - Quantity: `450` ml
   - Urgency: `High`
   - Required Date: (future date)
   - Notes: `Emergency surgery`
4. Click **Submit Request**

## Verification

### Check if Everything Works
1. Login as **Admin**
2. You should see:
   - Total Donors: 1
   - Registered Hospitals: 1
   - Blood Inventory with your added stock
   - Pending Request from hospital

## Troubleshooting

### "Connection failed" Error
- Make sure MySQL is running (green in XAMPP)
- Check XAMPP Control Panel

### Can't Login
- Make sure you ran `database.sql`
- Check username/password carefully
- Default admin: `admin` / `admin123`

### Page is Blank
- Make sure Apache is running (green in XAMPP)
- Check URL: `http://localhost/dbms-project/`

### CSS Not Loading
- Clear browser cache (Ctrl+F5)
- Check if CSS files exist in folder

## File Structure Check

Make sure these files exist in `C:\xampp\htdocs\dbms-project\`:
- ✅ config.php
- ✅ database.sql
- ✅ login.php
- ✅ register.php
- ✅ admin_dashboard.php
- ✅ donor_dashboard.php
- ✅ hospital_dashboard.php
- ✅ dashboard.css

## Next Steps

After successful setup:
1. Change admin password (security)
2. Add more donors
3. Add more hospitals
4. Test complete donation workflow
5. Explore all features

## Support

For detailed installation: See **INSTALLATION.md**
For full documentation: See **README.md**

---

**Congratulations!** Your Blood Donation Management System is ready to use!
