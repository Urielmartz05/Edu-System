# Edu-System

Public student registration creates a pending account. An administrator must approve it from user management before the student can sign in. Registration does not send an activation email or expose activation tokens.

The optional verification-email helper requires `APP_BASE_URL` (the application's trusted public base URL) and `MAIL_FROM` in the PHP server environment, plus a working PHP `mail()` transport. It never derives activation links from the request's Host header. External email delivery depends on the deployed server's mail configuration.

Administrator accounts currently have full management access; limited administrator scopes and stored course descriptions are not offered by the creation form.
