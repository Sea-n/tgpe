# tg.pe 專案說明

## 概覽

tg.pe 是一個短網址服務，支援三種建立方式：網頁介面、HTTP API 和 Telegram Bot。

## 檔案結構

| 檔案 | 用途 |
|------|------|
| `index.php` | 入口路由：處理 redirect、QR code 頁面、404 錯誤 |
| `web.php` | 網頁首頁表單，含速率限制與安全檢查 |
| `api.php` | HTTP API（POST，Token 驗證），供外部程式呼叫 |
| `telegram.php` | Telegram Bot 處理器（從 sean.taipei 硬連結） |
| `database.php` | 資料庫抽象層：SQLite 為主，可選 MySQL 雙寫 mirror |
| `safety.php` | Google Safe Browsing API 查詢工具函式 |
| `config.php` | 實際設定檔（不進版本控制） |
| `config-example.php` | 設定範本 |
| `abuse.php` | 濫用舉報處理 |

## 核心邏輯

- **短碼生成**：Base58 字元集，預設 3 字元（網頁版 4 字元），最長 32 字元
- **作者標記**：Telegram 用 `TG{UserID}`，網頁用 `WEB{IP}{國家碼}`，API 依 token 定義
- **安全檢查順序**：網域黑名單 → Google Safe Browsing → AbuseIPDB（僅網頁版、非 TW IP）
- **封禁機制**：封禁用戶會軟刪除其所有連結（`deleted_at`）

## 設定要點

複製 `config-example.php` 為 `config.php`，填入：
- `TG_ADMINS`：管理員 Telegram ID 陣列
- `HTTP_API_TOKENS`：API token → 作者名稱 對應表
- `ABUSEIPDB_KEY`、`SAFE_BROWSING_API_KEY`：外部 API 金鑰（留空則跳過檢查）
- `DB_DUAL_WRITE_ENABLED`：啟用後需另建 `config-mysql.php`
