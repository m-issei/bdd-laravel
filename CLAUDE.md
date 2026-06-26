# CLAUDE.md — 仕様駆動開発 (BDD) ガイド

## プロジェクト概要

Laravel 12 + PHPUnit + Jest による仕様駆動開発（BDD）プロジェクト。
**仕様（.feature / テストコード）を先に書き、実装はあとから行う**のが絶対ルール。

- PHP 8.2 / Laravel 12.x
- PHPUnit 11（PHP側テスト）
- Jest（JavaScript側テスト）
- Gherkin `.feature` ファイル（受け入れ仕様書）

---

## 仕様を受け取ったときの必須フロー

ユーザーから仕様・要件を渡されたとき、**必ずこの順序で進める**。勝手に実装へ飛ばない。

```
STEP 1: 仕様を理解し、不明点を質問する
  └─ /spec-review スキルを使って7つの観点から制約・エッジケースを網羅的にチェックする
  └─ 曖昧な点・決まっていない点を洗い出してユーザーに確認する

STEP 2: 要件を整理してユーザーに提示し、承認を得る
  └─ 「この理解で合っていますか？」と確認する

STEP 3: 設計方針を提案してユーザーに提示し、承認を得る
  └─ 以下の粒度で必ず提示する（詳細は「設計提案の必須粒度」セクション参照）
     - ディレクトリ構造（Models / Controllers / Services / Livewire / Enums）
     - コントローラーの責務と主要メソッド
     - サービス層の責務と主要メソッド
     - Enum・定数の定義
     - バリデーション（FormRequest）の設計
     - 横断的関心事（マルチテナントスコープ等）の担保方針

STEP 4: テストケースを設計してユーザーに提示し、承認を得る
  └─ Gherkin仕様 + PHPUnit/Jestのテスト設計を示す

STEP 5: 実装 → テスト実行（RED → GREEN）
  └─ 承認されたテストケースに基づいて実装する
```

**各STEPで必ずユーザーの承認を得てから次のSTEPに進む。**
承認なしに次のステップへ進まない。

---

## 設計提案の必須粒度（STEP 3）

STEP 3でユーザーに提示する設計方針は、以下の項目を必ず含める。
表面的な「方針」だけでなく、具体的なクラス名・メソッド名レベルまで示すこと。

### 1. ディレクトリ構造
以下のレイヤーを明示する：
- `app/Models/` — 全モデル一覧
- `app/Http/Controllers/` — ロールごとにサブディレクトリを切り、コントローラー一覧
- `app/Http/Middleware/` — 認証・スコープ制御のミドルウェア
- `app/Services/` — ビジネスロジックを担うサービスクラス一覧
- `app/Livewire/` — Livewireコンポーネント一覧
- `app/Enums/` — Enum・定数クラス一覧
- `app/Http/Requests/` — FormRequestクラス一覧

### 2. コントローラーの責務
- コントローラーはリクエスト受取とレスポンス返却のみ、ロジックはServiceへ委譲
- 主要なアクション（index / store / update / destroy 等）を列挙
- 具体的なコード例を1つ示す

### 3. サービス層の責務
- 各Serviceクラスが持つメソッドとシグネチャを列挙
- ビジネスルール（公開条件・状態遷移ガード等）がどのServiceに属するかを明示

### 4. Enum・定数
- 全Enumの名前・ケース・値を列挙
- 将来拡張が想定される箇所はコメントで明示

### 5. バリデーション設計
- FormRequestクラス名と対応するアクションを列挙
- 主要なルール（文字数・一意性・条件付き必須等）を示す

### 6. 横断的関心事
- マルチテナントスコープ・権限チェックの実現方法（Trait・Scope等）
- 論理削除の統一方針（SoftDeletesの使用）
- 認証ガードの設定方針

---

## 開発の絶対ルール：仕様ファースト

実装コードを書く前に、必ず以下の順序で進める。

```
1. 仕様を .feature ファイルまたはテストコードで記述する
2. テストを実行して RED（失敗）を確認する
3. テストが通る最小限の実装を書く
4. テストが GREEN になることを確認する
5. リファクタリングしてテストが GREEN のままか確認する
```

**「テストを書いていないコードは存在しないのと同じ」という前提で作業する。**

---

## ディレクトリ構造

```
.
├── features/                    # Gherkin 仕様書（受け入れテスト）
│   └── *.feature
├── tests/
│   ├── Feature/                 # Laravel 統合テスト
│   ├── Features/BDD/            # BDD スタイルの機能テスト
│   ├── Unit/                    # ユニットテスト
│   └── stubs/                   # テンプレートファイル
│       ├── FeatureTemplate.php
│       └── UnitTemplate.php
└── resources/specs/             # JavaScript Jest テスト
    ├── SpecTemplate.js
    ├── auth.spec.js
    └── form-validation.spec.js
```

---

## テスト実行コマンド

