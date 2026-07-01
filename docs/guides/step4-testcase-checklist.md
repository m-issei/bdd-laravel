# STEP 4 テストケース設計チェックリスト

STEP 4b でユーザーに提示するテストケースは、以下の4種類を**必ず全て**含める。
正常系だけを書いて終わりにしない。

---

## 必須の4種類

### 1. 正常系
仕様通りに動作する基本パスを検証する。

### 2. 異常系（必須）

以下のパターンを漏れなく含める：

- **バリデーション違反**: 必須項目の未入力、文字数超過、不正な形式（メール等）
- **権限違反**: 他ロールのURLへのアクセス、他組織リソースへのアクセス
- **存在しないリソース**: 存在しないIDへのアクセス（404）
- **状態違反**: 許可されていない操作（公開済みアンケートの編集、提出済み回答の上書き等）

### 3. 境界値テスト（必須）

仕様に文字数・数値の制限がある場合は以下を全てテストする：

- 上限値ちょうど（許容される）
- 上限値+1（拒否される）
- 下限値ちょうど（許容される）
- 下限値-1（拒否される）

例: タイトル255文字上限なら「255文字 → OK」「256文字 → NG」を両方書く。

### 4. 状態・パターン網羅（必須）

Enumや状態フラグが絡む処理は**全パターン**をテストする：

- 全てのステータス値（例: draft / published それぞれでの動作）
- 全ての状態遷移パス（許可されるもの・拒否されるもの）
- 条件分岐が発生する全てのケース（例: 回答あり/なし、有効/無効）

---

## テストの書き方規約

### PHP テスト

- メソッド名は**日本語**で書く（仕様として読めること）
- `Given / When / Then` をコメントで明示する
- `@test` アノテーションを使う
- テストは1つの振る舞いだけを検証する

```php
/** @test */
public function 未認証ユーザーは保護されたページにアクセスできない(): void
{
    // When
    $response = $this->get('/admin/dashboard');

    // Then
    $response->assertRedirect('/admin/login');
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

## テストファイルのテンプレート

```bash
cp tests/stubs/FeatureTemplate.php tests/Features/BDD/Admin/SurveyTest.php
cp tests/stubs/UnitTemplate.php tests/Unit/SurveyServiceTest.php
cp resources/specs/SpecTemplate.js resources/specs/survey.spec.js
```
