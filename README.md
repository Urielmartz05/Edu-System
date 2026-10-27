# Edu-System

Email verification requires `APP_BASE_URL` (the application's public base URL, for example `https://campus.example.com`) and `MAIL_FROM` (a valid sender address) in the PHP server environment. Configure PHP's `mail()` transport through `php.ini` or the server mail service. Registration never exposes activation links in the browser. If delivery fails, the account remains unverified and an administrator can activate it from user management.

Run the isolated regression checks with `php tests/regressionFixesTest.php`. They use simulated database connections and temporary files; they do not connect to the application database or send email.
