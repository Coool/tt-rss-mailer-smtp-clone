# Installation

1. Git clone plugin to tt-rss/plugins.local/mailer_smtp
2. Enable via `TTRSS_PLUGINS` global configuration setting (this is a system plugin, it can't be enabled per-user).
3. Set the following plugin settings via `.env` or `putenv()` in `config.php`:

```ini
TTRSS_SMTP_SERVER=localhost:587
# Use this server (hostname:port). Empty value disables plugin.

TTRSS_SMTP_LOGIN=
TTRSS_SMTP_PASSWORD=
# Login/password for SMTP auth, if needed.

TTRSS_SMTP_SECURE=tls
#Use secure connection. Allowed values: `ssl`, `tls`, or empty.

#TTRSS_SMTP_SKIP_CERT_CHECKS=
# Accept all SSL certificates, use with caution.

#TTRSS_SMTP_CA_FILE=
# Use custom CA certificate for SSL/TLS secure connections. Only used if TTRSS_SMTP_SKIP_CERT_CHECKS is false.
```


## Upgrading from legacy setup

If you don't use `.env`, migrate `config.php` values as follows:

`define('SMTP_SERVER', 'hostname');` &rarr; `putenv('TTRSS_SMTP_SERVER=hostname);`
