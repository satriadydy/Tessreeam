<?php
header('Content-Type: application/json');

$nik = $_GET['nik'] ?? '';
if (!$nik) {
    echo json_encode(["error" => "NIK tidak ditemukan"]);
    exit;
}

$username = "22088181";
$password = "A12345678";

$loginUrl = "https://alfalearning.sat.co.id/login/index.php";
$searchUrl = "https://alfalearning.sat.co.id/search/index.php?context=1&q=" . urlencode($nik);
$cookieFile = tempnam(sys_get_temp_dir(), 'cookie');

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $loginUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'username' => $username,
    'password' => $password
]));
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_exec($ch);

curl_setopt($ch, CURLOPT_URL, $searchUrl);
curl_setopt($ch, CURLOPT_POST, false);
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$response = curl_exec($ch);
curl_close($ch);

preg_match('/<a[^>]*class="result-title[^"]*"[^>]*>(.*?)<\/a>/i', $response, $matches);
$nama = $matches[1] ?? null;

echo json_encode([
    "nik" => $nik,
    "nama" => $nama ?: "Tidak ditemukan"
]);
?>
