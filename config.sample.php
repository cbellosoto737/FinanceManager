<?php
// Copy this file to "config.php", fill in real values, and upload it ONE
// DIRECTORY ABOVE public_html on Hostinger (e.g. domains/yourdomain.com/config.php),
// never inside public_html itself. api/_bootstrap.php requires it from there.
//
// After uploading, confirm from a browser that requesting this file's path
// directly returns a 404 -- it must not be web-served.

// --- MySQL ---
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');
define('DB_USER', 'your_database_user');
define('DB_PASS', 'your_database_password');

// --- Google Sign-In ---
// From Google Cloud Console > APIs & Services > Credentials > OAuth Client ID.
// Not secret by itself, but the server re-checks the token's "aud" against it.
define('GOOGLE_CLIENT_ID', 'your-client-id.apps.googleusercontent.com');

// --- Allowlist ---
// Only these Google account emails may sign in. Lowercase, exact match.
define('ALLOWED_EMAILS', [
  'you@example.com',
  'spouse@example.com',
]);
