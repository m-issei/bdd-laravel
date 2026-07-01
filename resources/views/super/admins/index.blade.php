<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>管理者アカウント管理 — スーパー管理者</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-100">

    <nav class="bg-indigo-700 text-white px-6 py-3 flex items-center justify-between">
        <div class="flex items-center gap-6">
            <span class="font-bold text-lg">スーパー管理者</span>
            <a href="{{ route('super.organizations.index') }}" class="text-sm hover:underline opacity-80">組織管理</a>
            <a href="{{ route('super.admins.index') }}" class="text-sm font-semibold underline">管理者アカウント</a>
        </div>
        <form method="POST" action="{{ route('super.logout') }}">
            @csrf
            <button class="text-sm hover:underline">ログアウト</button>
        </form>
    </nav>

    <div class="max-w-5xl mx-auto py-8 px-4">

        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">管理者アカウント管理</h1>
            <button onclick="document.getElementById('create-modal').classList.remove('hidden')"
                class="bg-indigo-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-indigo-700 transition">
                + 新規作成
            </button>
        </div>

        <div class="bg-white rounded-xl shadow overflow-hidden">
            @if ($admins->isEmpty())
                <p class="text-center text-gray-500 py-12 text-sm">管理者がまだいません</p>
            @else
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b">
                        <tr>
                            <th class="text-left px-6 py-3 font-medium text-gray-600">名前</th>
                            <th class="text-left px-6 py-3 font-medium text-gray-600">メール</th>
                            <th class="text-left px-6 py-3 font-medium text-gray-600">組織</th>
                            <th class="text-left px-6 py-3 font-medium text-gray-600">状態</th>
                            <th class="px-6 py-3"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @foreach ($admins as $admin)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 font-medium">{{ $admin->name }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $admin->email }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $admin->organization?->name ?? '—' }}</td>
                                <td class="px-6 py-4">
                                    @if ($admin->is_active)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">有効</span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600">無効</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right space-x-2">
                                    <button onclick="openEditModal({{ $admin->id }}, {{ $admin->organization_id }}, '{{ addslashes($admin->name) }}', '{{ addslashes($admin->email) }}')"
                                        class="text-indigo-600 hover:underline">編集</button>

                                    <form method="POST" action="{{ route('super.admins.toggle-active', $admin) }}" class="inline">
                                        @csrf @method('PATCH')
                                        <input type="hidden" name="is_active" value="{{ $admin->is_active ? '0' : '1' }}">
                                        <button class="{{ $admin->is_active ? 'text-yellow-600' : 'text-green-600' }} hover:underline">
                                            {{ $admin->is_active ? '無効化' : '有効化' }}
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('super.admins.destroy', $admin) }}" class="inline"
                                        onsubmit="return confirm('削除しますか？')">
                                        @csrf @method('DELETE')
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
            <h2 class="text-lg font-bold mb-4">管理者を新規作成</h2>
            <form method="POST" action="{{ route('super.admins.store') }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">組織 <span class="text-red-500">*</span></label>
                    <select name="organization_id" required
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('organization_id') border-red-500 @enderror">
                        <option value="">選択してください</option>
                        @foreach ($organizations as $org)
                            <option value="{{ $org->id }}" {{ old('organization_id') == $org->id ? 'selected' : '' }}>{{ $org->name }}</option>
                        @endforeach
                    </select>
                    @error('organization_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">名前 <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="{{ old('name') }}" required maxlength="100"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('name') border-red-500 @enderror">
                    @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">メールアドレス <span class="text-red-500">*</span></label>
                    <input type="email" name="email" value="{{ old('email') }}" required
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('email') border-red-500 @enderror">
                    @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">パスワード <span class="text-red-500">*</span></label>
                    <input type="password" name="password" required minlength="8"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 @error('password') border-red-500 @enderror">
                    @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
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
            <h2 class="text-lg font-bold mb-4">管理者を編集</h2>
            <form id="edit-form" method="POST">
                @csrf @method('PUT')
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">組織 <span class="text-red-500">*</span></label>
                    <select id="edit-org" name="organization_id" required
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach ($organizations as $org)
                            <option value="{{ $org->id }}">{{ $org->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">名前 <span class="text-red-500">*</span></label>
                    <input id="edit-name" type="text" name="name" required maxlength="100"
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-700 mb-1">メールアドレス <span class="text-red-500">*</span></label>
                    <input id="edit-email" type="email" name="email" required
                        class="w-full border rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
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
        function openEditModal(id, orgId, name, email) {
            document.getElementById('edit-form').action = '/super/admins/' + id;
            document.getElementById('edit-org').value   = orgId;
            document.getElementById('edit-name').value  = name;
            document.getElementById('edit-email').value = email;
            document.getElementById('edit-modal').classList.remove('hidden');
        }

        @if ($errors->any())
            document.getElementById('create-modal').classList.remove('hidden');
        @endif
    </script>
</body>
</html>
