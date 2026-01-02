<?php
session_start();

/* =========================
   設定
========================= */
$ADMIN_PASSWORD = '20150114';
$UPLOAD_DIR = __DIR__ . '/../uploads';
$COUNTER_FILE = $UPLOAD_DIR . '/counter.txt';

/* =========================
   管理ログ保存
========================= */
function write_admin_log($message) {
    $logFile = __DIR__ . '/admin.log';
    $time = date('Y-m-d H:i:s');
    $line = "[$time] $message\n";
    file_put_contents($logFile, $line, FILE_APPEND | LOCK_EX);
}

/* =========================
   ログイン処理
========================= */
if (!isset($_SESSION['admin'])) {
    if (isset($_POST['password'])) {
        if ($_POST['password'] === $ADMIN_PASSWORD) {
            $_SESSION['admin'] = true;
            header('Location: index.php');
            exit;
        } else {
            $error = 'パスワードが違います';
        }
    }
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>管理ログイン</title>
</head>
<body>
<h2>管理画面ログイン</h2>
<?php if (!empty($error)) echo "<p style='color:red'>$error</p>"; ?>
<form method="post">
    <input type="password" name="password" placeholder="管理パスワード">
    <button type="submit">ログイン</button>
</form>
</body>
</html>
<?php
    exit;
}

/* =========================
   処理メッセージ
========================= */
$message = '';

/* =========================
   画像削除
========================= */
if (isset($_POST['delete_id'])) {
    $id = basename($_POST['delete_id']);
    $metaFile = "$UPLOAD_DIR/$id.txt";

    $comment = '';
    $key = '';

    if (file_exists($metaFile)) {
        $data = json_decode(file_get_contents($metaFile), true);
        $comment = $data['comment'] ?? '';
        $key = $data['key'] ?? '';
    }

    foreach (glob("$UPLOAD_DIR/$id.*") as $file) {
        unlink($file);
    }

    write_admin_log("DELETE id=$id key=$key comment=\"$comment\"");
    $message = "ID {$id} を削除しました";
}

/* =========================
   連番初期化
========================= */
if (isset($_POST['reset_counter'])) {
    file_put_contents($COUNTER_FILE, "1");
    write_admin_log("RESET_COUNTER");
    $message = "アップロード連番を初期化しました（次は0001）";
}

/* =========================
   一覧取得
========================= */
$items = [];
foreach (glob("$UPLOAD_DIR/*.txt") as $meta) {
    $id = basename($meta, '.txt');
    $data = json_decode(file_get_contents($meta), true);
    if (!$data) continue;

    $items[] = [
        'id' => $id,
        'comment' => $data['comment'] ?? '',
        'key' => $data['key'] ?? ''
    ];
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>管理画面</title>
<style>
table { border-collapse: collapse; width: 100%; }
th, td { border: 1px solid #ccc; padding: 6px; }
th { background: #eee; }
</style>
</head>
<body>

<h2>画像アップローダー 管理画面</h2>

<?php if ($message) echo "<p style='color:green'>$message</p>"; ?>

<h3>アップロード一覧</h3>
<table>
<tr>
    <th>ID</th>
    <th>コメント</th>
    <th>削除キー</th>
    <th>操作</th>
</tr>
<?php foreach ($items as $item): ?>
<tr>
    <td><?= htmlspecialchars($item['id']) ?></td>
    <td><?= htmlspecialchars($item['comment']) ?></td>
    <td><?= htmlspecialchars($item['key']) ?></td>
    <td>
        <form method="post" onsubmit="return confirm('本当に削除しますか？');">
            <input type="hidden" name="delete_id" value="<?= htmlspecialchars($item['id']) ?>">
            <button type="submit">削除</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
</table>

<h3>管理操作</h3>
<form method="post" onsubmit="return confirm('連番を初期化しますか？');">
    <button type="submit" name="reset_counter">アップロード連番を初期化</button>
</form>

</body>
</html>
