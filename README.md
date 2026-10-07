# Fitness Tracker

A beginner-friendly **Fitness Tracker** built with plain **PHP, MySQL, HTML, and CSS** for XAMPP. It includes account registration and login, profile management, workout tracking, meal logging, progress logging, and a dashboard that summarizes recent activity.

## Features

- User registration and login with secure password hashing
- Session-protected pages for authenticated users
- Editable user profile with health and goal details
- Workout logging with date, type, duration, calories, and notes
- Meal logging with meal type, food name, calories, and notes
- Progress logging with weight updates and goal notes
- Dashboard summary with totals, latest weight, and recent activity

## Tech Stack

- PHP 8+
- MySQL / MariaDB
- HTML5
- CSS3
- XAMPP on macOS

## Project Structure

```text
Fitness_Tracker/
├── assets/
│   └── css/
├── config/
├── controllers/
├── database/
│   └── schema.sql
├── helpers/
├── models/
├── views/
│   └── partials/
├── dashboard.php
├── index.php
├── login.php
├── logout.php
├── meals.php
├── profile.php
├── progress.php
├── register.php
└── workouts.php
```

## Local Setup With XAMPP

1. Start **Apache** and **MySQL** from the XAMPP control panel.
2. Open **phpMyAdmin** or the MySQL CLI.
3. Import [`database/schema.sql`](./database/schema.sql) to create the `fitness_tracker` database and tables.
4. Confirm the database settings in [`config/database.php`](./config/database.php).
5. Place the project inside `htdocs` as `Fitness_Tracker`.
6. Open the app at `http://localhost/Fitness_Tracker/`.

## Troubleshooting

- If `register.php` or `login.php` shows a 500 error before form submit, make sure Apache can write sessions. This project stores sessions in `storage/sessions/`.
- If form submit fails with a database error, make sure MySQL is running and that you imported [`database/schema.sql`](./database/schema.sql).
- The configured database name is `fitness_tracker`, so your imported database and [`config/database.php`](./config/database.php) must match.

## Database Import

If you want to import from the terminal, run:

```bash
/Applications/XAMPP/xamppfiles/bin/mysql -u root < database/schema.sql
```

If your MySQL root user has a password, add `-p` and enter it when prompted.

## How To Use The App

1. Register a new account.
2. Log in with your email and password.
3. Update your profile details.
4. Add workouts, meals, and progress entries.
5. Check the dashboard for quick summaries and recent activity.

## Learning Notes

- `helpers/` contains reusable functions for auth, redirects, sessions, validation, and database access.
- `models/` contains database queries using prepared statements.
- `controllers/` validate form input and coordinate model calls.
- `views/partials/` contains the shared layout pieces.

## Known Future Improvements

- Add CSRF protection
- Add charts for progress trends
- Add goal reminders and notifications
- Add search and filtering for logs
- Add automated tests

## Git Setup

This repo can be initialized locally and connected to the existing GitHub repository:

```bash
git init
git add .
git commit -m "Build Fitness Tracker"
git branch -M main
git remote add origin https://github.com/SuganjanDev/Fitness_Tracker.git
git push -u origin main
```
