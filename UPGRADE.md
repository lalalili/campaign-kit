# 升級指南

## 0.x → 1.0.0

### 宿主端要做的事

1. 把 `composer.json` 的約束改成 `^1.0`：

   ```diff
   -"lalalili/campaign-kit": "^0.x"
   +"lalalili/campaign-kit": "^1.0"
   ```

2. 確認 `repositories` 是 `vcs` 而非指向本機 `packages/` 的 `path`：

   ```json
   { "type": "vcs", "url": "https://github.com/lalalili/campaign-kit.git" }
   ```

3. 更新並清快取：

   ```bash
   composer update lalalili/campaign-kit
   php artisan optimize:clear
   composer dump-autoload
   php artisan filament:cache-components   # 有 Filament 後台時必跑
   ```

### 破壞性變更

本次 1.0.0 **沒有移除或變更任何 public API**，純粹是版本契約與
消費模型的正規化。程式碼層面不需要調整。

若你原本用 `path` repository 搭配硬釘 `versions` 消費本套件，請改為
VCS + tag —— 前者會讓宿主停在舊版且完全不會有任何警告。

### 之後

public API 的定義、deprecation 流程與跨套件發版順序見
[SEMVER.md](https://github.com/lalalili/.github/blob/main/SEMVER.md)。
