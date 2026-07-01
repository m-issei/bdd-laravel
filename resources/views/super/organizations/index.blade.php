<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>組織管理 — スーパー管理者</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-100">

    {{-- ナビゲーション --}}
    <nav class="bg-indigo-700 text-white px-6 py-3 flex items-center justify-between">
        <span class="font-bold text-lg">スーパー管理者</span>
        <form method="POST" action="{{ route('super.logout') }}">
            @csrf
            <button class="text-sm hover:underline">ログアウト</button>
        </form>
    </nav>

    <div class="max-w-4xl mx-auto py-8 px-4">

        {{-- ページタイトル --}}
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">組織管理</h1>
            <button
                onclick="document.getElementById('create-modal').classList.remove('hidden')"
                class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700 transition"
            >
                + 新規作成
            </button>
        </div>

        {{-- フラッシュメッセージ --}}
        @if (session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">
                {{ session('success') }}
            </div>
        @endif

        {{-- 組織一覧 --}}
        <div class="bg-white rounded-xl shadow overflow-hidden">
            @if ($organizations->isEmpty())
                <p class="text-center text-gray-500 py-12 text-sm">組織がまだありません</p>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="text-left px-6 py-3 font-medium text-gray-600">組織名</th>
                            <th class="text-left px-6 py-3 font-medium text-gray-600">説明</th>
                            <th class="text-left px-6 py-3 font-medium text-gray-600">作成日</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach ($organizations as $org)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium">{{ $org->name }}</td>
                                <td class="px-6 py-4 text-gray-500">{{ $org->description ?? '—' }}</td>
                                <td class="px-6 py-4 text-gray-500">{{ $org->created_at->format('Y/m/d') }}</td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <button
                                        onclick="openEditModal({{ $org->id }}, '{{ addslashes($org->name) }}', '{{ addslashes($org->description ?? '') }}')"
                                        class="text-indigo-600 hover:underline"
                                    >
                                        編集
                                    </button>
                                    <form method="POST" action="{{ route('super.organizations.destroy', $org) }}" class="inline"
                                        onsubmit="return confirm('削除しますか？')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-500 hover:underline">削除</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    {{-- 新規作成モーダル --}}
    <div id="create-modal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
            <h2 class="text-lg font-bold mb-4">組織を新規作成</h2>
            <form method="POST" action="{{ route('super.organizations.store') }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">組織名 <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required maxlength="255"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">説明</label>
                    <textarea name="description" rows="3"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description') }}</textarea>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('create-modal').classList.add('hidden')"
                        class="px-4 py-2 text-sm border rounded-lg hover:bg-gray-50">キャンセル</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold">作成</button>
                </div>
            </form>
        </div>
    </div>

    {{-- 編集モーダル --}}
    <div id="edit-modal" class="hidden fixed inset-0 bg-black/40 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl shadow-xl w-full max-w-md p-6">
            <h2 class="text-lg font-bold mb-4">組織を編集</h2>
            <form id="edit-form" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">組織名 <span class="text-red-500">*</span></label>
                    <input id="edit-name" type="text" name="name" required maxlength="255"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">説明</label>
                    <textarea id="edit-description" name="description" rows="3"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"></textarea>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="document.getElementById('edit-modal').classList.add('hidden')"
                        class="px-4 py-2 text-sm border rounded-lg hover:bg-gray-50">キャンセル</button>
                    <button type="submit"
                        class="px-4 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 font-semibold">保存</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(id, name, description) {
            document.getElementById('edit-form').action = '/super/organizations/' + id;
            document.getElementById('edit-name').value = name;
            document.getElementById('edit-description').value = description;
            document.getElementById('edit-modal').classList.remove('hidden');
        }

        @if ($errors->any())
            document.getElementById('create-modal').classList.remove('hidden');
        @endif
    </script>
</body>
</html>
