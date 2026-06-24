# BDD Laravel + JavaScript Project

Laravel + JavaScript を使用した仕様駆動開発（BDD）プロジェクトです。
PHPUnitと Jestを使用してテストを実装します。

## 環境構成

- **PHP**: 8.2
- **Laravel**: 12.x
- **PHPUnit**: Laravel付属（PHP側テスト）
- **Jest**: 最新版（JavaScript側テスト）
- **Node.js**: 24.11.0

## プロジェクト構造

```
.
├── tests/
│   ├── Unit/              # PHP ユニットテスト
│   ├── Feature/           # PHP 機能テスト
│   └── Features/BDD/      # PHP BDD テスト（Gherkin形式での記述）
├── resources/
│   └── specs/             # JavaScript BDD テスト
├── phpunit.xml            # PHPUnit設定
├── jest.config.js         # Jest設定
└── .babelrc               # Babel設定
```

## セットアップ

### PHP依存関係のインストール

```bash
/usr/local/opt/php@8.2/bin/php /usr/local/bin/composer install
```

### JavaScript依存関係のインストール

```bash
npm install
```

## テスト実行

### PHPテスト実行

```bash
# 全PHPテストを実行
npm run php:test

# PHPテストをwatchモード実行
npm run php:test:watch

# または直接Artisan使用
php artisan test

# 特定のテストのみ実行
php artisan test tests/Features/BDD/UserFeatureTest.php
```

### JavaScriptテスト実行

```bash
# 全Jestテストを実行
npm test

# Jestをwatchモード実行
npm run test:watch

# カバレッジレポート付き実行
npm run test:coverage
```

## BDD テスト作成ガイド

### PHP側（PHPUnit + BDD）

`tests/Features/BDD/` にテストファイルを作成します。

```php
<?php
namespace Tests\Features\BDD;

class UserFeatureTest extends TestCase
{
    /**
     * Scenario: User can register
     * Given: A new user wants to register
     * When: The user submits valid registration data
     * Then: Account is created successfully
     */
    public function test_user_registration(): void
    {
        // Arrange
        // When
        // Then
    }
}
```

### JavaScript側（Jest + BDD）

`resources/specs/` にテストファイルを作成します。

```javascript
/**
 * Feature: User Authentication
 * 
 * Scenario: User can login
 * Given: A registered user exists
 * When: User enters valid credentials
 * Then: User is authenticated
 */

describe('User Authentication', () => {
  test('should authenticate successfully', () => {
    // Arrange
    // Act
    // Assert
  });
});
```

## ベストプラクティス

### テスト命名規則
- `test_feature_scenario_expected_outcome()` (PHP)
- `should_do_something_when_condition()` (JavaScript)

### 構成: AAA パターン
- **Arrange**: テストデータのセットアップ
- **Act**: 実行したいアクション
- **Assert**: 結果の確認

## トラブルシューティング

### PHPコマンドのエイリアス設定

`.zshrc` または `.bash_profile` に以下を追加：

```bash
alias php=/usr/local/opt/php@8.2/bin/php
alias composer=/usr/local/bin/composer
```

### NPM での権限エラー

```bash
npm config set prefix '~/.npm-global'
export PATH=~/.npm-global/bin:$PATH
```

## リソース

- [PHPUnit Documentation](https://phpunit.de/)
- [Jest Documentation](https://jestjs.io/)
- [Laravel Testing](https://laravel.com/docs/testing)
- [BDD Wikipedia](https://en.wikipedia.org/wiki/Behavior-driven_development)
