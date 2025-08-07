<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

function getNameFromAlfalearning($nik) {
    // Initialize curl session
    $ch = curl_init();
    
    // Set curl options
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_COOKIEJAR => '/tmp/cookies.txt',
        CURLOPT_COOKIEFILE => '/tmp/cookies.txt',
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36'
    ]);
    
    // Step 1: Login
    curl_setopt($ch, CURLOPT_URL, 'https://alfalearning.sat.co.id/login/index.php');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'username' => '22088181',
        'password' => 'A12345678'
    ]));
    
    $login_response = curl_exec($ch);
    
    if (curl_error($ch)) {
        return ['error' => 'Login failed: ' . curl_error($ch)];
    }
    
    // Step 2: Search for NIK
    curl_setopt($ch, CURLOPT_URL, "https://alfalearning.sat.co.id/search/index.php?context=1&q={$nik}");
    curl_setopt($ch, CURLOPT_POST, false);
    
    $search_response = curl_exec($ch);
    
    if (curl_error($ch)) {
        curl_close($ch);
        return ['error' => 'Search failed: ' . curl_error($ch)];
    }
    
    curl_close($ch);
    
    // Parse HTML to extract name
    $dom = new DOMDocument();
    @$dom->loadHTML($search_response);
    
    // Find name in the HTML (adjust selector based on actual HTML structure)
    $xpath = new DOMXPath($dom);
    
    // Try different selectors to find the name
    $possible_selectors = [
        "//td[contains(text(), '{$nik}')]/following-sibling::td[1]",
        "//tr[contains(., '{$nik}')]//td[2]",
        "//*[contains(text(), '{$nik}')]/parent::*//*[contains(@class, 'name')]"
    ];
    
    foreach ($possible_selectors as $selector) {
        $nodes = $xpath->query($selector);
        if ($nodes->length > 0) {
            $name = trim($nodes->item(0)->textContent);
            if (!empty($name) && $name !== $nik) {
                return ['success' => true, 'name' => $name, 'nik' => $nik];
            }
        }
    }
    
    return ['error' => 'Name not found for NIK: ' . $nik];
}

// Handle request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $nik = $input['nik'] ?? '';
} else {
    $nik = $_GET['nik'] ?? '';
}

if (empty($nik)) {
    echo json_encode(['error' => 'NIK is required']);
    exit;
}

$result = getNameFromAlfalearning($nik);
echo json_encode($result);
?>
