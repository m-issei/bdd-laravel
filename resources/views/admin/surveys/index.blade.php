<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>アンケート管理</title>
</head>
<body>
<h1>アンケート一覧</h1>
<ul>
    @foreach ($surveys as $survey)
        <li>{{ $survey->title }} ({{ $survey->status->value }})</li>
    @endforeach
</ul>
</body>
</html>
