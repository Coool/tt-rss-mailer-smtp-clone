<?php
require_once __DIR__ . "/vendor/autoload.php";

class mailer_smtp extends Plugin {
	/** @var PluginHost $host */
	private $host;

	const TTRSS_SMTP_SERVER = "TTRSS_SMTP_SERVER";
	const TTRSS_SMTP_LOGIN = "TTRSS_SMTP_LOGIN";
	const TTRSS_SMTP_PASSWORD = "TTRSS_SMTP_PASSWORD";
	const TTRSS_SMTP_SECURE = "TTRSS_SMTP_SECURE";
	const TTRSS_SMTP_SKIP_CERT_CHECKS = "TTRSS_SMTP_SKIP_CERT_CHECKS";
	const TTRSS_SMTP_CA_FILE = "TTRSS_SMTP_CA_FILE";

	function about() {
		return array(null,
			"Sends mail via SMTP using PHPMailer. Read README.txt before enabling.",
			"fox",
			1,
			"https://git.tt-rss.org/fox/ttrss-mailer-smtp");
	}

	function init(PluginHost $host) {
		$this->host = $host;

		Config::add(self::TTRSS_SMTP_SERVER, "", Config::T_STRING);
		Config::add(self::TTRSS_SMTP_LOGIN, "", Config::T_STRING);
		Config::add(self::TTRSS_SMTP_PASSWORD, "", Config::T_STRING);
		Config::add(self::TTRSS_SMTP_SECURE, "", Config::T_STRING);
		Config::add(self::TTRSS_SMTP_SKIP_CERT_CHECKS, "false", Config::T_BOOL);
		Config::add(self::TTRSS_SMTP_CA_FILE, "", Config::T_STRING);

		$host->add_hook(PluginHost::HOOK_SEND_MAIL, $this);
	}

	function hook_send_mail($mailer, $params) {
		if (Config::get(self::TTRSS_SMTP_SERVER)) {

			$phpmailer = new \PHPMailer\PHPMailer\PHPMailer();

			$phpmailer->isSMTP();

			$pair = explode(":", Config::get(self::TTRSS_SMTP_SERVER), 2);
			$phpmailer->Host = $pair[0];
			$phpmailer->Port = (int)$pair[1];
			$phpmailer->CharSet = "UTF-8";

			if (!$phpmailer->Port) $phpmailer->Port = 25;

			if (Config::get(self::TTRSS_SMTP_LOGIN)) {
				$phpmailer->SMTPAuth = true;
				$phpmailer->Username = Config::get(self::TTRSS_SMTP_LOGIN);
				$phpmailer->Password = Config::get(self::TTRSS_SMTP_PASSWORD);
			}

			if (Config::get(self::TTRSS_SMTP_SECURE)) {
				$phpmailer->SMTPSecure = Config::get(self::TTRSS_SMTP_SECURE);
			} else {
				$phpmailer->SMTPAutoTLS = false;
			}

			if (Config::get(self::TTRSS_SMTP_SKIP_CERT_CHECKS)) {
				$phpmailer->SMTPOptions = array(
				    'ssl' => array(
				        'verify_peer' => false,
				        'verify_peer_name' => false,
				        'allow_self_signed' => true
				    )
				);
			} else if (Config::get(self::TTRSS_SMTP_CA_FILE)) {
				$phpmailer->SMTPOptions = array(
				    'ssl' => array(
                        'cafile' => Config::get(self::TTRSS_SMTP_CA_FILE)
				    )
				);
            }

			$from_name = $params["from_name"] ? $params["from_name"] :
				Config::get(Config::SMTP_FROM_NAME);

			$from_address = $params["from_address"] ? $params["from_address"] :
				Config::get(Config::SMTP_FROM_ADDRESS);

			$phpmailer->setFrom($from_address, $from_name);
			$phpmailer->addAddress($params["to_address"], $params["to_name"]);
			$phpmailer->Subject = $params["subject"];
			$phpmailer->CharSet = "UTF-8";

			if (!empty($params["message_html"])) {
				$phpmailer->msgHTML($params["message_html"]);
				$phpmailer->AltBody = $params["message"];
			} else {
				$phpmailer->Body = $params["message"];
			}

			if (!empty($params['headers']))
				foreach ($params['headers'] as $header) {
					$phpmailer->addCustomHeader($header);
				}

			$rc = $phpmailer->send();

			if (!$rc)
				$mailer->set_error($rc . " " . $phpmailer->ErrorInfo);

			return $rc;
		}
	}

	function api_version() {
		return 2;
	}

}
?>
