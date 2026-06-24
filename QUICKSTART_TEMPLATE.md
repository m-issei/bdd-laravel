# BDD テンプレート クイックスタート

## 🚀 5分で始めるテンプレート活用

### ステップ 1: テンプレートをコピー

#### PHP Feature テスト（推奨: API/フロー テスト）

```bash
# ① テンプレートをコピー
cp tests/stubs/FeatureTemplate.php tests/Features/BDD/MyFeatureTest.php

# ② ファイルを開いて編集
code tests/Features/BDD/MyFeatureTest.php
```

**編集ポイント:**
- `ExampleFeatureTest` → `MyFeatureTest` に変更
- Feature コメントを記入
- テストメソッドを追加

#### PHP Unit テスト（推奨: ロジック/メソッド テスト）

```bash
cp tests/stubs/UnitTemplate.php tests/Unit/MyClassTest.php
code tests/Unit/MyClassTest.php
```

#### JavaScript Jest テスト

```bash
cp resources/specs/SpecTemplate.js resources/specs/my-feature.spec.js
code resources/specs/my-feature.spec.js
```

---

### ステップ 2: テストを実行

```bash
# PHP Feature テスト実行
php artisan test tests/Features/BDD/MyFeatureTest.php

# Jest テスト実行
npm test -- my-feature.spec.js

# Watch モード（ファイル保存時に自動実行）
npm run php:test:watch
npm test -- --watch
```

---

### ステップ 3: 緑 → 赤 → 緑の TDD サイクル

```
1️⃣ RED: テストを書いて実行 → 失敗する
2️⃣ GREEN: 実装コードを追加 → テストが成功
3️⃣ REFACTOR: コードを改善 → テストは成功のまま
```

**例:**

```php
// ① RED - テストを書く（まだ実装がない）
public function test_should_calculate_total_price(): void
{
    $item = new CartItem('Apple', 5, 2.00);
    $total = $item->getTotalPrice();
    $this->assertEquals(10.00, $total);
}

// ② Green - 実装追加
class CartItem {
    public function getTotalPrice() {
        return $this->quantity * $this->price;
    }
}

// ③ Refactor - コードを改善
// テストはそのままで、実装を最適化
```

---

## 📚 実例テンプレート

このプロジェクトには、すぐに使える実例が用意されています：

### PHP Feature テスト実例

📄 [tests/Features/BDD/UserRegistrationAuthenticationFeatureTest.php](../tests/Features/BDD/UserRegistrationAuthenticationFeatureTest.php)

**学べること:**
- ✓ 複数シナリオの構成
- ✓ Factory を使ったテストデータ生成
- ✓ API エンドポイントのテスト
- ✓ 認証テスト (`assertAuthenticatedAs`)
- ✓ データベース検証 (`assertDatabaseHas`)

**実行:**
```bash
php artisan test tests/Features/BDD/UserRegistrationAuthenticationFeatureTest.php
```

### JavaScript Jest テスト実例

📄 [resources/specs/form-validation.spec.js](../resources/specs/form-validation.spec.js)

**学べること:**
- ✓ Validator ロジックのテスト
- ✓ 複数条件のテスト
- ✓ 複数エラーの検証
- ✓ beforeEach/afterEach の使用

**実行:**
```bash
npm test -- form-validation.spec.js
```

---

## 🎯 テンプレート選択フローチャート

```
テストを書く
    ↓
何をテストする？
    ↓
    ├─ API エンドポイント
    │  ├─ 複数の処理が連携？ → YES → Feature Template
    │  └─ 単一の処理？ → Unit Template
    │
    ├─ クラスのメソッド/ロジック
    │  └─ → Unit Template
    │
    ├─ JavaScript 関数/コンポーネント
    │  └─ → Jest Template
    │
    └─ ビジネス要件を文書化したい？
       └─ → Gherkin Feature File
```

---

## 💡 よくあるテンプレート活用例

### 例1: ユーザー認証機能をテスト

