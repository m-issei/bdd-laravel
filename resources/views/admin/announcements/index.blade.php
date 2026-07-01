<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>お知らせ管理</title>
</head>
<body>
<h1>お知らせ一覧</h1>
<ul>
    @foreach ($announcements as $announcement)
        <li>{{ $announcement->title }} ({{ $announcement->status->value }})</li>
    @endforeach
</ul>
</body>
</html>
