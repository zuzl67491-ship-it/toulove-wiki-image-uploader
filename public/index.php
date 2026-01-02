<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta name="robots" content="noindex, nofollow">
  <title>とうらぶWiki 画像アップローダー</title>

  <style>
    body {
      font-family: sans-serif;
      background: #f4f4f4;
      margin: 0;
      padding: 0;
    }

    header {
      background: #ffffff;
      padding: 15px 20px;
      border-bottom: 1px solid #ccc;
    }

    header h1 {
      margin: 0;
      font-size: 1.2em;
    }

    .container {
      max-width: 1000px;
      margin: 20px auto 120px;
      padding: 0 15px;
    }

    .notice {
      background: #fff3cd;
      border-left: 6px solid #ffc107;
      padding: 15px;
      margin-bottom: 20px;
      font-size: 0.9em;
      line-height: 1.6;
    }

    form {
      background: #ffffff;
      padding: 20px;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      margin-bottom: 30px;
    }

    label {
      display: block;
      margin-top: 15px;
      font-weight: bold;
    }

    input[type="file"],
    input[type="text"],
    textarea {
      width: 100%;
      margin-top: 5px;
      padding: 8px;
      box-sizing: border-box;
    }

    textarea {
      resize: vertical;
    }

    button {
      margin-top: 20px;
      padding: 10px;
      width: 100%;
      background: #28a745;
      color: #fff;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      font-size: 1em;
    }

    button:hover {
      background: #218838;
    }

    .search-box {
      margin-bottom: 15px;
    }

    .image-list {
      background: #ffffff;
      padding: 15px;
      border-radius: 8px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    }

    .image-item {
      display: inline-block;
      width: 180px;
      margin: 10px;
      vertical-align: top;
      text-align: center;
      font-size: 0.85em;
    }

    .image-item img {
      width: 100%;
      border-radius: 6px;
    }

    footer {
      position: fixed;
      bottom: 0;
      width: 100%;
      background: #f8f9fa;
      border-top: 1px solid #ccc;
      padding: 10px;
      text-align: center;
      font-size: 0.9em;
    }
  </style>
</head>

<body>

<header>
  <h1>とうらぶWiki 画像アップローダー</h1>
</header>

<div class="container">

  <!-- 注意文（超短縮版） -->
  <div class="notice">
    ・とうらぶWiki専用アップローダーです<br>
    ・削除キー（数字4桁）は必須です（忘れないでください）<br>
    ・生成されたURLはそのままWikiに貼り付けてください
  </div>

  <!-- アップロードフォーム -->
  <form method="POST" action="upload.php" enctype="multipart/form-data">
    <h2>画像アップロード</h2>

    <label>画像ファイル（jpg / png）</label>
    <input type="file" name="image" accept="image/jpeg,image/png" required>

    <label>コメント（35文字まで・任意）</label>
    <textarea name="comment" maxlength="35" placeholder="コメントを入力（任意）"></textarea>

    <label>削除キー（数字4桁）</label>
    <input type="text" name="delete_key" pattern="\d{4}" maxlength="4" placeholder="例：1234" required>

    <button type="submit">アップロード</button>
  </form>

  <!-- 検索 -->
  <div class="search-box">
    <input type="text" id="searchInput" placeholder="画像名・コメントで検索">
  </div>

  <!-- 画像一覧 -->
  <div class="image-list" id="imageList">
    <!-- PHPで image-item をここに出力 -->
    <!--
    <div class="image-item" data-name="" data-comment="">
      <a href="download.php?id=1">
        <img src="image.php?id=1" loading="lazy">
      </a>
      <div>コメント</div>
    </div>
    -->
  </div>

</div>

<footer>
  <a href="https://wikiwiki.jp/toulove/" target="_blank">とうらぶWikiへ戻る</a>
</footer>

<script>
  // 検索フィルタ
  document.getElementById('searchInput').addEventListener('keyup', function () {
    const keyword = this.value.toLowerCase();
    document.querySelectorAll('.image-item').forEach(item => {
      const name = item.dataset.name || '';
      const comment = item.dataset.comment || '';
      item.style.display =
        name.toLowerCase().includes(keyword) ||
        comment.toLowerCase().includes(keyword)
          ? 'inline-block'
          : 'none';
    });
  });
</script>

</body>
</html>