```bash
# ① テンプレートをコピー
cp tests/stubs/FeatureTemplate.php tests/Features/BDD/AuthenticationFeatureTest.php

# ② Feature コメントを記入
# 「User Authentication」という機能を記入

# ③ 複数のシナリオを追加
# - ユーザーログイン成功
# - ログイン失敗（パスワード間違い）
# - ロジアウト
```

### 例2: バリデーション関数をテスト

```bash
# ① Jest テンプレートをコピー
cp resources/specs/SpecTemplate.js resources/specs/email-validator.spec.js

# ② 複数のテストケースを追加
# - 正しいメール形式
# - 間違ったメール形式
# - 空文字列
```

---

## 🔍 テンプレート内のコメント解説

すべてのテンプレートに以下のコメント構造があります：

```php
/**
 * ════════════════════════════════════════════════════════════════
 * Scenario: [What is being tested]
 * ════════════════════════════════════════════════════════════════
 * Given:  [Initial state/precondition]
 * When:   [Action/input]
 * Then:   [Expected outcome]
 * ════════════════════════════════════════════════════════════════
 */

// ✓ ARRANGE - Setup
// ✓ ACT     - Execute
// ✓ ASSERT  - Verify
```

| コメント部 | 説明 | 例 |
|-----------|-----|-----|
| **Scenario** | テストの意図 | "User can login with valid credentials" |
| **Given** | テスト前提条件 | "A registered user exists" |
| **When** | ユーザーアクション | "The user submits login form" |
| **Then** | 期待される結果 | "User is authenticated" |
| **ARRANGE** | テストデータ準備 | `$user = User::factory()->create();` |
| **ACT** | コード実行 | `$response = $this->post('/login', ...)` |
| **ASSERT** | 結果検証 | `$this->assertAuthenticatedAs($user);` |

---

## ✅ テンプレート使用チェックリスト

新しいテストを書く際は、以下を確認：

```
[ ] テンプレートをコピーした
[ ] クラス名/ファイル名を変更した
[ ] Feature コメントを記入した
[ ] 複数シナリオ（正常系・エラー系）を含めた
[ ] Given/When/Then コメントを書いた
[ ] ARRANGE/ACT/ASSERT の構造がある
[ ] テストメソッド名は snake_case
[ ] テストメソッド名は `test_` で始まる
[ ] コードを実行してテストが成功した
[ ] Git にコミットした
```

---

## 🐛 トラブルシューティング

### テストが実行されない場合

```bash
# ① クラス名が正しいか確認
grep -n "class" tests/Features/BDD/MyTest.php

# ② テストメソッド名が test_ で始まるか確認
grep -n "public function test_" tests/Features/BDD/MyTest.php

# ③ 名前空間が正しいか確認
head -5 tests/Features/BDD/MyTest.php
```

### Jest テストが動作しない場合

```bash
# ① jest.config.cjs が存在するか確認
ls -la jest.config.cjs

# ② Babel 設定を確認
cat .babelrc

# ③ テストファイルが *.spec.js か *.test.js で終わるか確認
ls resources/specs/*.spec.js
```

---

## 📖 関連ドキュメント

- 📘 詳細ガイド: [TEMPLATE_GUIDE.md](./TEMPLATE_GUIDE.md)
- 📙 BDD セットアップ: [BDD_SETUP.md](./BDD_SETUP.md)
- 📗 README: [README.md](./README.md)

---

## 🎓 参考リソース

- [PHPUnit 公式ドキュメント](https://phpunit.de/documentation.html)
- [Jest 公式ドキュメント](https://jestjs.io/docs/getting-started)
- [Gherkin 言語リファレンス](https://cucumber.io/docs/gherkin/)
- [BDD ベストプラクティス](https://cucumber.io/docs/bdd/)

---

## 🚀 次のステップ

1. テンプレートを選択
2. テストコードを書く
3. テスト実行
4. 実装コードを追加
5. Git にコミット
6. 次の機能のテストを書く

**Happy Testing! 🎉**
