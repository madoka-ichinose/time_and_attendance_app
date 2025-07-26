# 概要

- このアプリケーションは、Laravelを使用して構築された勤怠管理システムです。
- 一般ユーザーは打刻や勤怠の確認・申請ができ、管理者ユーザーは全体の勤怠や申請の管理・承認が行えます。

## ユーザーの種類

- 一般ユーザー（スタッフ）：出退勤の打刻・勤怠確認・修正申請が可能
- 管理者ユーザー（マネージャー）：勤怠管理・修正申請の承認・スタッフ管理が可能

## 使用技術

- フレームワーク：Laravel
- 認証：Laravel Fortify
- フロントエンド：Blade
- データベース：MySQL
  
## 環境構築

1. リポジトリをクローン
   - git clone git@github.com:madoka-ichinose/time_and_attendance_app.git
   - cd time_and_attendance_app
2. .env設定
   - cp .env.example .env
   - php artisan key:generate
3. パッケージインストール
   - composer install
   - npm install && npm run dev
4. マイグレーション＆シーディング
   - php artisan migrate --seed
5. サーバー起動
   - php artisan serve
6. doctrine/dbal パッケージをインストール
   - composer require doctrine/dbal

## 管理者ユーザーと一般ユーザーのログイン情報

# 管理者ユーザー

- メールアドレス：admin@example.com
- パスワード：admin2025

# 一般ユーザー

- メールアドレス：user1@example.com
- パスワード：password2025