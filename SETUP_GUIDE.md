# UpHub Handover & Setup Guide

Welcome! To get the UpHub system working on your local machine, follow these steps to set up your own API keys and environment.

## 1. Google Maps API (For the Social Services Map)
The system uses Google Maps to show local support centers. 
1. Go to the [Google Cloud Console](https://console.cloud.google.com/).
2. Create a new project called "UpHub".
3. Enable the **Maps JavaScript API**.
4. Create an **API Key** under "Credentials".
5. Open `config/config.php` and paste your key here:
   ```php
   define('GOOGLE_MAPS_API_KEY', 'YOUR_ACTUAL_KEY_HERE');
   ```

## 2. Email Notifications (Gmail SMTP)
The system sends emails when people apply for jobs. It uses Symfony Mailer.
1. Go to your Google Account settings -> Security.
2. Enable **2-Step Verification**.
3. Search for **"App Passwords"**.
4. Create a new App Password (select "Other" and name it "UpHub").
5. Google will give you a 16-character code (e.g., `abcd efgh ijkl mnop`).
6. Open `config/config.php` and update the `MAILER_DSN`:
   ```php
   // Replace 'YOUR_EMAIL' with your gmail (encoded @ as %40) 
   // Replace 'YOUR_APP_PASSWORD' with the 16-char code (no spaces)
   define('MAILER_DSN', 'gmail+smtp://YOUR_EMAIL%40gmail.com:YOUR_APP_PASSWORD@default');
   define('MAILER_FROM', 'your-email@gmail.com');
   ```

## 3. Database Setup
1. Open XAMPP and start Apache and MySQL.
2. Go to [http://localhost/phpmyadmin](http://localhost/phpmyadmin).
3. Create a new database named `uphub`.
4. Import the file located at `sql/install.sql`.

## 4. Final Check
Once you've updated the `config/config.php` file, visit:
`http://localhost/UpHub/index.php`

Everything should now be functional with your own credentials!
