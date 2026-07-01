<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>アンケート一覧</title>
</head>
<body>
<h1>アンケート一覧</h1>
<ul>
    @foreach ($surveys as $survey)
        <li><a href="{{ route('app.surveys.show', $survey->id) }}">{{ $survey->title }}</a></li>
    @endforeach
</ul>
</body>
</html>
