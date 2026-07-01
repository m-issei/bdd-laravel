# アンケートシステム 設計方針

最終更新: 2026-07-01

---

## 1. ディレクトリ構造

```
app/
├── Models/
│   ├── Organization.php          # 組織
│   ├── Admin.php                 # 管理者（スーパー管理者も兼用）
│   ├── Participant.php           # 参加者
│   ├── Survey.php                # アンケート
│   ├── SurveySection.php         # 見出し
│   ├── Question.php              # 質問
│   ├── AnswerType.php            # 回答方式（将来拡張用テーブル）
│   ├── Response.php              # 回答セッション（参加者×アンケート）
│   ├── ResponseAnswer.php        # 個別回答（質問×値）
│   └── Announcement.php          # お知らせ
│
├── Http/
│   ├── Controllers/
│   │   ├── Super/
│   │   │   ├── AuthController.php
│   │   │   ├── OrganizationController.php
│   │   │   └── AdminAccountController.php
│   │   ├── Admin/
│   │   │   ├── AuthController.php
│   │   │   ├── ParticipantController.php
│   │   │   ├── SurveyController.php
│   │   │   ├── DashboardController.php
│   │   │   └── AnnouncementController.php
│   │   └── App/
│   │       ├── AuthController.php
│   │       ├── SurveyResponseController.php
│   │       └── AnnouncementController.php
│   │
│   ├── Middleware/
│   │   ├── SuperAdminMiddleware.php   # /super 用：super ガード認証確認
│   │   ├── AdminMiddleware.php        # /admin 用：admin ガード認証確認
│   │   ├── ParticipantMiddleware.php  # /app 用：participant ガード認証確認
│   │   └── EnsureActiveAccount.php   # 有効フラグチェック（is_active）
│   │
│   └── Requests/
│       ├── Super/
│       │   ├── StoreOrganizationRequest.php
│       │   ├── UpdateOrganizationRequest.php
│       │   ├── StoreAdminAccountRequest.php
│       │   └── UpdateAdminAccountRequest.php
│       ├── Admin/
│       │   ├── StoreParticipantRequest.php
│       │   ├── UpdateParticipantRequest.php
│       │   ├── StoreSurveyRequest.php
│       │   ├── UpdateSurveyRequest.php
│       │   ├── StoreSurveySectionRequest.php
│       │   ├── UpdateSurveySectionRequest.php
│       │   ├── StoreQuestionRequest.php
│       │   ├── UpdateQuestionRequest.php
│       │   ├── StoreAnnouncementRequest.php
│       │   └── UpdateAnnouncementRequest.php
│       └── App/
│           ├── SaveResponseRequest.php
│           └── SubmitResponseRequest.php
│
├── Services/
│   ├── Super/
│   │   ├── OrganizationService.php
│   │   └── AdminAccountService.php
│   ├── Admin/
│   │   ├── ParticipantService.php
│   │   ├── SurveyService.php
│   │   ├── DashboardService.php
│   │   └── AnnouncementService.php
│   └── App/
│       └── SurveyResponseService.php
│
├── Livewire/
│   ├── Admin/
│   │   ├── SurveyBuilder.php          # 見出し・質問のD&D並び替え
│   │   └── DashboardChart.php         # Chart.js 棒グラフ
│   └── App/
│       └── SurveyResponseForm.php     # 途中保存・最終提出
│
└── Enums/
    ├── SurveyStatus.php               # draft / published
    ├── AdminStatus.php                # active / inactive
    ├── ParticipantStatus.php          # active / inactive
    └── AnnouncementStatus.php         # draft / published
```

---

## 2. コントローラーの責務

**基本方針**: コントローラーはリクエスト受取とレスポンス返却のみ。ビジネスロジックはServiceへ委譲する。

### Super/OrganizationController

| メソッド | HTTPメソッド | 処理概要 |
|----------|-------------|---------|
| `index()` | GET /super/organizations | 組織一覧（論理削除除く） |
| `store(StoreOrganizationRequest $req)` | POST /super/organizations | 新規作成 |
| `update(UpdateOrganizationRequest $req, Organization $org)` | PUT /super/organizations/{org} | 更新 |
| `destroy(Organization $org)` | DELETE /super/organizations/{org} | 論理削除 |

```php
// 実装例
public function store(StoreOrganizationRequest $request): RedirectResponse
{
    $this->organizationService->create($request->validated());
    return redirect()->route('super.organizations.index');
}
```

### Admin/SurveyController

| メソッド | HTTPメソッド | 処理概要 |
|----------|-------------|---------|
| `index()` | GET /admin/surveys | アンケート一覧（自組織） |
| `store(StoreSurveyRequest $req)` | POST /admin/surveys | 新規作成（下書き） |
| `update(UpdateSurveyRequest $req, Survey $survey)` | PUT /admin/surveys/{survey} | 編集（下書きのみ） |
| `publish(Survey $survey)` | POST /admin/surveys/{survey}/publish | 公開（1問以上必要） |
| `destroy(Survey $survey)` | DELETE /admin/surveys/{survey} | 論理削除 |

### App/SurveyResponseController

| メソッド | HTTPメソッド | 処理概要 |
|----------|-------------|---------|
| `index()` | GET /app/surveys | 公開中アンケート一覧 |
| `show(Survey $survey)` | GET /app/surveys/{survey} | 回答フォーム or 閲覧（提出済み判定） |
| `save(SaveResponseRequest $req, Survey $survey)` | POST /app/surveys/{survey}/save | 途中保存 |
| `submit(SubmitResponseRequest $req, Survey $survey)` | POST /app/surveys/{survey}/submit | 最終提出 |

