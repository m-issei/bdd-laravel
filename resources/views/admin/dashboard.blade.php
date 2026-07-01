<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ダッシュボード — 管理者</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-100">
    <nav class="bg-blue-700 text-white px-6 py-3 flex items-center justify-between">
        <span class="font-bold text-lg">管理者ダッシュボード</span>
        <form method="POST" action="{{ route('admin.logout') }}">
            @csrf
            <button class="text-sm hover:underline">ログアウト</button>
        </form>
    </nav>
    <div class="max-w-4xl mx-auto py-8 px-4">
        <h1 class="text-2xl font-bold mb-4">ダッシュボード</h1>
        <p class="text-gray-500">管理者機能を実装予定です。</p>
    </div>
</body>
</html>
