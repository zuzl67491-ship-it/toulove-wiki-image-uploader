<?php
// upload.php
// TouLove Wiki Image Uploader

// ==========================
// 設定
// ==========================
$UPLOAD_DIR = __DIR__ . '/uploads';
$MAX_SIZE   = 100 * 1024 * 1024; // 100MB

// jpg / png のみ
$ALLOWED_MIME = [
    'image/jpeg' => 'jpg',
    'image/png'  => 'png',
];

// ==========================
// POSTチェック
// ==========================
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method Not Allowed');
}

// ==========================
// 入力チェック
// ==========================
if (
    !isset($_FILES['image']) ||
    !isset($_POST['comment']) ||
    !isset($_POST['key'])
) {
    http_response_code(400);
    exit('Bad Request');
}

$comment = trim($_POST['comment']);
$key     = $_POST['key'];

// コメント：35文字以内
if (mb_strlen($comment) > 35) {
    exit('コメントは35文字以内で入力してください');
}

// 削除キー：数字4桁
if (!preg_match('/^\d{4}$/', $key)) {
    exit('削除キーは4桁の数字で入力してください');
}

// ==========================
// ファイルチェック
// ==========================
$file = $_FILES['image'];

if ($file['error'] !== UPLOAD_ERR_OK) {
    exit('ファイルアップロードに失敗しました');
}

if ($file['size'] > $MAX_SIZE) {
    exit('ファイルサイズが大きすぎます');
}

// MIMEチェック
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime  = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!isset($ALLOWED_MIME[$mime])) {
    exit('jpg / png の画像のみアップロードできます');
}

$ext = $ALLOWED_MIME[$mime];

// ==========================
// ID採番
// ==========================
// uploads 内の最大ID + 1
$maxId = 0;
if (is_dir($UPLOAD_DIR)) {
    foreach (glob($UPLOAD_DIR . '/*.{jpg,png}', GLOB_BRACE) as $f) {
        if (preg_match('/\/(\d+)\.(jpg|png)$/', $f, $m)) {
            $maxId = max($maxId, (int)$m[1]);
        }
    }
}
$id = $maxId + 1;

// ==========================
// 保存
// ==========================
if (!is_dir($UPLOAD_DIR)) {
    mkdir($UPLOAD_DIR, 0755, true);
}

$filename = $id . '.' . $ext;
$filepath = $UPLOAD_DIR . '/' . $filename;

if (!move_uploaded_file($file['tmp_name'], $filepath)) {
    exit('ファイルの保存に失敗しました');
}

// メタ情報保存（JSON）
file_put_contents(
    $UPLOAD_DIR . '/' . $id . '.txt',
    json_encode([
        'comment'  => $comment,
        'key'      => $key,
        'created'  => date('Y-m-d H:i:s'),
        'filename' => $filename
    ], JSON_UNESCAPED_UNICODE)
);

// ==========================
// 完了画面
// ==========================
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<meta name="robots" content="noindex, nofollow">
<title>アップロード完了</title>
<style>
body { font-family:sans-serif; background:#f4f4f4; }
.box {
  max-width:600px; margin:80px auto; background:#fff;
  padding:30px; border-radius:10px; text-align:center;
}
input { width:100%; padding:10px; }
a { color:#007bff; font-weight:bold; text-decoration:none; }
</style>
</head>
<body>
<div class="box">
  <h2>アップロード完了</h2>

  <p>以下のURLを Wiki に貼り付けてください。</p>

  <input type="text"
         value="<?= htmlspecialchars("/download/$id") ?>"
         readonly>

  <p style="margin-top:20px;">
    <a href="/download/<?= $id ?>">ダウンロードページへ</a>
  </p>

  <p>
    <a href="/">アップローダーに戻る</a>
  </p>
</div>
</body>
</html>
