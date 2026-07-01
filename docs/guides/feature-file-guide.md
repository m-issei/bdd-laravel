# .feature ファイルガイド

`.feature` ファイルは **人間と Claude の両方が読む「仕様の単一ソース」** である。

---

## 目的

| 読み手 | 目的 |
|--------|------|
| 人間（PM・開発者） | コードを読まずに「このシステムが何をするか」を理解する |
| Claude | 実装・テスト作成前に期待する振る舞いを把握し、認識ズレを防ぐ |

---

## ファイルの場所と対応関係

| `.feature` ファイル | PHPUnit テストファイル |
|--------------------|----------------------|
| `features/super/auth.feature` | `tests/Features/BDD/Super/AuthTest.php` |
| `features/super/organization.feature` | `tests/Features/BDD/Super/OrganizationTest.php` |
| `features/super/admin_account.feature` | `tests/Features/BDD/Super/AdminAccountTest.php` |
| `features/admin/auth.feature` | `tests/Features/BDD/Admin/AuthTest.php` |
| `features/admin/participant.feature` | `tests/Features/BDD/Admin/ParticipantTest.php` |
| `features/admin/survey.feature` | `tests/Features/BDD/Admin/SurveyTest.php` |
| `features/admin/dashboard.feature` | `tests/Features/BDD/Admin/DashboardTest.php` |
| `features/admin/announcement.feature` | `tests/Features/BDD/Admin/AnnouncementTest.php` |
| `features/app/auth.feature` | `tests/Features/BDD/App/AuthTest.php` |
| `features/app/survey_response.feature` | `tests/Features/BDD/App/SurveyResponseTest.php` |
| `features/app/announcement.feature` | `tests/Features/BDD/App/AnnouncementTest.php` |

---

## Claude が読むタイミング

- **STEP 4b（PHPUnit テスト設計）の前**: Scenario をテストメソッドに変換する
- **STEP 5（実装）の前**: 実装すべき振る舞いを確認してから実装を始める
- **バグ修正の前**: 仕様として期待される動作を確認する

---

## 書き方フォーマット

```gherkin
Feature: <機能名>
  As a <ロール>
  I want <やりたいこと>
  So that <目的・価値>

  Scenario: <正常系のシナリオ名>
    Given <前提条件>
    When <操作>
    Then <期待する結果>

  Scenario: <異常系のシナリオ名>
    Given <前提条件>
    When <不正な操作>
    Then <エラーになる>
```

新機能追加時のテンプレート:
```bash
cp features/example.feature features/<ロール>/<機能>.feature
```

---

## 書き方のルール

- 正常系・異常系・境界値・状態網羅の全シナリオを Given/When/Then で書く
- シナリオ名は日本語で「〜できる」「〜の場合は〜になる」の形式
- 実装の詳細（クラス名・メソッド名）はシナリオに書かない（振る舞いだけを書く）
