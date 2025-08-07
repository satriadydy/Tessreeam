<?php
header('Content-Type: application/json');

// Ambil NIK dari query string
$nik = $_GET['nik'] ?? '';

if (!$nik) {
    echo json_encode(["error" => "NIK tidak ditemukan"]);
    exit;
}

// Login credentials Alfalearning
$username = "22088181"; // Ganti jika punya akun lain
$password = "A12345678";

// URL login dan pencarian
$loginUrl = "https://alfalearning.sat.co.id/login/index.php";
$searchUrl = "https://alfalearning.sat.co.id/search/index.php?context=1&q=" . urlencode($nik);

// File cookie sementara
$cookieFile = tempnam(sys_get_temp_dir(), "cookie");

// Langkah 1: Login
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

// Langkah 2: Akses halaman pencarian setelah login
curl_setopt($ch, CURLOPT_URL, $searchUrl);
curl_setopt($ch, CURLOPT_POST, false);
curl_setopt($ch, CURLOPT_HTTPGET, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$response = curl_exec($ch);
curl_close($ch);

// Langkah 3: Ambil nama dari HTML hasil pencarian
preg_match('/<a[^>]*class="result-title[^"]*"[^>]*>(.*?)<\/a>/i', $response, $matches);
$nama = $matches[1] ?? null;

// Output dalam format JSON
echo json_encode([
    "nik" => $nik,
    "nama" => $nama ?: "Tidak ditemukan"
]);
?>
