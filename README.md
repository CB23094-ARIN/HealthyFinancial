# HealthyFinancial

AI-powered student finance assistant for Malaysians.

## Features

- Auto-categorization of spending using Gemini AI
- Receipt scanning (OCR + AI parsing)
- "Can I afford?" smart checker with AI-generated advice that changes based on each user's item, price, and remaining budget
- Financial health score + saving streak
- Campus leaderboard & gamification
- PTPTN Mode concept planned as the next major feature

## Pitch Highlight: PTPTN Mode

HealthyFinancial can grow beyond normal expense tracking by becoming a student finance companion built around PTPTN life. The planned PTPTN Mode would help students turn loan money into a practical monthly survival plan instead of treating it like one big balance.

The idea is simple but memorable: once a student enters their PTPTN amount, monthly allowance, semester length, and fixed commitments, the app can recommend a safe weekly spending limit, warn when spending is too fast, and show how long the money can realistically last. This makes the product feel specifically built for Malaysian students, not just a generic budgeting app.

For the pitch, emphasize PTPTN Mode as the future differentiator:

- It connects directly to a real Malaysian student money problem.
- It turns a large loan disbursement into weekly spending guidance.
- It can pair with receipt scanning and AI categorization to detect where PTPTN money is going.
- It gives the leaderboard a stronger purpose by rewarding students who keep their PTPTN runway healthy.

Status: PTPTN Mode is not implemented yet. It is a planned feature and pitch direction.

## Setup

```bash
composer install
npm install && npm run build (optional)
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```