```bash
# PHP 全テスト
php artisan test

# PHP 特定ファイル
php artisan test tests/Features/BDD/UserFeatureTest.php

# PHP フィルタ（メソッド名）
php artisan test --filter="ユーザー登録"

# JavaScript 全テスト
npm test

# JavaScript 特定ファイル
npm test -- auth.spec.js

# JavaScript ウォッチモード
npm test -- --watch

# PHP ウォッチモード
npm run php:test:watch
```

---

## 新機能の実装手順

### ステップ 1: Gherkin 仕様を書く

`features/` に `.feature` ファイルを作成する。テンプレートは `features/example.feature` を参照。

```gherkin
Feature: ユーザー登録
  As a 新規ユーザー
  I want メールとパスワードで登録したい
  So that サービスを利用できる

  Scenario: 正常な登録
    Given 有効なメールアドレスとパスワードが入力されている
    When 登録ボタンを押す
    Then アカウントが作成される
    And ダッシュボードにリダイレクトされる

  Scenario: 重複メールでの登録失敗
    Given 既存ユーザーと同じメールアドレスが入力されている
    When 登録ボタンを押す
    Then エラーメッセージが表示される
```

### ステップ 2: PHP テストを書く（RED）

`tests/Features/BDD/` にテストファイルを作成。テンプレート: `tests/stubs/FeatureTemplate.php`

```php
/**
 * @test
 * Scenario: 正常な登録
 */
public function ユーザーが有効な情報で登録できる(): void
{
    // Given
    $data = ['email' => 'test@example.com', 'password' => 'secret123'];

    // When
    $response = $this->postJson('/api/register', $data);

    // Then
    $response->assertStatus(201);
    $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
}
```

テストを実行して **失敗（RED）** を確認する：
```bash
php artisan test tests/Features/BDD/UserRegistrationTest.php
```

### ステップ 3: 最小実装を書く（GREEN）

テストが通る最小限のコードだけを書く。過剰な実装をしない。

### ステップ 4: GREEN を確認してリファクタリング

```bash
php artisan test tests/Features/BDD/UserRegistrationTest.php
```

---

## テストの書き方規約

### PHP テスト

- メソッド名は **日本語** で書く（仕様として読めること）
- `Given / When / Then` をコメントで明示する
- `@test` アノテーションを使う
- テストは1つの振る舞いだけを検証する

```php
/** @test */
public function 未認証ユーザーは保護されたページにアクセスできない(): void
{
    // When
    $response = $this->get('/dashboard');

    // Then
    $response->assertRedirect('/login');
}
```

### JavaScript テスト

- `describe` ブロックで機能をグループ化する
- `it` / `test` の説明は **「〜できる」「〜が表示される」** の形式で書く
- AAA（Arrange / Act / Assert）パターンに従う

```javascript
describe('ログインフォーム', () => {
  it('空のフォームを送信するとエラーが表示される', () => {
    // Arrange
    render(<LoginForm />);
    // Act
    fireEvent.click(screen.getByRole('button', { name: '送信' }));
    // Assert
    expect(screen.getByText('必須項目です')).toBeInTheDocument();
  });
});
```

---

## Claude への作業依頼パターン

### 新機能を追加するとき

```
「〇〇機能を追加して」ではなく：

「以下の仕様でテストを書いてから実装して：
- ユーザーが〇〇できる
- 〇〇の場合はエラーになる
- 〇〇の場合は〇〇が表示される」
```

### バグを修正するとき

```
「バグを直して」ではなく：

「このバグを再現するテストを先に書き、
テストがREDになることを確認してから修正して」
```

### Claude が守るべきこと

1. **テストなしの実装コードを書かない**
2. **テストを実行してREDを確認してから実装に進む**
3. **実装後にテストを実行してGREENを確認する**
4. **テストが通らない状態でリポジトリを触らない**
5. **過剰な実装・先読み設計をしない**（テストが要求する最小限のみ）

---

## よくある間違いと対処法

| 間違い | 正しいアプローチ |
|--------|-----------------|
| 実装してからテストを書く | テストを先に書いてREDを確認する |
| 複数の機能を一度に実装する | 1シナリオずつREDGREENを繰り返す |
| テストを削除してテストを通す | テストが要求する動作を実装する |
| モックを多用して実装を隠す | できる限り実際の動作でテストする |
| エラーを無視してテストをスキップする | エラーの原因を特定して修正する |

---

## テンプレートの使い方

```bash
# PHP Feature テスト
cp tests/stubs/FeatureTemplate.php tests/Features/BDD/MyFeatureTest.php

# PHP Unit テスト
cp tests/stubs/UnitTemplate.php tests/Unit/MyClassTest.php

# JavaScript テスト
cp resources/specs/SpecTemplate.js resources/specs/my-feature.spec.js

# Gherkin 仕様書
cp features/example.feature features/my-feature.feature
```

---

## 参考ドキュメント

- [BDD_SETUP.md](BDD_SETUP.md) — 環境構築の詳細
- [QUICKSTART_TEMPLATE.md](QUICKSTART_TEMPLATE.md) — テンプレートの使い方
- [TEMPLATE_GUIDE.md](TEMPLATE_GUIDE.md) — 各テンプレートの詳細説明
- [features/example.feature](features/example.feature) — Gherkin の書き方サンプル
