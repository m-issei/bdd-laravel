# BDD テンプレート使用ガイド

## 📋 目次

1. [テンプレート概要](#テンプレート概要)
2. [PHP Feature テンプレート](#php-feature-テンプレート)
3. [PHP Unit テンプレート](#php-unit-テンプレート)
4. [JavaScript Jest テンプレート](#javascript-jest-テンプレート)
5. [Gherkin Feature ファイル](#gherkin-feature-ファイル)
6. [ベストプラクティス](#ベストプラクティス)
7. [テンプレート選択ガイド](#テンプレート選択ガイド)

---

## テンプレート概要

このプロジェクトでは、仕様駆動開発（BDD）を最大限に活かすための3つのテンプレートを用意しています：

| テンプレート | 用途 | ファイル形式 | テストフレームワーク |
|---|---|---|---|
| **Feature Template** | Laravel エンドポイント・複合機能テスト | PHP | PHPUnit |
| **Unit Template** | 単一クラス・メソッドのロジックテスト | PHP | PHPUnit |
| **Jest Template** | JavaScript コンポーネント・ロジックテスト | JavaScript | Jest |
| **Gherkin Feature** | 受け入れテスト仕様書・利害関係者との共有 | `.feature` | Behat/Cucumber |

---

## PHP Feature テンプレート

### 📁 ファイルパス
```
tests/stubs/FeatureTemplate.php
```

### 🎯 用途
- API エンドポイントのテスト
- ユーザーのユースケース・フロー全体をテスト
- データベース操作が伴うテスト
- 複数の処理が連携したシナリオテスト

### 📝 使用方法

#### 1. テンプレートをコピーして新しいテストを作成

```bash
# Feature テストディレクトリに新しいテストを作成
cp tests/stubs/FeatureTemplate.php tests/Features/BDD/UserAuthenticationFeatureTest.php
```

#### 2. クラス名と名前空間を修正

```php
namespace Tests\Features\BDD;  // そのまま

class UserAuthenticationFeatureTest extends TestCase  // ファイル名に合わせる
{
```

#### 3. 機能説明を記入

```php
/**
 * ====================================================================
 * Feature: User Authentication Management
 * ====================================================================
 * 
 * As a user
 * I want to authenticate with email and password
 * So that I can access my account securely
 * 
 * ====================================================================
 */
```

#### 4. シナリオごとにテストメソッドを追加

```php
public function test_user_can_login_with_valid_credentials(): void
{
    // ARRANGE
    $user = User::factory()->create([
        'email' => 'test@example.com',
        'password' => Hash::make('password123'),
    ]);

    // ACT
    $response = $this->post('/login', [
        'email' => 'test@example.com',
        'password' => 'password123',
    ]);

    // ASSERT
    $response->assertRedirect('/dashboard');
    $this->assertAuthenticatedAs($user);
}
```

### ✅ チェックリスト

- [ ] テストクラス名は `*FeatureTest` で終わる
- [ ] テストメソッド名は `test_` で始まる（snake_case）
- [ ] Given/When/Then コメントがある
- [ ] ARRANGE/ACT/ASSERT の3段階が明確
- [ ] 複数のシナリオ（正常系・異常系・エッジケース）がある
- [ ] テストデータはFactory を使用

---

## PHP Unit テンプレート

### 📁 ファイルパス
```
tests/stubs/UnitTemplate.php
```

### 🎯 用途
- 単一クラスのビジネスロジックテスト
- ユーティリティ関数のテスト
- 外部依存なしのピュアロジックテスト
- エラーハンドリングのテスト

### 📝 使用方法

#### 1. テンプレートをコピーして新しいテストを作成

```bash
cp tests/stubs/UnitTemplate.php tests/Unit/Services/PaymentServiceTest.php
```

#### 2. テストするクラスを指定

```php
namespace Tests\Unit\Services;

use App\Services\PaymentService;
use PHPUnit\Framework\TestCase;

class PaymentServiceTest extends TestCase
{
```

#### 3. 単一責任でテストメソッドを追加

```php
public function test_should_calculate_tax_correctly(): void
{
    // ARRANGE
    $service = new PaymentService();
    $amount = 100;
    $taxRate = 0.10;
    $expected = 110;

    // ACT
    $result = $service->addTax($amount, $taxRate);

    // ASSERT
    $this->assertEquals($expected, $result);
}

public function test_should_throw_exception_on_negative_amount(): void
{
    $this->expectException(\InvalidArgumentException::class);
    
    $service = new PaymentService();
    $service->addTax(-100, 0.10);
}
```

### ✅ チェックリスト

- [ ] テストクラス名は `*Test` で終わる
- [ ] 1つのテストメソッドは1つの振る舞いをテスト
- [ ] 外部依存（DB、API）はない
- [ ] Mock/Stub の使用は最小限
- [ ] テスト実行時間が速い（< 1秒）

---

## JavaScript Jest テンプレート

### 📁 ファイルパス
```
resources/specs/SpecTemplate.js
```

### 🎯 用途
- React/Vue コンポーネントテスト
- JavaScript ユーティリティ関数テスト
- API クライアントのテスト
- 状態管理（State）のテスト

### 📝 使用方法

#### 1. テンプレートをコピーして新しいテストを作成

```bash
cp resources/specs/SpecTemplate.js resources/specs/components/Button.spec.js
```

#### 2. テスト対象の機能を記述

```javascript
/**
 * ====================================================================
 * Feature: Button Component
 * ====================================================================
 * 
 * As a UI developer
 * I want a reusable Button component
 * So that I can maintain consistent UI across the app
 * 
 * ====================================================================
 */

import { render, screen } from '@testing-library/react';
import Button from '@/components/Button';

describe('Feature: Button Component', () => {
```

#### 3. シナリオ別に describe と test を階層化

```javascript
describe('Scenario: Button Rendering', () => {
  test('should render button with label', () => {
    // ARRANGE
    const label = 'Click Me';

    // ACT
    render(<Button label={label} />);
    const button = screen.getByRole('button');

    // ASSERT
    expect(button).toHaveTextContent(label);
  });

  test('should apply custom className', () => {
    // ARRANGE
    const customClass = 'primary';

    // ACT
    render(<Button className={customClass} />);
    const button = screen.getByRole('button');

    // ASSERT
    expect(button).toHaveClass(customClass);
  });
});
```

### ✅ チェックリスト

- [ ] テストファイル名は `*.spec.js` で終わる
- [ ] Feature ブロックでユースケースを説明
- [ ] Scenario ブロックで関連テストをグループ化
- [ ] 各テストの主張は単一
- [ ] beforeEach/afterEach で適切にセットアップ/クリーンアップ
- [ ] Mock や Spy は必要最小限に

---

## Gherkin Feature ファイル

### 📁 ファイルパス
```
features/example.feature
```

### 🎯 用途
- ビジネス要件を非技術者にも理解できる形式で記述
- ステークホルダーとの仕様議論に使用
- 受け入れテストの基仕様書
- チーム全体で共有する仕様ドキュメント

### 📝 使用方法

#### 1. テンプレートをコピー

```bash
cp features/example.feature features/user_authentication.feature
```

#### 2. Feature ヘッダーを記入

```gherkin
Feature: User Authentication
  Manage user login and logout operations

  As a user
  I want to log in with email and password
  So that I can access my personal account
```

#### 3. Background（共通セットアップ）を定義

```gherkin
Background:
  Given the system is running
  And the database is initialized
  And test users exist
```

#### 4. Scenario を記述（非技術者も理解できる表現で）

```gherkin
Scenario: Valid credentials grant access
  Given I am on the login page
  When I enter "user@example.com" as email
  And I enter "correct-password" as password
  And I click the login button
  Then I should be logged in
  And I should see the dashboard
```

#### 5. 複数データセットをテストする場合は Scenario Outline を使用

```gherkin
Scenario Outline: Login with various credentials
  Given I am on the login page
  When I enter "<email>" as email
  And I enter "<password>" as password
  And I click the login button
  Then I should see "<result>"

  Examples:
    | email           | password | result          |
    | valid@test.com  | correct  | dashboard       |
    | valid@test.com  | wrong    | error message   |
    | invalid@        | password | validation error|
```

### 📝 Gherkin キーワード

| キーワード | 説明 |
|-----------|-----|
| **Feature** | 機能全体の説明 |
| **Background** | すべてのシナリオで共通の前提条件 |
| **Scenario** | 1つの具体的なユースケース |
| **Scenario Outline** | テンプレート化されたシナリオ（複数データ） |
| **Given** | 前提条件（システム状態の初期設定） |
| **When** | ユーザーアクション（実行される動作） |
| **Then** | 期待される結果（検証ポイント） |
| **And** | 上記の複数条件を接続 |
| **But** | 否定的な条件 |

### ✅ チェックリスト

- [ ] Feature は独立した機能単位
- [ ] Background で共通セットアップを定義
- [ ] Scenario は ビジネス観点で理解できる表現
- [ ] Given/When/Then のフォーマットに従う
- [ ] サンプルデータは明確で現実的
- [ ] 複数のシナリオで happy path, sad path, edge case を網羅

---

## ベストプラクティス

### 📌 AAA パターン（Arrange/Act/Assert）

すべてのテストは3段階に分ける：

```
1. ARRANGE  - テストデータと状態を準備
2. ACT      - テスト対象のコードを実行
3. ASSERT   - 結果を検証
```

### 📌 テストメソッド名の命名規則

**推奨:**
```php
// "should_" + "期待される動き" + "when_" + "条件"
test_should_return_user_list_when_admin_logged_in()
test_should_throw_exception_when_invalid_input_provided()
test_should_update_only_specified_fields_when_partial_data_sent()
```

**避けるべき:**
```php
testUser()        // 何をテストしているか不明確
test_1()          // ビジネス観点が不明
test_login_ok()   // 期待動作が不明確
```

### 📌 Given/When/Then コメント

すべてのテストメソッドにコメント追加：

```php
/**
 * Scenario: User receives confirmation email
 * Given:  A user submits registration form
 * When:   All validation passes
 * Then:   A confirmation email is sent
 */
public function test_user_receives_confirmation_email(): void
```

### 📌 テストデータ管理

- **Factory** を使って テストデータ生成
- **Fixture** ではなく **Factory** を推奨

```php
// ✅ Good - Factory を使用
$user = User::factory()->create(['role' => 'admin']);

// ❌ Avoid - ハードコードされたデータ
$user = new User();
$user->name = 'Test User';
$user->save();
```

### 📌 複数シナリオのカバレッジ

各機能テストで以下を含める：

```
✓ Happy Path      - 正常系・期待通りの流れ
✓ Sad Path        - エラーケース・異常系
✓ Edge Cases      - 境界値・特殊な状態
```

### 📌 テスト実行時間

- **Unit テスト**: < 1秒
- **Feature テスト**: < 5秒
- 遅い場合はデータベーストランザクションやモック化を検討

---

## テンプレート選択ガイド

### どのテンプレートを使うべき？

```
┌─ API エンドポイント、複数の相互作用がある？
│  ├─ YES → PHP Feature Template (tests/Features/BDD/*)
│  └─ NO  ↓
│
├─ 単一クラス、外部依存なし？
│  ├─ YES → PHP Unit Template (tests/Unit/*)
│  └─ NO  ↓
│
├─ JavaScript ファイル、コンポーネント？
│  ├─ YES → Jest Template (resources/specs/*)
│  └─ NO  ↓
│
└─ ビジネス要件を非技術者と共有したい？
   ├─ YES → Gherkin Feature File (features/*.feature)
   └─ NO  → 上記を選択
```

### テンプレート実例マトリクス

| テスト対象 | テンプレート | 例 |
|-----------|-------------|-----|
| `/api/users` POST エンドポイント | Feature | UserRegistrationFeatureTest.php |
| `UserRepository::findById()` | Unit | UserRepositoryTest.php |
| `ValidationService::validateEmail()` | Unit | ValidationServiceTest.php |
| React Button コンポーネント | Jest | Button.spec.js |
| Vue フォームバリデーション | Jest | FormValidator.spec.js |
| ユーザー認証フロー | Gherkin | user_authentication.feature |
| 支払い処理フロー | Gherkin | payment_processing.feature |

---

## テンプレート実行方法

### PHP テストの実行

```bash
# 全テスト実行
npm run php:test

# 特定のファイルのみ
php artisan test tests/Features/BDD/UserFeatureTest.php

# 特定のメソッドのみ
php artisan test tests/Features/BDD/UserFeatureTest.php --filter=test_user_can_register

# Watch モード（ファイル変更時に自動実行）
npm run php:test:watch
```

### Jest テストの実行

```bash
# 全テスト実行
npm test

# 特定のファイルのみ
npm test -- auth.spec.js

# Watch モード
npm test -- --watch

# カバレッジレポート
npm run test:coverage
```

### Gherkin テストの実行（Behat インストール後）

```bash
# Behat をインストール（まだの場合）
composer require --dev behat/behat

# テスト実行
vendor/bin/behat features/
```

---

## 次のステップ

1. **テンプレートを選択** してテストファイルを作成
2. **ビジネス要件を記述** （Given/When/Then コメント）
3. **テストコードを実装**
4. **テストを実行** して緑色を確認
5. **実装コードを追加** （TDD スタイル）
6. **コミット** して GitHub にプッシュ

---

## リソース

- [PHPUnit ドキュメント](https://phpunit.de/)
- [Jest ドキュメント](https://jestjs.io/)
- [Behat ドキュメント](https://behat.org/)
- [Gherkin 仕様](https://cucumber.io/docs/gherkin/)
- BDD ガイド: `BDD_SETUP.md`
