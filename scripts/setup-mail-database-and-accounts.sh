#!/bin/bash
# Complete mail database and account setup script

set -e

DOMAIN="mygigguide.co.za"
MAIL_DB="mailserver"

echo "=== Setting up Mail Database and Accounts ==="

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo "Please run as root (use sudo)"
    exit 1
fi

# Check if MySQL/MariaDB is running
if ! systemctl is-active --quiet mariadb && ! systemctl is-active --quiet mysql; then
    echo "Starting database service..."
    systemctl start mariadb 2>/dev/null || systemctl start mysql 2>/dev/null || {
        echo "ERROR: Cannot start database service. Please start it manually."
        exit 1
    }
    sleep 2
fi

# Create database and tables
echo "Creating mail database and tables..."
mysql -u root <<EOF
CREATE DATABASE IF NOT EXISTS ${MAIL_DB};

USE ${MAIL_DB};

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

INSERT IGNORE INTO virtual_domains (name) VALUES ('${DOMAIN}');
EOF

# Create email accounts
echo "Creating email accounts..."
mysql -u root ${MAIL_DB} < /var/www/mygigguide/scripts/create-email-accounts.sql

echo ""
echo "✅ Email accounts created successfully!"
echo ""
echo "Accounts:"
echo "  - dave@mygigguide.co.za"
echo "  - noreply@mygigguide.co.za"
echo ""
echo "Next steps:"
echo "1. Configure Postfix to use MySQL authentication"
echo "2. Configure Dovecot to use MySQL authentication"
echo "3. Set up nginx for Roundcube webmail"
echo "4. Configure DNS records (MX, SPF, etc.)"


