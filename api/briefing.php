<?php
// PHP 8.1+ with cURL. All upstream URLs are fixed; only the ICAO code is variable.
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
function respond(array $body, int $status = 200): never {
    http_response_code($status);
    echo json_encode($body, JSON_INVALID_UTF8_SUBSTITUTE | JSON_UNESCAPED_SLASHES);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'GET' && $_SERVER['REQUEST_METHOD'] !== 'HEAD') {
    header('Allow: GET, HEAD'); respond(['error' => 'Method not allowed.'], 405);
}
$code = strtoupper((string)($_GET['airport'] ?? ''));
if (!preg_match('/^[A-Z0-9]{4}$/D', $code)) {
    respond(['error' => 'Enter a four-character ICAO airport code, such as KIWA or EGLL.'], 400);
}
if (!function_exists('curl_multi_init')) {
    respond(['error' => 'This server needs the PHP cURL extension enabled.'], 503);
}
$cacheFile = sys_get_temp_dir() . '/crosswind-v1-' . $code . '.json';
if (is_file($cacheFile) && filemtime($cacheFile) > time() - 300) {
    $cached = json_decode((string)file_get_contents($cacheFile), true);
    if (is_array($cached) && isset($cached['airport'])) {
        header('Cache-Control: private, max-age=60'); respond($cached);
    }
}
$urls = [
    "https://aviationweather.gov/api/data/airport?ids=$code&format=json",
    "https://aviationweather.gov/api/data/metar?ids=$code&format=json",
    "https://aviationweather.gov/api/data/taf?ids=$code&format=json",
    "https://mesonet.agron.iastate.edu/api/1/mos.json?station=$code&model=GFS"
];
$multi = curl_multi_init();
$handles = [];
foreach ($urls as $url) {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 6,
        CURLOPT_TIMEOUT => 14,
        CURLOPT_USERAGENT => 'CrosswindFlightdesk/1.0 (aviation planning research)',
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);
    curl_multi_add_handle($multi, $ch);
    $handles[] = $ch;
}
do {
    $status = curl_multi_exec($multi, $running);
    if ($running) curl_multi_select($multi, 1.0);
} while ($running && $status === CURLM_OK);
$values = [];
$errors = [];
$labels = ['Airport', 'METAR', 'TAF', 'MOS'];
foreach ($handles as $i => $ch) {
    $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $body = curl_multi_getcontent($ch);
    $decoded = $http === 204 ? [] : json_decode((string)$body, true);
    $good = curl_errno($ch) === 0 && $http >= 200 && $http < 300 && is_array($decoded);
    $values[$i] = $good ? $decoded : null;
    if (!$good) $errors[] = $labels[$i];
    curl_multi_remove_handle($multi, $ch);
    curl_close($ch);
}
curl_multi_close($multi);
$airport = $values[0][0] ?? null;
if (!$airport) {
    respond(['error' => in_array('Airport', $errors, true)
        ? 'Airport service is unavailable. Please try again.'
        : 'Airport not found in the AWC database. Check the ICAO code.'], 502);
}
$mos = array_map(static function (array $m): array {
    return [
        'time' => rtrim((string)$m['ftime_utc'], 'Z') . 'Z',
        'issued' => rtrim((string)$m['runtime_utc'], 'Z') . 'Z',
        'direction' => $m['wdr'] ?? null,
        'speed' => $m['wsp'] ?? null,
    ];
}, $values[3]['data'] ?? []);
$data = [
    'airport' => $airport,
    'metar' => $values[1][0] ?? null,
    'taf' => $values[2][0] ?? null,
    'mos' => $mos,
    'retrieved' => gmdate('Y-m-d\TH:i:s\Z'),
    'errors' => $errors,
];
// Failure to cache does not prevent returning live data.
@file_put_contents($cacheFile, json_encode($data), LOCK_EX);
header('Cache-Control: private, max-age=60');
respond($data);
