# Project name: HealthyFinancial.
HealthyFinancial is an AI-powered personal finance management web application developed using Laravel and MySQL to help users maintain healthy financial habits. The system enables users to track expenses, manage their budgets, and scan receipts, while AI automatically categorizes spending and provides personalized financial recommendations.


# Leaderboard points formula 
transactionCount: every transaction counts as 1 point.
activeDays: each unique day with at least one transaction counts as 10 points.
saving_streak: every full 7-day streak gives 25 bonus points.
Multiple transactions on the same day increase transaction points, but only count as one active day.
Receipt scan items count as transactions too, because each item is saved as a transaction.

Features: 
- Auto-categorization of spending using Gemini AI
- Receipt scanning (OCR + AI parsing)
- "Can I afford?" smart checker with cute advice
- Financial health score + saving streak
- Campus leaderboard & gamification
- PTPTN mode ready