---

## 3. サービス層の責務

### Super/OrganizationService

```php
create(array $data): Organization
update(Organization $org, array $data): Organization
delete(Organization $org): void          # 論理削除
```

### Super/AdminAccountService

```php
create(array $data): Admin
update(Admin $admin, array $data): Admin
toggleActive(Admin $admin, bool $active): void
delete(Admin $admin): void
```

### Admin/SurveyService

```php
create(Organization $org, array $data): Survey
update(Survey $survey, array $data): Survey   # 下書きのみ可
publish(Survey $survey): void                  # 1問以上チェック・状態遷移ガード
delete(Survey $survey): void
reorderItems(Survey $survey, array $order): void   # D&D並び替え
moveSectionQuestion(Question $q, ?int $sectionId): void
```

ビジネスルール担当箇所:
- `publish()` — 質問0件拒否、published→draftへの差し戻し拒否
- `update()` — published状態の場合は例外をスロー

### Admin/DashboardService

```php
getResponseCount(Survey $survey): int
getAverageByQuestion(Survey $survey): Collection   # [question_id => avg]
```

集計対象: `submitted_at IS NOT NULL` のResponseのみ（途中保存除外）

### App/SurveyResponseService

```php
saveProgress(Participant $p, Survey $survey, array $answers): Response
submit(Participant $p, Survey $survey, array $answers): Response  # 全問回答チェック・再提出拒否
findOrCreateResponse(Participant $p, Survey $survey): Response
```

---

## 4. Enum 定義

### SurveyStatus

```php
enum SurveyStatus: string
{
    case Draft     = 'draft';
    case Published = 'published';
}
```

遷移: `Draft → Published` のみ（`Published → Draft` は不可）

### AdminStatus / ParticipantStatus

```php
enum AdminStatus: string
{
    case Active   = 'active';
    case Inactive = 'inactive';
}

enum ParticipantStatus: string
{
    case Active   = 'active';
    case Inactive = 'inactive';
}
```

### AnnouncementStatus

```php
enum AnnouncementStatus: string
{
    case Draft     = 'draft';
    case Published = 'published';
}
```

---

## 5. バリデーション設計

### Super/StoreOrganizationRequest

| フィールド | ルール |
|-----------|--------|
| `name` | required / string / max:255 |
| `description` | nullable / string |

### Super/StoreAdminAccountRequest

| フィールド | ルール |
|-----------|--------|
| `organization_id` | required / exists:organizations,id |
| `name` | required / string / max:100 |
| `email` | required / email / unique:admins,email |
| `password` | required / string / min:8 |

### Admin/StoreParticipantRequest

| フィールド | ルール |
|-----------|--------|
| `name` | required / string / max:100 |
| `email` | required / email / unique:participants,email（同一組織スコープ） |
| `password` | required / string / min:8 |

同一組織スコープの一意制約:
```php
Rule::unique('participants', 'email')->where('organization_id', $this->user()->organization_id)
```

### Admin/StoreSurveyRequest

| フィールド | ルール |
|-----------|--------|
| `title` | required / string / max:255 |

### Admin/StoreQuestionRequest

| フィールド | ルール |
|-----------|--------|
| `text` | required / string / max:500 |
| `section_id` | nullable / exists:survey_sections,id |
| `order` | required / integer / min:0 |

### Admin/StoreSurveySectionRequest

| フィールド | ルール |
|-----------|--------|
| `title` | required / string / max:100 / unique（同一アンケート内） |

同一アンケート内の一意制約:
```php
Rule::unique('survey_sections', 'title')->where('survey_id', $this->route('survey')->id)
```

### Admin/StoreAnnouncementRequest

| フィールド | ルール |
|-----------|--------|
| `title` | required / string / max:255 |
| `body` | required / string / max:10000 |

### App/SubmitResponseRequest

| フィールド | ルール |
|-----------|--------|
| `answers` | required / array |
| `answers.*.question_id` | required / exists:questions,id |
| `answers.*.value` | required / integer / between:1,5 |

---

## 6. 横断的関心事

### マルチテナントスコープ

- 管理者・参加者は `organization_id` を持ち、ログインユーザーと異なる組織のリソースへのアクセスは全て 404 を返す
- Model に `HasOrganizationScope` Trait を適用し、`boot()` で `addGlobalScope` を設定
- Controller の `authorize()` またはモデルバインディングの `resolveRouteBindingQuery()` でスコープを適用

```php
// 例: Admin側のSurveyモデルバインディング
public function resolveRouteBinding($value, $field = null): ?Model
{
    return $this->where('id', $value)
                ->where('organization_id', auth('admin')->user()->organization_id)
                ->firstOrFail();  // 404
}
```

### 論理削除

全モデルに `SoftDeletes` Trait を使用する。

| モデル | 論理削除後の回答データ |
|--------|----------------------|
| Organization | — |
| Admin | — |
| Participant | ResponseおよびResponseAnswerは保持 |
| Survey | ResponseおよびResponseAnswerは保持 |
| Announcement | — |

### 認証ガード設定

`config/auth.php` に3ガードを設定する:

| ガード名 | モデル | ログインURL |
|---------|--------|------------|
| `super` | App\Models\Admin (is_super=true) | /super/login |
| `admin` | App\Models\Admin | /admin/login |
| `participant` | App\Models\Participant | /app/login |

管理者とスーパー管理者は同一テーブル（`admins`）で `is_super` フラグで区別する。

### EnsureActiveAccount ミドルウェア

ログイン後のリクエストで `is_active = false` のアカウントを即時ログアウトする。`admin` と `participant` ガードに適用する。
