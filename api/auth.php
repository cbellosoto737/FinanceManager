<?php
declare(strict_types=1);
require __DIR__ . '/_bootstrap.php';

// Verifies a Google Identity Services ID token via Google's tokeninfo
// endpoint. Fine at this volume (a couple of logins a month for 2 people);
// fails closed on any network hiccup or unexpected claim rather than
// trusting a bare 200 response. If this endpoint ever feels flaky, swap in
// local JWKS verification (Google's certs + a small JWT library) -- this
// function is the only thing that would need to change.
function verify_google_id_token(string $idToken): ?array {
  $ch = curl_init('https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($idToken));
  curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 5,
    CURLOPT_CONNECTTIMEOUT => 5,
  ]);
  $response = curl_exec($ch);
  $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $curlError = curl_error($ch);
  curl_close($ch);

  if ($response === false || $curlError !== '' || $httpCode !== 200) {
    return null;
  }
  $claims = json_decode($response, true);
  if (!is_array($claims)) {
    return null;
  }
  if (($claims['aud'] ?? '') !== GOOGLE_CLIENT_ID) return null;
  $iss = $claims['iss'] ?? '';
  if ($iss !== 'https://accounts.google.com' && $iss !== 'accounts.google.com') return null;
  if (($claims['email_verified'] ?? '') !== 'true') return null;
  if (!isset($claims['exp']) || (int)$claims['exp'] < time()) return null;
  if (empty($claims['email'])) return null;

  return $claims;
}

start_secure_session();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  json_out(['error' => 'method_not_allowed'], 405);
}

require_csrf_header();

$ip = client_ip();
rate_limit_check($ip);

$body = json_input();
$credential = $body['credential'] ?? '';
if (!is_string($credential) || $credential === '') {
  json_out(['error' => 'missing_credential'], 400);
}

$claims = verify_google_id_token($credential);
$email = $claims['email'] ?? null;
$emailLower = $email !== null ? strtolower($email) : null;

if ($emailLower === null || !in_array($emailLower, array_map('strtolower', ALLOWED_EMAILS), true)) {
  log_auth_attempt($ip, false, $email);
  json_out(['error' => 'not_allowed'], 401);
}

session_regenerate_id(true);
$_SESSION['email'] = $emailLower;
issue_remember_cookie($emailLower);
log_auth_attempt($ip, true, $emailLower);

json_out(['email' => $emailLower]);
