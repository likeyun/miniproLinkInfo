<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

$url = trim($_GET['url'] ?? '');

if (!$url) {
    http_response_code(400);
    exit('缺少 url 参数');
}

$url = preg_replace('/^http:/i', 'https:', $url);

$parsed = parse_url($url);

if (
    !$parsed ||
    empty($parsed['scheme']) ||
    empty($parsed['host']) ||
    strtolower($parsed['scheme']) !== 'https'
) {
    http_response_code(400);
    exit('图片地址不合法');
}

$host = strtolower($parsed['host']);

// 仅允许微信图片域名
$allowedHosts = [
    'mmbiz.qpic.cn',
    'mmbiz.qlogo.cn',
    'wx.qlogo.cn'
];

$allowed = false;

foreach ($allowedHosts as $allowedHost) {
    if ($host === $allowedHost || str_ends_with($host, '.' . $allowedHost)) {
        $allowed = true;
        break;
    }
}

if (!$allowed) {
    http_response_code(403);
    exit('不允许代理该域名');
}

$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_MAXREDIRS => 3,
    CURLOPT_CONNECTTIMEOUT => 5,
    CURLOPT_TIMEOUT => 15,
    CURLOPT_ENCODING => '',
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
    CURLOPT_HTTPHEADER => [
        'Accept: image/avif,image/webp,image/apng,image/svg+xml,image/*,*/*;q=0.8',
        'Referer: https://mp.weixin.qq.com/',
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/134.0.0.0 Safari/537.36'
    ]
]);

$data = curl_exec($ch);

if ($data === false) {
    http_response_code(502);
    exit('图片获取失败');
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

curl_close($ch);

if ($httpCode !== 200) {
    http_response_code($httpCode);
    exit('图片获取失败');
}

if (!$contentType || stripos($contentType, 'image/') !== 0) {
    http_response_code(403);
    exit('返回内容不是图片');
}

header('Content-Type: ' . $contentType);
header('Cache-Control: public, max-age=86400');
header('X-Content-Type-Options: nosniff');

echo $data;