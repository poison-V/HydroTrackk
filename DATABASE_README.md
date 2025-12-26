# 🗄️ HydroTrack Database Setup Guide

This guide will help you set up the database for the HydroTrack POS system.

## 📋 Prerequisites

- XAMPP installed and running
- MySQL/MariaDB service started
- phpMyAdmin accessible at `http://localhost/phpmyadmin`

## 🚀 Quick Setup (Recommended)

### Method 1: Using phpMyAdmin (Easiest)

1. **Start XAMPP**
   - Open XAMPP Control Panel
   - Start **Apache** and **MySQL** services

2. **Open phpMyAdmin**
   - Navigate to `http://localhost/phpmyadmin` in your browser

3. **Import Database**
   - Click on the **"Import"** tab at the top
   - Click **"Choose File"** button
   - Select `database_setup.sql` from your HydroTrack folder
   - Scroll down and click **"Go"** button
   - Wait for the success message

4. **Verify Setup**
   - Click on `hydrotrack_db` in the left sidebar
   - You should see 7 tables: `users`, `customers`, `products`, `inventory`, `transactions`, `stock_history`, `expenses`

### Method 2: Using MySQL Command Line

```bash
# Navigate to HydroTrack directory
cd c:\xampp\htdocs\HydroTrack

# Run the setup script
mysql -u root -p < database_setup.sql

# Press Enter when prompted for password (default is empty)
```

## 🔐 Default Login Credentials

After setup, you can login with these test accounts:

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@gmail.com | password123 |
| Cashier | cashier@gmail.com | password123 |
| Manager | manager@gmail.com | password123 |

## 📊 Database Structure

### Tables Overview

| Table | Description | Records |
|-------|-------------|---------|
| **users** | User accounts with roles | 3 sample users |
| **customers** | Customer information | 5 sample customers |
| **products** | Gallon sizes and pricing | 4 products |
| **inventory** | Individual gallon tracking | 11 items |
| **transactions** | Sales records | 5 transactions |
| **stock_history** | Stock movements | 7 records |
| **expenses** | Business expenses | 5 records |

### Product Pricing (Sample Data)

| Size | Refill Price | Borrow Price |
|------|--------------|--------------|
| 20L Slim | ₱25.00 | ₱150.00 |
| 20L Round | ₱25.00 | ₱150.00 |
| 10L | ₱15.00 | ₱80.00 |
| 5L | ₱10.00 | ₱50.00 |

## ✅ Testing the Setup

### 1. Test Login
```
1. Navigate to: http://localhost/HydroTrack/
2. Login with: admin@gmail.com / password123
3. You should see the Home Dashboard
```

### 2. Check Sample Data
```
1. Go to Daily Sales Report page
2. You should see 5 sample transactions
3. Check customer names: Juan Dela Cruz, Maria Santos, etc.
```

### 3. Create New User
```
1. Logout from current account
2. Click "Create Account"
3. Fill in the form with your details
4. Login with your new account
```

## 🔧 Troubleshooting

### Error: "Database connection failed"

**Solution:**
1. Make sure MySQL is running in XAMPP
2. Check `config.php` settings:
   - DB_HOST: `localhost`
   - DB_NAME: `hydrotrack_db`
   - DB_USER: `root`
   - DB_PASS: `` (empty)

### Error: "Table already exists"

**Solution:**
1. The database already exists
2. To reset: Drop the database in phpMyAdmin first
3. Then re-import `database_setup.sql`

### Can't login with sample accounts

**Solution:**
1. Verify the database was imported successfully
2. Check if `users` table has 3 records
3. Try creating a new account via signup page

## 📝 Customizing the Data

### Change Product Prices

```sql
-- Open phpMyAdmin > hydrotrack_db > products > Edit
UPDATE products SET price_refill = 30.00 WHERE size_key = '20LiterSlim';
```

### Add More Sample Customers

```sql
INSERT INTO customers (full_name, phone, email, address) 
VALUES ('Your Name', '09123456789', 'email@gmail.com', 'Your Address');
```

### Change User Password

```sql
-- Password hash for 'newpassword123'
UPDATE users 
SET password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi' 
WHERE email = 'admin@gmail.com';
```

## 🎯 Next Steps

1. ✅ Database is now set up
2. 🔐 Login with test credentials
3. 🛒 Start creating transactions
4. 📊 View reports and analytics
5. 👥 Add your real customers
6. 💰 Track your actual sales

## 📞 Need Help?

If you encounter any issues:
1. Check XAMPP MySQL service is running
2. Verify `config.php` database settings
3. Check phpMyAdmin for error messages
4. Review the database structure in phpMyAdmin

---

**Happy Tracking! 💧**
