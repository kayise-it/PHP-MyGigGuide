#!/bin/bash
# Complete Email Server Configuration Script
# Run this after Postfix is installed

DOMAIN="mygigguide.co.za"
MAIL_DB="mailserver"
ROUNDCUBE_DB="roundcube"

echo "=== Configuring Email Server ==="

# Check if running as root
if [ "$EUID" -ne 0 ]; then 
    echo "Please run as root (use sudo)"
    exit 1
fi

# 1. Create mail database if it doesn't exist
echo "Setting up mail database..."
mysql -u root <<EOF
CREATE DATABASE IF NOT EXISTS ${MAIL_DB};
CREATE DATABASE IF NOT EXISTS ${ROUNDCUBE_DB};

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

echo "Database configured!"
echo ""
echo "To create an email account:"
echo "1. Generate password: doveadm pw -s SHA512-CRYPT"
echo "2. Insert into database:"
echo "   mysql -u root -p ${MAIL_DB}"
echo "   INSERT INTO virtual_users (domain_id, password, email) VALUES (1, '{SHA512-CRYPT}\$6\$...', 'user@${DOMAIN}');"


