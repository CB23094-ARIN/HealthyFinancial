# HealthyFinancial 💚

AI-powered student finance assistant for Malaysians.

## Features

- Auto-categorization of spending using Gemini AI
- Receipt scanning (OCR + AI parsing)
- "Can I afford?" smart checker with cute advice
- Financial health score + saving streak
- Campus leaderboard & gamification
- PTPTN mode ready

## Setup

```bash
composer install
npm install && npm run build (optional)
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve