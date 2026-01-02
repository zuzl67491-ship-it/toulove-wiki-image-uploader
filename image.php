<?php
// image.php
// TouLove Wiki Image Uploader
// 直リンク防止用 画像配信スクリプト

// ==========================
// 基本設定
// ==========================

// 画像保存ディレクトリ（実環境に合わせて変更）
$UPLOAD_DIR = __DIR__ . '/uploads';

// 許可するMIMEタイプ
$ALLOWED_MIME = [
    'image/jpeg',
    'image/png',
    'image/gif'
];

// ==========================
// アクセス制御
// ==========================

// POST以外は拒否（直リンク防止）
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(403);
    exit('Forbidden');
}

// ID取得
$id = $_POST['id'] ?? null;
if (!$id || !preg_match('/^\d+$/', $id)) {
    http_response_code(400);
    exit('Bad Request');
}

// ==========================
// 画像ファイル取得
// ==========================

// ※ 今は「ID.png / ID.jpg」形式を仮定
//   後でDB化する前提
$patterns = [
    "$UPLOAD_DIR/$id.jpg",
    "$UPLOAD_DIR/$id.jpeg",
    "$UPLOAD_DIR/$id.png",
    "$UPLOAD_DIR/$id.gif",
];

$filePath = null;
foreach ($patterns as $p) {
    if (file_exists($p)) {
        $filePath = $p;
        break;
    }
}

if (!$filePath) {
    http_response_code(404);
    exit('File not found');
}

// MIME判定
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $filePath);
finfo_close($finfo);

if (!in_array($mime, $ALLOWED_MIME, true)) {
    http_response_code(403);
    exit('Invalid file type');
}

// ==========================
// 画像出力
// ==========================

// キャッシュ制御（Wiki直貼り想定）
header('Content-Type: ' . $mime);
header('Content-Length: ' . filesize($filePath));
header('Cache-Control: private, max-age=3600');
header('X-Content-Type-Options: nosniff');

// ファイル送信
readfile($filePath);
exit;
