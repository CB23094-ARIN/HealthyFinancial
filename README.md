# Project name: HealthyFinancial.
HealthyFinancial is an AI-powered personal finance management web application developed using Laravel and MySQL to help users maintain healthy financial habits. The system enables users to track expenses, manage their budgets, and scan receipts, while AI automatically categorizes spending and provides personalized financial recommendations.


# Leaderboard points formula 
transactionCount: every transaction counts as 1 point.
activeDays: each unique day with at least one transaction counts as 10 points.
saving_streak: every full 7-day streak gives 25 bonus points.
PTPTN Mode bonus: on-track PTPTN users earn 20 bonus points; tight-but-protected users earn 10 bonus points.
Multiple transactions on the same day increase transaction points, but only count as one active day.
Receipt scan items count as transactions too, because each item is saved as a transaction.

# Features: 
- Auto-categorization of spending using Gemini AI
- Receipt scanning (OCR + AI parsing)
- "Can I afford?" smart checker with AI-generated advice that changes based on the user's item, price, budget, and spending context
- Financial health score + saving streak
- University leaderboard 
- PTPTN Mode: a loan-aware student budgeting mode with safe daily spend, protected reserve, affordability guardrails, and a leaderboard bonus for staying disciplined

# PTPTN Mode 
PTPTN Mode is the standout student-focused feature. Instead of only saying "you spent RM X", HealthyFinancial turns a student's monthly budget or PTPTN money into a survival plan:

- Shows how much the student can safely spend per day until month end.
- Keeps the monthly budget visible as the monthly budget, while the remaining balance includes PTPTN when PTPTN Mode is enabled.
- Shows how much spending has moved into PTPTN after the monthly budget is exceeded.
- Protects a small PTPTN reserve before approving non-essential purchases.
- Makes "Can I afford this?" stricter when the purchase would eat into the reserve.
- Adds a PTPTN badge and bonus points when the user stays on track.

