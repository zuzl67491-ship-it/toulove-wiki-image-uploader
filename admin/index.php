<?php
session_start();

/* =========================
   管理画面設定
========================= */
define('ADMIN_PASSWORD', '20150114');

$UPLOAD_DIR  = dirname(__DIR__) . '/uploads';
$COUNTER_FILE = $UPLOAD_DIR . '/counter.txt';

/* =========================
   ログイン処理
========================= */
if (isset($_POST['admin_pass'])) {
    if ($_POST['admin_pass'] === ADMIN_PASSWORD) {
        $_SESSION['admin_login'] = true;
    } else {
        $error = 'パスワードが違います';
    }
}

if (empty($_SESSION['admin_login'])) {
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>管理画面ログイン</title>
<meta name="robots" content="noindex,nofollow">
<style>
body { background:#f4f4f4; font-family:sans-serif; }
.box {
  max-width:420px; margin:120px auto; background:#fff;
  padding:30px; border-radius:10px; text-align:center;
}
input,button { width:100%; padding:10px; margin-top:12px; }
button { background:#007bff; color:#fff; border:none; }
</style>
</head>
<body>
<div class="box">
<h2>管理画面ログイン</h2>
<?php if (!empty($error)) echo "<p style='color:red'>$error</p>"; ?>
<form method="post">
<input type="password" name="admin_pass" placeholder="管理パスワード">
<button>ログイン</button>
</form>
</div>
</body>
</html>
<?php
exit;
}

/* =========================
   削除処理
========================= */
if (isset($_POST['delete_id'])) {
    $id = basename($_POST['delete_id']);

    foreach (glob("$UPLOAD_DIR/$id.*") as $file) {
        unlink($file);
    }
    $message = "ID {$id} を削除しました";
}

/* =========================
   連番初期化処理
========================= */
if (isset($_POST['reset_counter'])) {
    file_put_contents($COUNTER_FILE, "1");
    $message = "アップロード連番を初期化しました（次は0001）";
}

/* =========================
   一覧取得
========================= */
$items = [];

foreach (glob("$UPLOAD_DIR/*.txt") as $meta) {
    if (basename($meta) === 'counter.txt') continue;

    $id = basename($meta, '.txt');
    $data = json_decode(file_get_contents($meta), true);
    if (!$data) continue;

    $items[$id] = $data;
}

krsort($items);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>画像アップローダー 管理画面</title>
<meta name="robots" content="noindex,nofollow">
<style>
body { background:#f4f4f4; font-family:sans-serif; }
.container {
  max-width:1100px; margin:40px auto; background:#fff;
  padding:30px; border-radius:10px;
}
table { width:100%; border-collapse:collapse; }
th,td { border:1px solid #ccc; padding:8px; font-size:0.9em; }
th { background:#f8f8f8; }
button {
  padding:6px 12px; border:none; cursor:pointer;
}
.del { background:#dc3545; color:#fff; }
.reset { background:#ff9800; color:#fff; }
.notice {
  background:#fff4f4; border:1px solid #ffb6c1;
  padding:15px; border-radius:8px; margin-bottom:20px;
}
</style>
</head>
<body>

<div class="container">
<h2>画像アップローダー 管理画面</h2>

<?php if (!empty($message)) echo "<p style='color:green'>$message</p>"; ?>

<div class="notice">
<strong>管理者向け注意</strong><br>
・削除キーは確認用です。<br>
・とうらぶと無関係な画像、不適切な画像は削除してください。<br>
・連番初期化は<strong>既存画像と被らないことを確認してから</strong>行ってください。
</div>

<form method="post" onsubmit="return confirm('アップロード連番を初期化しますか？');">
<button class="reset" name="reset_counter" value="1">
アップロード連番を初期化
</button>
</form>

<br>

<table>
<tr>
<th>ID</th>
<th>コメント</th>
<th>削除キー</th>
<th>投稿日</th>
<th>操作</th>
</tr>

<?php foreach ($items as $id => $d): ?>
<tr>
<td><?= htmlspecialchars($id) ?></td>
<td><?= htmlspecialchars($d['comment'] ?? '') ?></td>
<td><?= htmlspecialchars($d['key'] ?? '') ?></td>
<td><?= htmlspecialchars($d['created'] ?? '') ?></td>
<td>
<form method="post" onsubmit="return confirm('この画像を削除しますか？');">
<input type="hidden" name="delete_id" value="<?= $id ?>">
<button class="del">削除</button>
</form>
</td>
</tr>
<?php endforeach; ?>
</table>

</div>
</body>
</html>
