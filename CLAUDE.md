# CLAUDE.md — 仕様駆動開発 (BDD) ガイド

## プロジェクト概要

Laravel 12 + PHPUnit + Jest による仕様駆動開発（BDD）プロジェクト。
**仕様（.feature / テストコード）を先に書き、実装はあとから行う**のが絶対ルール。

- PHP 8.2 / Laravel 12.x / PHPUnit 11 / Jest / Gherkin `.feature`

---

## 必須フロー

ユーザーから仕様・要件を渡されたとき、**必ずこの順序で進める**。勝手に実装へ飛ばない。

```
STEP 0（自動）: app/Models/ にファイルあり → 既存プロジェクト（STEP 1 で影響範囲調査を実施）

STEP 1: 仕様理解・不明点の質問
  └─ /spec-review スキルで制約・エッジケースを網羅的にチェック
  └─ [既存] docs/guides/impact-report-guide.md を読み、関連ソースを調査して影響範囲をまとめる

STEP 2: 要件整理（＋影響範囲レポート）→ ユーザーに提示・承認
  └─ [既存] 承認後 docs/impact/<機能名>.md に保存

STEP 3: 設計方針を提案 → ユーザーに提示・承認
  └─ docs/guides/step3-design-checklist.md を必ず読んでから設計する

STEP 4a: .feature ファイルを書く → ユーザーに提示・承認
  └─ features/<ロール>/<機能>.feature に Gherkin で受け入れ仕様を記述
  └─ docs/guides/feature-file-guide.md を必ず読んでから書く

STEP 4b: PHPUnit テストケースを設計 → ユーザーに提示・承認
  └─ docs/guides/step4-testcase-checklist.md を必ず読んでから設計する

STEP 5: 実装 → テスト実行（RED → GREEN）
  └─ 対象の features/*.feature を必ず読んでから実装を始める
  └─ docs/guides/bdd-workflow-guide.md に RED→GREEN の手順あり
```

**各STEPで必ずユーザーの承認を得てから次のSTEPに進む。承認なしに次へ進まない。**

---

## 絶対ルール：仕様ファースト

```
1. 仕様を .feature またはテストコードで先に書く（RED）
2. テストを実行して RED を確認する
3. テストが通る最小限の実装を書く
4. GREEN を確認する
5. リファクタリングして GREEN のままか確認する
```

**「テストを書いていないコードは存在しないのと同じ」**

---

## Claude が守るべきこと

1. **app/Models/ にファイルあり → 必ず影響範囲調査を実施する**（docs/guides/impact-report-guide.md を読む）
2. **影響範囲レポートへの承認なしに実装へ進まない**
3. **実装前に対象の features/*.feature を必ず読む**
4. **テストなしの実装コードを書かない**
5. **テスト RED を確認してから実装に進む**
6. **実装後に GREEN を確認する**
7. **過剰な実装・先読み設計をしない**（テストが要求する最小限のみ）

---

## ディレクトリ構造

```
.
├── docs/
│   ├── guides/              # Claude が各STEPで読む手順書（必要時に参照）
│   │   ├── step3-design-checklist.md
│   │   ├── step4-testcase-checklist.md
│   │   ├── feature-file-guide.md
│   │   ├── impact-report-guide.md
│   │   └── bdd-workflow-guide.md
│   ├── init/                # 初期フェーズのドキュメント成果物
│   │   ├── SPEC.md
│   │   ├── DESIGN.md
│   │   └── TEST_CASES.md
│   └── impact/              # 既存プロジェクトへの影響範囲レポート成果物
├── features/
│   ├── super/               # super/auth.feature 等
│   ├── admin/               # admin/survey.feature 等
│   └── app/                 # app/survey_response.feature 等
├── tests/
│   ├── Features/BDD/        # Super/ Admin/ App/ サブディレクトリ
│   ├── Unit/
│   └── stubs/               # FeatureTemplate.php / UnitTemplate.php
└── resources/specs/         # Jest テスト
```

---

## テスト実行コマンド

```bash
php artisan test                                                   # PHP 全テスト
php artisan test tests/Features/BDD/Admin/SurveyTest.php          # 特定ファイル
php artisan test --filter="アンケートを公開できる"                  # メソッド名フィルタ
npm test                                                           # JS 全テスト
npm test -- --watch                                                # JS ウォッチモード
npm run php:test:watch                                             # PHP ウォッチモード
```

---

## 参考ドキュメント

- [docs/guides/](docs/guides/) — 各STEPの詳細手順書（STEPごとに必ず読む）
- [docs/init/SPEC.md](docs/init/SPEC.md) — 要件定義書
- [docs/init/DESIGN.md](docs/init/DESIGN.md) — 設計方針
- [BDD_SETUP.md](BDD_SETUP.md) — 環境構築の詳細
