# Google SMTP and email verification

New customer accounts created from the storefront stay pending until the customer opens a single-use verification link. The link expires after 24 hours. After verification, the customer is signed in and returned to the page they were trying to reach, including checkout. Existing accounts and administrator-created accounts are unchanged.

Registration is blocked before account creation while the SMTP username or app password is missing. If the SMTP server rejects a message after an account has been created, that account remains pending and the login page offers a resend form.

## Configure Google SMTP

The default transport uses Google Gmail SMTP at `smtp.gmail.com`, port `587`, with STARTTLS. The WordPress mailer also accepts port `465` with SSL if those values are overridden.

1. Turn on [2-Step Verification](https://support.google.com/accounts/answer/185839) for the Google account that will send shop mail.
2. Create a Google [App Password](https://support.google.com/accounts/answer/185833). Google requires 2-Step Verification for app passwords; some managed or protected accounts may not offer them.
3. Copy `.env.example` to a private `.env` file and replace the Google account and app-password placeholders. The sample is safe to commit; the real `.env` is ignored by Git.
4. Pass those values into the WordPress container as environment variables. Docker Compose can read the private `.env` beside the compose file, but it does not automatically inject every variable into a container, so map them explicitly:

```yaml
services:
  wordpress:
    environment:
      PAXIG_SMTP_USERNAME: ${PAXIG_SMTP_USERNAME}
      PAXIG_SMTP_APP_PASSWORD: ${PAXIG_SMTP_APP_PASSWORD}
      # Optional; these are the defaults.
      PAXIG_SMTP_HOST: ${PAXIG_SMTP_HOST:-smtp.gmail.com}
      PAXIG_SMTP_PORT: ${PAXIG_SMTP_PORT:-587}
      PAXIG_SMTP_ENCRYPTION: ${PAXIG_SMTP_ENCRYPTION:-tls}
      PAXIG_SMTP_FROM_EMAIL: ${PAXIG_SMTP_USERNAME}
      PAXIG_SMTP_FROM_NAME: ${PAXIG_SMTP_FROM_NAME:-PAXIG Shoes}
```

After editing `.env`, recreate the WordPress container so it receives the settings. This theme repository does not include the site's Docker Compose file, so add the mapping above to the Compose configuration that starts WordPress. Do not commit the app password. Spaces in a copied Google App Password are ignored. The From address defaults to the SMTP username; if you override it, use an address Google permits that account to send as.

The same values can be supplied as `PAXIG_SMTP_*` constants in `wp-config.php`, which must remain outside version control. The code does not store SMTP credentials in the WordPress database.

## What the flow does

- A storefront registration creates a WooCommerce customer with email-verification-required metadata and a random token hash. The raw token is only included in the email link.
- The customer cannot log in until the link is used. Verification is one-time; expired or invalid links do not activate the account.
- The login page can resend a link. Responses do not reveal whether an email address has an account, and resend requests are rate-limited per address.
- SMTP is configured through WordPress's `phpmailer_init` hook. Google documents `smtp.gmail.com` and TLS on port `587`; WordPress documents the PHPMailer hook and `wp_mail()` behavior in its [mail API reference](https://developer.wordpress.org/reference/hooks/phpmailer_init/).

Google SMTP values are based on [Google's Gmail client settings](https://support.google.com/mail/answer/7104828). Google notes that an app password requires 2-Step Verification and is revoked when the Google Account password changes.
