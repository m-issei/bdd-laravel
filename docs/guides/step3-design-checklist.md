# STEP 3 設計提案チェックリスト

STEP 3 でユーザーに提示する設計方針は、以下の項目を**全て**含める。
表面的な「方針」だけでなく、具体的なクラス名・メソッド名レベルまで示すこと。

---

## 1. ディレクトリ構造

以下のレイヤーを明示する：

- `app/Models/` — 全モデル一覧
- `app/Http/Controllers/` — ロールごとにサブディレクトリを切り、コントローラー一覧
- `app/Http/Middleware/` — 認証・スコープ制御のミドルウェア
- `app/Services/` — ビジネスロジックを担うサービスクラス一覧
- `app/Livewire/` — Livewireコンポーネント一覧
- `app/Enums/` — Enum・定数クラス一覧
- `app/Http/Requests/` — FormRequestクラス一覧

## 2. コントローラーの責務

- コントローラーはリクエスト受取とレスポンス返却のみ、ロジックはServiceへ委譲
- 主要なアクション（index / store / update / destroy 等）を列挙
- 具体的なコード例を1つ示す

```php
public function store(StoreSurveyRequest $request): RedirectResponse
{
    $this->surveyService->create(auth()->user()->organization, $request->validated());
    return redirect()->route('admin.surveys.index');
}
```

## 3. サービス層の責務

- 各Serviceクラスが持つメソッドとシグネチャを列挙
- ビジネスルール（公開条件・状態遷移ガード等）がどのServiceに属するかを明示

## 4. Enum・定数

- 全Enumの名前・ケース・値を列挙
- 将来拡張が想定される箇所はコメントで明示

## 5. バリデーション設計

- FormRequestクラス名と対応するアクションを列挙
- 主要なルール（文字数・一意性・条件付き必須等）を示す
- 同一組織スコープの一意制約など特殊なルールは必ずコード例を示す

```php
Rule::unique('participants', 'email')->where('organization_id', $this->user()->organization_id)
```

## 6. 横断的関心事

- マルチテナントスコープ・権限チェックの実現方法（Trait・Scope等）
- 論理削除の統一方針（SoftDeletesの使用）
- 認証ガードの設定方針
