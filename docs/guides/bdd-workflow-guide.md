# BDD ワークフローガイド

---

## 新機能の実装手順（RED → GREEN サイクル）

### 1. .feature ファイルを書く

`features/<ロール>/<機能>.feature` に Gherkin 形式で受け入れ仕様を記述する。

```gherkin
Feature: アンケート管理
  As a 管理者
  I want アンケートを作成・公開したい
  So that 参加者が回答できる環境を整えられる

  Scenario: 質問が1問以上あるアンケートを公開できる
    Given 管理者でログインしている
    And 1問以上の質問がある下書きアンケートが存在する
    When アンケートを公開する
    Then ステータスがpublishedになる

  Scenario: 質問が0件のアンケートは公開できない
    Given 管理者でログインしている
    And 質問が0件の下書きアンケートが存在する
    When アンケートを公開しようとする
    Then エラーが返る
```

### 2. PHPUnit テストを書く（RED）

`tests/Features/BDD/` にテストファイルを作成。テンプレート: `tests/stubs/FeatureTemplate.php`

```php
/** @test */
public function 質問が1問以上あるアンケートを公開できる(): void
{
    // Given
    $survey = Survey::factory()->withQuestions(1)->create(['status' => SurveyStatus::Draft]);

    // When
    $response = $this->postJson("/admin/surveys/{$survey->id}/publish");

    // Then
    $response->assertOk();
    $this->assertEquals(SurveyStatus::Published, $survey->fresh()->status);
}
```

テストを実行して **RED（失敗）** を確認する：
```bash
php artisan test tests/Features/BDD/Admin/SurveyTest.php
```

### 3. 最小実装を書く（GREEN）

テストが通る最小限のコードだけを書く。過剰な実装をしない。

### 4. GREEN を確認してリファクタリング

```bash
php artisan test tests/Features/BDD/Admin/SurveyTest.php
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
テストが RED になることを確認してから修正して」
```

---

## よくある間違いと対処法

| 間違い | 正しいアプローチ |
|--------|-----------------|
| 実装してからテストを書く | テストを先に書いて RED を確認する |
| 複数の機能を一度に実装する | 1シナリオずつ RED→GREEN を繰り返す |
| テストを削除してテストを通す | テストが要求する動作を実装する |
| モックを多用して実装を隠す | できる限り実際の動作でテストする |
| エラーを無視してテストをスキップする | エラーの原因を特定して修正する |
