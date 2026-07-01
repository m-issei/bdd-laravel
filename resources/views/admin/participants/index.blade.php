<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>参加者管理</title>
</head>
<body>
<h1>参加者一覧</h1>
<table>
    <thead>
        <tr>
            <th>名前</th>
            <th>メールアドレス</th>
            <th>ステータス</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($participants as $participant)
        <tr>
            <td>{{ $participant->name }}</td>
            <td>{{ $participant->email }}</td>
            <td>
                @if ($participant->trashed())
                    削除済み
                @elseif (!$participant->is_active)
                    無効
                @else
                    有効
                @endif
            </td>
        </tr>
        @endforeach
    </tbody>
</table>
</body>
</html>
