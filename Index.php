<?php
// URL target dari query ?url=
if (!isset($_GET['url'])) {
    die("URL tidak diberikan.");
}
$url = $_GET['url'];

// Ambil konten target
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$content = curl_exec($ch);
curl_close($ch);

// Hapus header X-Frame-Options atau CSP dari HTML
$content = preg_replace('/<meta[^>]*http-equiv="X-Frame-Options"[^>]*>/i', '', $content);
$content = preg_replace('/<meta[^>]*http-equiv="Content-Security-Policy"[^>]*>/i', '', $content);

// Kirimkan ke browser
header("Content-Type: text/html; charset=UTF-8");
echo $content;
