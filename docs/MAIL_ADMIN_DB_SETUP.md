# Mail admin database setup (`/admin/mail-accounts`)

**Pin this** — fix when Laravel shows:

`SQLSTATE[HY000] [1698] Access denied for user 'root'@'localhost' (Connection: mailserver)`

**Why:** The mail admin page reads mailbox rows from MariaDB database `mailserver`. Laravel defaults to MySQL user `root` with no password. On the VPS, `root` only works via `sudo mysql` (socket auth), not from PHP — so the page 500s.

RainLoop/webmail is **unaffected** (Postfix/Dovecot use their own config).

---

## One-time fix on VPS

SSH in:

```bash
ssh dave@41.61.20.39
# or: ssh dave@mel55-nix02
```

### 1. Create a dedicated DB user

```bash
sudo mysql
```

In the MariaDB prompt (choose a strong password — save in `docs/VPS_CREDENTIALS.local.md`):

```sql
CREATE USER IF NOT EXISTS 'mailuser'@'localhost' IDENTIFIED BY 'YOUR_STRONG_PASSWORD';
GRANT ALL PRIVILEGES ON mailserver.* TO 'mailuser'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

Quick test:

```bash
mysql -u mailuser -p mailserver -e "SELECT id, email FROM virtual_users;"
```

### 2. Add to Laravel `.env`

```bash
sudo nano /var/www/mygigguide/.env
```

Add (same password as step 1):

```env
MAIL_DB_HOST=127.0.0.1
MAIL_DB_PORT=3306
MAIL_DB_DATABASE=mailserver
MAIL_DB_USERNAME=mailuser
MAIL_DB_PASSWORD=YOUR_STRONG_PASSWORD
```

### 3. Clear config cache

```bash
cd /var/www/mygigguide
sudo -u www-data php artisan config:clear
```

### 4. Verify

Open **https://www.mygigguide.co.za/admin/mail-accounts** — should list mailboxes (`dave@`, `noreply@`, etc.).

---

## Local / `.env.example`

Template vars (for documentation only — set real values on VPS `.env`):

```env
MAIL_DB_HOST=127.0.0.1
MAIL_DB_PORT=3306
MAIL_DB_DATABASE=mailserver
MAIL_DB_USERNAME=mailuser
MAIL_DB_PASSWORD=
```

See also: `config/database.php` connection `mailserver`, `docs/VPS_CREDENTIALS.local.md`.

---

**Last updated:** May 2026
