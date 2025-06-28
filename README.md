Laravel Telegram Feedback & Email Notification System
=====================================================

A lightweight Laravel-based feedback system that allows users to send feedback messages via Telegram bot and delivers those messages to both Telegram groups and admin email inbox. Ideal for small businesses, startups, customer support teams, and Telegram-based services.

-----------------------------------------------------
🚀 Features
-----------------------------------------------------

- Telegram Bot integration via defstudio/telegraph
- User-friendly admin panel to add/edit bots
- Receives feedback messages via Telegram
- Stores messages in database
- Sends responses back to users via Telegram
- Forwards all messages to a Telegram group
- Sends email notifications to admin
- Laravel 11 compatible

-----------------------------------------------------
📂 Folder Structure
-----------------------------------------------------

/app/Http/Controllers/Telegrambot.php
/app/Models/FeedbackRequest.php
/app/Models/FeedbackResponse.php
/resources/views/telegrambot/index.blade.php
/routes/web.php
...

-----------------------------------------------------
🛠 Installation
-----------------------------------------------------

Please refer to the install.txt file for step-by-step instructions.

Basic steps:
1. Clone or unzip the project.
2. Run `composer install`.
3. Configure your `.env` file (Telegram Bot Token, Email, DB, etc).
4. Run migrations with `php artisan migrate`.
5. Set webhook via `php artisan telegraph:set-webhook {bot_id}`.
6. Access `/` to add your bot via UI.

-----------------------------------------------------
⚙️ Environment Variables
-----------------------------------------------------

MY_GROUP_ID=-1001234567890
MY_EMAIL=your_email@example.com
TELEGRAM_BOT_TOKEN=xxx:yyy

-----------------------------------------------------
💬 Feedback Format
-----------------------------------------------------

- All messages are stored in the `feedback_requests` table.
- Admin can reply via Telegram by replying to the forwarded message (includes #ID{id}#).
- The system will automatically send response back to user and mark the message as answered.

-----------------------------------------------------
🧑‍💻 Dependencies
-----------------------------------------------------

- Laravel 11+
- defstudio/telegraph
- Bootstrap 5 (UI)
- Mail (default PHP or SMTP supported)

-----------------------------------------------------
📄 License
-----------------------------------------------------

This project is open-sourced under the MIT license (see license.txt)

-----------------------------------------------------
🙏 Credits
-----------------------------------------------------

Developed by Boburbek Ziyodullaev
https://codecanyon.net/user/ubsoftware

For support, please open a comment under the item or contact via Envato profile.
