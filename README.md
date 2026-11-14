# LIFNOOK

A personal digital space for memories, achievements, growth and future dreams.
Built with **HTML, CSS, JavaScript, PHP and MySQL** (runs on XAMPP).

## Features
- Register / Login for every user (passwords stored hashed)
- Mood check-in after login, followed by a motivational quote based on the mood
- **Memories**: add, edit, delete and search
- **Vision Board**: add goals, track progress with a slider, mark as achieved
- **Achievements**: auto-added when a goal is completed, or add your own (with confetti)
- Responsive design that works on mobile and desktop

## How to run (XAMPP)
1. Install and open **XAMPP**. Start **Apache** and **MySQL**.
2. Copy the `lifnook` folder into `C:\xampp\htdocs\` (on Mac/Linux: the `htdocs` folder of XAMPP).
3. Open **http://localhost/phpmyadmin** > click **Import** > choose `database.sql` > **Go**.
   This creates the `lifnook` database, all tables and the quotes.
4. Open **http://localhost/lifnook/** in your browser.
5. Click **Create your space**, register, and start using it.

If your MySQL has a password, change it in `includes/config.php`.

## Folder structure
```
lifnook/
  index.php            redirects to login or home
  login.php, register.php, logout.php
  mood.php             mood selection + quote
  dashboard.php        home with 2 options + achievements strip
  memories.php         memories CRUD
  vision.php           vision board CRUD + progress
  achievements.php     achievements CRUD
  database.sql         database + tables + quotes
  includes/            config.php (DB + helpers), header.php, footer.php
  assets/css/style.css
  assets/js/app.js
```

## Database tables
users, moods, quotes, memories, goals, achievements
(all user data is linked with `user_id` and deleted automatically if the user is removed).

## Security used
- Prepared statements (PDO) against SQL injection
- `password_hash` / `password_verify`
- `htmlspecialchars` output escaping against XSS
- CSRF token on every form
- Each query is limited to the logged-in user's own data
