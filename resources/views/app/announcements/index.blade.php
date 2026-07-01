<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>お知らせ一覧</title>
</head>
<body>
<h1>お知らせ一覧</h1>
<ul>
    @foreach ($announcements as $announcement)
        <li><a href="{{ route('app.announcements.show', $announcement->id) }}">{{ $announcement->title }}</a></li>
    @endforeach
</ul>
</body>
</html>
