# Blood Donation Management System (BloodLife)

A comprehensive, full-stack web application for managing blood donations, inventory, and donor records using PHP, MySQL, HTML, CSS, and JavaScript.

## About

BloodLife is a complete blood bank management system that streamlines operations by connecting blood donors, hospitals, and administrators through an intuitive web interface. The system ensures efficient blood donation management, real-time inventory tracking, and timely fulfillment of blood requests.

## Features

### Completed Features

- ✅ **User Authentication System**
  - Role-based login (Admin, Donor, Hospital)
  - Secure password hashing with PHP password_hash()
  - Session management
  - Admin passkey verification

- ✅ **Admin Dashboard**
  - Overview statistics (total donors, hospitals, donations)
  - Blood inventory management (add/remove stock)
  - View and manage all blood requests
  - Donor and hospital registration monitoring
  - System logs and audit trail
  - Real-time inventory status with visual indicators

- ✅ **Donor Portal**
  - Personal donation statistics and history
  - Blood donation appointment scheduling
  - Eligibility status checking (56-day rule)
  - Lives saved counter
  - Notification system
  - Profile management

- ✅ **Hospital Portal**
  - Blood request submission with urgency levels
  - Real-time blood inventory viewing
  - Request tracking and management
  - Request cancellation capability
  - Blood stock status alerts

- ✅ **Blood Inventory System**
  - Real-time tracking of all 8 blood types (A+, A-, B+, B-, AB+, AB-, O+, O-)
  - Visual stock level indicators (Critical/Low/Available)
  - Inventory update logging
  - Stock statistics and analytics

- ✅ **Database Management**
  - Comprehensive MySQL schema with 10+ tables
  - Proper foreign key relationships
  - Indexed queries for performance
  - Transaction logging
  - Data integrity constraints

- ✅ **Security Features**
  - SQL injection prevention (prepared statements)
  - XSS protection (input sanitization)
  - Password encryption
  - Role-based access control
  - Session security
  - Activity logging with IP tracking

## Technologies

### Backend
- **PHP 7.4+** - Server-side logic and database interaction
- **MySQL 5.7+** - Relational database management

### Frontend
- **HTML5** - Structure and semantic markup
- **CSS3** - Styling with gradients, flexbox, and grid
- **JavaScript** - Client-side validation and interactivity

### Server
- **Apache** - Web server (via XAMPP)

## Database Schema

The system includes 10 main tables:

1. **users** - Main authentication table
2. **admins** - Admin-specific data
3. **donors** - Donor profiles with medical info
4. **hospitals** - Hospital information
5. **blood_inventory** - Current stock levels
6. **donations** - Donation records
7. **blood_requests** - Hospital blood requests
8. **appointments** - Scheduled donations
9. **notifications** - User notifications
10. **system_logs** - Activity audit trail

## Getting Started

### Prerequisites

- XAMPP (or similar Apache + MySQL + PHP stack)
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Modern web browser

### Installation

1. **Clone or download the repository:**
   ```bash
   git clone https://github.com/revankumarz/blood-donation-management.git
   ```

2. **Copy to XAMPP htdocs:**
   ```
   Copy the project folder to: C:\xampp\htdocs\dbms-project\
   ```

3. **Start XAMPP Services:**
   - Open XAMPP Control Panel
   - Start Apache
   - Start MySQL

4. **Create the Database:**
   - Open phpMyAdmin: `http://localhost/phpmyadmin`
   - Go to SQL tab
   - Copy and paste contents of `database.sql`
   - Click "Go" to execute

5. **Verify Configuration:**
   - Check `config.php` for correct database credentials
   - Default: host=localhost, user=root, password=(blank)

6. **Access the Application:**
   ```
   http://localhost/dbms-project/
   ```

For detailed installation instructions, see [INSTALLATION.md](INSTALLATION.md)

### Default Login Credentials

**Admin Account:**
- Username: `admin`
- Password: `admin123`

**Admin Passkey (for new admin registration):** `admin123`

**Note:** Change these credentials immediately after first login in production!

## Usage

### For Donors

1. Register at `/register.php` selecting "Donor" role
2. Fill in personal and medical information
3. Login to access donor dashboard
4. Schedule donation appointments
5. Track donation history and eligibility

### For Hospitals

1. Register at `/register.php` selecting "Hospital" role
2. Fill in hospital information
3. Login to access hospital dashboard
4. Submit blood requests with urgency levels
5. Track request status
6. View available blood inventory

### For Admins

1. Login with admin credentials
2. Monitor overall system statistics
3. Manage blood inventory (add/remove stock)
4. View all donors and hospitals
5. Track blood requests
6. Review system logs

## Project Structure

```
dbms-project/
├── config.php              # Database configuration and helper functions
├── database.sql            # Complete database schema
├── index.html              # Landing page
├── index.css               # Landing page styles
├── login.php               # Login page
├── login.css               # Login page styles
├── register.php            # Registration page
├── register.css            # Registration styles
├── logout.php              # Logout handler
├── unauthorized.php        # 403 error page
├── dashboard.css           # Dashboard styles (shared)
├── admin_dashboard.php     # Admin main dashboard
├── donor_dashboard.php     # Donor main dashboard
├── hospital_dashboard.php  # Hospital main dashboard
├── manage_inventory.php    # Blood inventory management
├── INSTALLATION.md         # Detailed installation guide
└── README.md               # This file
```

## Security Considerations

- All user inputs are sanitized using `htmlspecialchars()` and `real_escape_string()`
- Passwords are hashed using PHP's `password_hash()` with bcrypt
- SQL queries use prepared statements to prevent SQL injection
- Role-based access control prevents unauthorized access
- Session management with secure configurations
- Activity logging for audit trails

## Future Enhancements

- Email notifications for urgent blood requests
- SMS alerts for eligible donors
- Advanced reporting and analytics
- Donor search by blood type and location
- Mobile-responsive design improvements
- Export data to PDF/Excel
- Blood donation certificates
- Donor rewards/badges system
- Integration with hospital management systems

## Contributing

This is a collaborative project developed by a team of 6 developers.

**Team members should:**
1. Pull the latest changes before starting work: `git pull`
2. Create a feature branch: `git checkout -b feature-name`
3. Make your changes
4. Commit with descriptive messages: `git commit -m "Add feature description"`
5. Push to your branch: `git push origin feature-name`
6. Create a Pull Request for review

## Testing

### Test the Complete Workflow

1. **Register a donor** with blood type O+
2. **Login as admin** and add blood inventory
3. **Register a hospital**
4. **Login as hospital** and submit blood request
5. **Login as donor** and schedule appointment
6. **Login as admin** and verify all activities in logs

## Troubleshooting

- **Connection failed**: Ensure MySQL is running in XAMPP
- **Access denied**: Check database credentials in `config.php`
- **Blank pages**: Verify Apache is running and check error logs
- **CSS not loading**: Clear browser cache

For more help, see [INSTALLATION.md](INSTALLATION.md)

## License

*To be determined*

## Contact

Project maintained by [@revankumarz](https://github.com/revankumarz)

## Acknowledgments

- Built with PHP, MySQL, HTML, CSS, and JavaScript
- Designed for educational purposes and real-world blood bank management
- Part of DBMS project coursework
