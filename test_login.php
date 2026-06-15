<?php
$ch = curl_init('http://localhost:8000/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
$response = curl_exec($ch);
list($header, $body) = explode("\r\n\r\n", $response, 2);

preg_match('/name="_token" value="(.*?)"/', $body, $matches);
$token = $matches[1] ?? '';
preg_match('/PHPSESSID=(.*?);/', $header, $matches);
$cookie = $matches[1] ?? '';

// POST login
$ch2 = curl_init('http://localhost:8000/login');
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_HEADER, true);
curl_setopt($ch2, CURLOPT_POST, true);
curl_setopt($ch2, CURLOPT_POSTFIELDS, http_build_query([
    'email' => 'admin@clinic.test',
    'password' => '123456',
    '_token' => $token
]));
curl_setopt($ch2, CURLOPT_COOKIE, "PHPSESSID=$cookie");
$response2 = curl_exec($ch2);
list($header2, $body2) = explode("\r\n\r\n", $response2, 2);

// After login, session ID is regenerated! We need the NEW cookie.
preg_match_all('/Set-Cookie: PHPSESSID=(.*?);/', $header2, $matches2);
$new_cookie = end($matches2[1]);
if (!$new_cookie) {
    $new_cookie = $cookie; // fallback
}

// GET dashboard
$ch3 = curl_init('http://localhost:8000/dashboard');
curl_setopt($ch3, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch3, CURLOPT_HEADER, true);
curl_setopt($ch3, CURLOPT_COOKIE, "PHPSESSID=$new_cookie");
$response3 = curl_exec($ch3);
list($header3, $body3) = explode("\r\n\r\n", $response3, 2);

echo "Dashboard status:\n$header3\n";
if (strpos($header3, '302 Found') !== false) {
    echo "Redirected to: " . preg_match('/Location: (.*)/', $header3, $m) ? $m[1] : '' . "\n";
} else {
    echo "Success! Dashboard loaded.\n";
}
