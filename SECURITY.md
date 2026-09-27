# Security policy

Do not report a vulnerability, real API key, Webhook secret, wallet private key, or customer payment data in a public issue.

Email **support@boltutil.com** with the subject `BoltUtil WooCommerce security report`. Include the affected plugin version, reproduction steps, expected and actual behavior, and a non-sensitive proof of concept. We will acknowledge the report and coordinate a fix and disclosure with you.

Only the latest published version receives security fixes. The plugin stores a merchant API key and Webhook secret in the WordPress database, encrypted using the site's authentication salts. Protect WordPress administrator access, database backups, and those salts. Rotate credentials in BoltUtil if you believe they were exposed.
