# Email Accounts Setup - dave@mygigguide.co.za and noreply@mygigguide.co.za

## Current Status
✅ Password hashes generated
✅ SQL script created
⚠️ MariaDB needs to be reinstalled/started
⚠️ Database and accounts need to be created

## Step 1: Fix MariaDB (if needed)

```bash
# Reinstall MariaDB server if missing
sudo apt install -y mariadb-server

# Start MariaDB
sudo systemctl start mariadb
sudo systemctl enable mariadb

# Verify it's running
sudo systemctl status mariadb
```

## Step 2: Run the Setup Script

```bash
# Make script executable (if not already)
chmod +x /var/www/mygigguide/scripts/setup-mail-database-and-accounts.sh

# Run the setup script
sudo /var/www/mygigguide/scripts/setup-mail-database-and-accounts.sh
```

## Step 3: Manual Setup (if script doesn't work)

### Create Database and Tables

```bash
mysql -u root -p
```

Then run:

```sql
CREATE DATABASE IF NOT EXISTS mailserver;

USE mailserver;

CREATE TABLE IF NOT EXISTS virtual_domains (
  id INT NOT NULL AUTO_INCREMENT,
  name VARCHAR(50) NOT NULL,
  PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS virtual_users (
  id INT NOT NULL AUTO_INCREMENT,
  domain_id INT NOT NULL,
  password VARCHAR(106) NOT NULL,
  email VARCHAR(120) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY email (email),
  FOREIGN KEY (domain_id) REFERENCES virtual_domains(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS virtual_aliases (
  id INT NOT NULL AUTO_INCREMENT,
  domain_id INT NOT NULL,
  source VARCHAR(100) NOT NULL,
  destination VARCHAR(100) NOT NULL,
  PRIMARY KEY (id),
  FOREIGN KEY (domain_id) REFERENCES virtual_domains(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

INSERT IGNORE INTO virtual_domains (name) VALUES ('mygigguide.co.za');
```

### Create Email Accounts

```sql
USE mailserver;

SET @domain_id = (SELECT id FROM virtual_domains WHERE name = 'mygigguide.co.za' LIMIT 1);

-- dave@mygigguide.co.za (Password: Dave123!)
INSERT INTO virtual_users (domain_id, password, email) 
VALUES (@domain_id, '{SHA512-CRYPT}$6$CunDF05W76cWI2hZ$agCaoip5BIMdvIBsKD9uhiQUb8nM5RgwvWlUUHCIT3yQc2mQ/vs1KMhsEFTpWjXlprUbMlnJFgz4z26Oa1Z3j.', 'dave@mygigguide.co.za')
ON DUPLICATE KEY UPDATE password = VALUES(password);

-- noreply@mygigguide.co.za (Password: ew&G87bqxu!)
INSERT INTO virtual_users (domain_id, password, email) 
VALUES (@domain_id, '{SHA512-CRYPT}$6$r7gtLtgITmaInA5l$/rN/ex4dqwSiL0ya0qKFujUkVe.rc6A3q66RtazpzLEJoEj6AZjjinE22PqTVxU4dibgxZQ.UIxxrhfzvln7o.', 'noreply@mygigguide.co.za')
ON DUPLICATE KEY UPDATE password = VALUES(password);

-- Verify accounts
SELECT id, email FROM virtual_users WHERE email IN ('dave@mygigguide.co.za', 'noreply@mygigguide.co.za');

EXIT;
```

## Step 4: Verify Accounts

```bash
mysql -u root -p mailserver -e "SELECT id, email FROM virtual_users WHERE email IN ('dave@mygigguide.co.za', 'noreply@mygigguide.co.za');"
```

## Email Account Details

### Account 1: dave@mygigguide.co.za
- **Password**: Dave123!
- **Password Hash**: `{SHA512-CRYPT}$6$CunDF05W76cWI2hZ$agCaoip5BIMdvIBsKD9uhiQUb8nM5RgwvWlUUHCIT3yQc2mQ/vs1KMhsEFTpWjXlprUbMlnJFgz4z26Oa1Z3j.`

### Account 2: noreply@mygigguide.co.za
- **Password**: ew&G87bqxu!
- **Password Hash**: `{SHA512-CRYPT}$6$r7gtLtgITmaInA5l$/rN/ex4dqwSiL0ya0qKFujUkVe.rc6A3q66RtazpzLEJoEj6AZjjinE22PqTVxU4dibgxZQ.UIxxrhfzvln7o.`

## Next Steps

After creating the accounts, you still need to:
1. Configure Postfix to use MySQL authentication
2. Configure Dovecot to use MySQL authentication  
3. Set up nginx for Roundcube webmail
4. Configure DNS records (MX, SPF, DKIM, DMARC)

See `/var/www/mygigguide/EMAIL_SETUP_COMPLETE.md` for full configuration details.


