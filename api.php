<?php

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit;

// ===== 微信公众平台配置 =====
$token = '自己抓';
$fingerprint = '自己抓';

// 必须填写当前 mp.weixin.qq.com 登录后的 Cookie
$cookie = '自己抓';

// ===== 获取参数 =====
$input = json_decode(file_get_contents('php://input'), true) ?: [];

$link = trim(
    $_GET['link']
    ?? $_POST['link']
    ?? $input['link']
    ?? ''
);

if (!$link) {
    echo json_encode([
        'code' => 400,
        'msg' => '缺少 link 参数'
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ===== 微信接口 =====
$url = 'https://mp.weixin.qq.com/cgi-bin/operate_appmsg?sub=search_weapp_link';

$postData = http_build_query([
    'link' => $link,
    'fingerprint' => $fingerprint,
    'token' => $token,
    'lang' => 'zh_CN',
    'f' => 'json',
    'ajax' => 1
]);

$referer = 'https://mp.weixin.qq.com/cgi-bin/appmsg?t=media/appmsg_edit_v2'
    . '&action=edit'
    . '&isNew=1'
    . '&type=77'
    . '&createType=0'
    . '&token=' . $token
    . '&lang=zh_CN';

$ch = curl_init();

curl_setopt_array($ch, [
    CURLOPT_URL => $url,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $postData,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_TIMEOUT => 15,
    CURLOPT_CONNECTTIMEOUT => 8,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTPHEADER => [
        'Accept: */*',
        'Content-Type: application/x-www-form-urlencoded; charset=UTF-8',
        'X-Requested-With: XMLHttpRequest',
        'Referer: ' . $referer,
        'Cookie: ' . $cookie,
        'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/134.0.0.0 Safari/537.36'
    ]
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

if ($response === false || $error) {
    echo json_encode([
        'code' => 500,
        'msg' => '请求微信接口失败',
        'error' => $error
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

$data = json_decode($response, true);

if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        'code' => 500,
        'msg' => '微信接口返回非JSON数据',
        'http_code' => $httpCode,
        'data' => $response
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode([
    'code' => 0,
    'msg' => '请求成功',
    'data' => $data
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);