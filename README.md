# 📬 Laravel Telegram Feedback & Email Notifier System

A clean and secure feedback collection system via Telegram Bot with automatic email notifications. Easily integrate a Telegram bot with your Laravel application, collect messages from users, and get them delivered directly to your email.

---

---

This product is sold under Envato Regular License.

## 🚀 Features

- 🔐 **Secure admin panel** (HTTP Basic Auth)
- 🤖 Add/edit your own Telegram bots in admin interface
- 📨 Collect feedback messages via Telegram bot
- 📧 Automatically send messages to your email
- 👥 Automatically forward messages to your Telegram group
- 🔄 Reply to user feedback via Telegram group (automatic delivery)
- 🧼 Clean codebase following Laravel standards
- 📦 Quick installation with `.env.example`

---

## ⚠ Important Notice

> 🛠 The admin panel is designed **only for managing your Telegram bots** (adding/editing token & name).  
> ❌ It does not store or display feedback messages in the interface.  
> All feedback is delivered via **Telegram group and email only**.

---

## 📋 Requirements

- PHP 8.2 or newer
- Laravel 11 or newer
- SSL-enabled web server (HTTPS is required for Telegram webhook)
- Working mail configuration (SMTP or `mail()`)

---

## 📦 What's Included?

- Full Laravel project
- Admin UI for managing Telegram bots
- Email + Telegram group delivery
- Telegram webhook integration via `defstudio/telegraph`
- Example `.env` configuration
- Installation guide (`install.txt`)
- MIT or Regular license

---

## 🔧 Installation

1. Upload the project to your server
2. Run `composer install`
3. Copy `.env.example` to `.env` and fill in your values
4. Run `php artisan key:generate`
5. Run `php artisan migrate`
6. Visit the project in browser – it will be protected with HTTP Basic Auth
7. Log in, add your Telegram bot, and enjoy!

---

## 📞 Support

If you need help installing or setting up, feel free to contact us via the comments section or your CodeCanyon dashboard.

---

## 👮 License

This project is sold under the Envato Regular License. You are not allowed to resell, redistribute, or sublicense it outside of Envato.

---

Thank you for purchasing!
