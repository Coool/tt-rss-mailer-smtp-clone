Warning: this plugin uses PHPMailer which is a library with regularly discovered
vulnerabilities. Use at your own risk.

Installation
============

1. Git clone plugin to tt-rss/plugins.local/mailer_smtp
2. Enable in config.php directive PLUGINS (this is a system plugin, it can't be enabled
per-user).
3. Add the following to config.php:

```
	define('SMTP_SERVER', '');
	// Hostname:port combination to send outgoing mail (i.e. localhost:25).
	// Blank - use system MTA.

	define('SMTP_LOGIN', '');
	define('SMTP_PASSWORD', '');
	// These two options enable SMTP authentication when sending
	// outgoing mail. Only used with SMTP_SERVER.

	define('SMTP_SECURE', '');
	// Used to select a secure SMTP connection. Allowed values: ssl, tls,
	// or empty.

	define('SMTP_SKIP_CERT_CHECKS', false);
	// Accept all SSL certificates, use with caution.
```

At least SMTP_SERVER needs to set for plugin to work.
