<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="robots" content="noindex, nofollow">
  <meta name="googlebot" content="noindex, nofollow">
  <title>画像ダウンロード | TouLove Wiki Image Uploader</title>

  <style>
    body {
      font-family: sans-serif;
      background-color: #f4f4f4;
      margin: 0;
      padding: 0;
    }

    .container {
      max-width: 700px;
      margin: 80px auto;
      background: #fff;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    }

    h1 {
      font-size: 1.3em;
      margin-bottom: 20px;
      border-left: 8px solid #ffb6c1;
      padding-left: 10px;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 25px;
    }

    th, td {
      border: 1px solid #ddd;
      padding: 10px;
      font-size: 0.95em;
    }

    th {
      background: #f8f8f8;
      width: 30%;
      text-align: left;
    }

    .notice {
      background: #fff5f5;
      border: 1px solid #ffb6c1;
      padding: 15px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 0.9em;
      line-height: 1.6;
    }

    .download-box {
      text-align: center;
      margin-top: 30px;
    }

    .download-box button {
      background: #28a745;
      color: #fff;
      font-size: 1.1em;
      padding: 15px 25px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
    }

    .download-box button:hover {
      background: #218838;
    }

    .back-link {
      margin-top: 25px;
      text-align: center;
    }

    .back-link a {
      color: #007bff;
      text-decoration: none;
      font-weight: bold;
    }

    .back-link a:hover {
      text-decoration: underline;
    }
  </style>
</head>

<body>

<div class="container">
  <h1>画像ダウンロード</h1>

  <div class="notice">
    このページは、サーバー負荷軽減のための<strong>ワンクッションページ</strong>です。<br>
    下の「ダウンロード」ボタンを押すと、画像が表示されます。
  </div>

  <table>
    <tr>
      <th>ファイル名</th>
      <td><!-- <?= htmlspecialchars($filename) ?> -->example.png</td>
    </tr>
    <tr>
      <th>コメント</th>
      <td><!-- <?= htmlspecialchars($comment) ?> -->コメント例</td>
    </tr>
    <tr>
      <th>投稿日</th>
      <td><!-- <?= htmlspecialchars($created_at) ?> -->2026-01-01 12:34</td>
    </tr>
  </table>

  <div class="download-box">
    <form method="post">
      <!-- CSRFトークン等を後で追加 -->
      <button type="submit" name="download">Download</button>
    </form>
  </div>

  <div class="back-link">
    <a href="/">アップローダーへ戻る</a>
  </div>
</div>

</body>
</html>
