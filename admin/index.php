<?php
session_start();

/* =========================
   設定
========================= */
// パスワードのハッシュ（初回生成用に下のコメントアウト部分を実行して出力されたハッシュをここに貼り付けてください）
// 例: echo password_hash('20150114', PASSWORD_DEFAULT);
define('ADMIN_PASSWORD_HASH', '$2y$10$XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX'); // ← ここにハッシュ値を設定

$UPLOAD_DIR = __DIR__ . '/../uploads';
$COUNTER_FILE = $UPLOAD_DIR . '/counter.txt';
// ログはWeb公開ディレクトリ外に配置（../admin/ などを作成して権限を厳しく）
$LOG_FILE = __DIR__ . '/../admin.log';

/* =========================
   管理ログ保存
========================= */
function write_admin_log($message) {
    global $LOG_FILE;
    $time = date('Y-m-d H:i:s');
    $line = "[$time] $message\n";
    @file_put_contents($LOG_FILE, $line, FILE_APPEND | LOCK_EX);
}

/* =========================
   ログイン処理
========================= */
if (!isset($_SESSION['admin'])) {
    $error = '';

    if (isset($_POST['password'])) {
        if (password_verify($_POST['password'], ADMIN_PASSWORD_HASH)) {
            session_regenerate_id(true); // セッション固定攻撃対策
            $_SESSION['admin'] = true;
            header('Location: index.php');
            exit;
        } else {
            $error = 'パスワードが違います';
            write_admin_log("LOGIN_FAILED from IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        }
    }
    ?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>管理ログイン</title>
<style>
body { font-family: sans-serif; max-width: 400px; margin: 50px auto; }
input[type=password] { width: 100%; padding: 10px; font-size: 16px; }
button { padding: 10px 20px; font-size: 16px; margin-top: 10px; }
.error { color: red; }
</style>
</head>
<body>
<h2>管理画面ログイン</h2>
<?php if ($error): ?>
<p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>
<form method="post">
    <input type="password" name="password" placeholder="管理パスワード" required autofocus>
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
    // IDは数字4桁であることを前提にさらに厳しくチェック（必要に応じて調整）
    if (!preg_match('/^\d{4,}$/', $id)) {
        $message = "不正なID形式です";
    } else {
        $metaFile = "$UPLOAD_DIR/{$id}.txt";

        $comment = '';
        $key = '';

        if (file_exists($metaFile)) {
            $data = json_decode(@file_get_contents($metaFile), true);
            if ($data) {
                $comment = $data['comment'] ?? '';
                $key = $data['key'] ?? '';
            }
        }

        $deleted = false;
        foreach (glob("$UPLOAD_DIR/{$id}.*") as $file) {
            if (is_file($file) && @unlink($file)) {
                $deleted = true;
            }
        }
        if (file_exists($metaFile) && @unlink($metaFile)) {
            $deleted = true;
        }

        if ($deleted) {
            write_admin_log("DELETE id=$id key=$key comment=\"$comment\"");
            $message = "ID {$id} を削除しました";
        } else {
            $message = "ID {$id} の削除に失敗しました（ファイルが存在しない可能性があります）";
        }
    }
}

/* =========================
   連番初期化
========================= */
if (isset($_POST['reset_counter'])) {
    if (@file_put_contents($COUNTER_FILE, "1") !== false) {
        write_admin_log("RESET_COUNTER");
        $message = "アップロード連番を初期化しました（次は0001）";
    } else {
        $message = "カウンター初期化に失敗しました";
    }
}

/* =========================
   一覧取得
========================= */
$items = [];
foreach (glob("$UPLOAD_DIR/*.txt") as $meta) {
    $id = basename($meta, '.txt');
    // ID形式チェック（安全のため）
    if (!preg_match('/^\d{4,}$/', $id)) continue;

    $data = json_decode(@file_get_contents($meta), true);
    if (!$data) continue;

    $image = '';
    foreach (['jpg', 'jpeg', 'png', 'gif', 'webp'] as $ext) {
        $imgPath = "$UPLOAD_DIR/{$id}.{$ext}";
        if (file_exists($imgPath)) {
            $image = "../uploads/{$id}.{$ext}";
            break;
        }
    }

    $items[] = [
        'id' => $id,
        'comment' => $data['comment'] ?? '',
        'key' => $data['key'] ?? '',
        'image' => $image
    ];
}

/* =========================
   ログ読み込み
========================= */
$logs = [];
if (file_exists($LOG_FILE)) {
    $logs = array_reverse(array_filter(file($LOG_FILE, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)));
}
?>
<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="UTF-8">
<title>管理画面</title>
<style>
body { font-family: sans-serif; margin: 20px; }
table { border-collapse: collapse; width: 100%; margin-top: 20px; }
th, td { border: 1px solid #ccc; padding: 8px; text-align: left; vertical-align: top; }
th { background: #f0f0f0; }
img { max-width: 150px; max-height: 150px; border-radius: 6px; object-fit: cover; }
pre { background: #f8f8f8; padding: 15px; border: 1px solid #ddd; max-height: 400px; overflow: auto; white-space: pre-wrap; }
.message { padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; color: #155724; border-radius: 4px; margin: 10px 0; }
</style>
</head>
<body>

<h2>画像アップローダー 管理画面</h2>

<?php if ($message): ?>
<div class="message"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<h3>アップロード一覧（<?= count($items) ?>件）</h3>
<table>
<tr>
    <th>ID</th>
    <th>画像</th>
    <th>コメント</th>
    <th>削除キー</th>
    <th>操作</th>
</tr>
<?php foreach ($items as $item): ?>
<tr>
    <td><?= htmlspecialchars($item['id']) ?></td>
    <td>
        <?php if ($item['image']): ?>
            <img src="<?= htmlspecialchars($item['image']) ?>" alt="image">
        <?php else: ?>
            なし
        <?php endif; ?>
    </td>
    <td><?= nl2br(htmlspecialchars($item['comment'])) ?></td>
    <td><?= htmlspecialchars($item['key']) ?></td>
    <td>
        <form method="post" style="display:inline;" onsubmit="return confirm('本当にID <?= htmlspecialchars($item['id']) ?> を削除しますか？');">
            <input type="hidden" name="delete_id" value="<?= htmlspecialchars($item['id']) ?>">
            <button type="submit" style="background:#dc3545;color:white;border:none;padding:6px 12px;border-radius:4px;cursor:pointer;">削除</button>
        </form>
    </td>
</tr>
<?php endforeach; ?>
<?php if (empty($items)): ?>
<tr><td colspan="5" style="text-align:center;">アップロードされた画像はありません</td></tr>
<?php endif; ?>
</table>

<h3>管理操作</h3>
<form method="post" onsubmit="return confirm('アップロード連番を本当に初期化しますか？（次は0001になります）');">
    <button type="submit" name="reset_counter" style="padding:10px 20px;font-size:16px;">アップロード連番を初期化</button>
</form>

<h3>管理ログ</h3>
<pre><?php
foreach ($logs as $line) {
    echo htmlspecialchars($line) . "\n";
}
?></pre>

<p><a href="?logout=1">ログアウト</a></p>

<?php
if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: index.php');
    exit;
}
?>

</body>
</html>