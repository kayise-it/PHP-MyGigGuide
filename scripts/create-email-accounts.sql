-- Email Account Creation Script
-- Run this after the mailserver database is set up

USE mailserver;

-- Ensure domain exists
INSERT IGNORE INTO virtual_domains (name) VALUES ('mygigguide.co.za');

-- Get domain ID (assuming it's 1, but we'll use a subquery to be safe)
SET @domain_id = (SELECT id FROM virtual_domains WHERE name = 'mygigguide.co.za' LIMIT 1);

-- Create dave@mygigguide.co.za account
-- Password: Dave123!
-- Hash generated: {SHA512-CRYPT}$6$CunDF05W76cWI2hZ$agCaoip5BIMdvIBsKD9uhiQUb8nM5RgwvWlUUHCIT3yQc2mQ/vs1KMhsEFTpWjXlprUbMlnJFgz4z26Oa1Z3j.
INSERT INTO virtual_users (domain_id, password, email) 
VALUES (@domain_id, '{SHA512-CRYPT}$6$CunDF05W76cWI2hZ$agCaoip5BIMdvIBsKD9uhiQUb8nM5RgwvWlUUHCIT3yQc2mQ/vs1KMhsEFTpWjXlprUbMlnJFgz4z26Oa1Z3j.', 'dave@mygigguide.co.za')
ON DUPLICATE KEY UPDATE password = VALUES(password);

-- Create noreply@mygigguide.co.za account
-- Password: ew&G87bqxu!
-- Hash generated: {SHA512-CRYPT}$6$r7gtLtgITmaInA5l$/rN/ex4dqwSiL0ya0qKFujUkVe.rc6A3q66RtazpzLEJoEj6AZjjinE22PqTVxU4dibgxZQ.UIxxrhfzvln7o.
INSERT INTO virtual_users (domain_id, password, email) 
VALUES (@domain_id, '{SHA512-CRYPT}$6$r7gtLtgITmaInA5l$/rN/ex4dqwSiL0ya0qKFujUkVe.rc6A3q66RtazpzLEJoEj6AZjjinE22PqTVxU4dibgxZQ.UIxxrhfzvln7o.', 'noreply@mygigguide.co.za')
ON DUPLICATE KEY UPDATE password = VALUES(password);

-- Verify accounts were created
SELECT id, email, LEFT(password, 20) as password_preview FROM virtual_users WHERE email IN ('dave@mygigguide.co.za', 'noreply@mygigguide.co.za');


