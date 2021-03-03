<?php
require_once __DIR__ . "/vendor/autoload.php";

class mailer_smtp extends Plugin {
	/** @var PluginHost $host */
	private $host;

	const SMTP_SERVER = "SMTP_SERVER";
	const SMTP_LOGIN = "SMTP_LOGIN";
	const SMTP_PASSWORD = "SMTP_PASSWORD";
	const SMTP_SECURE = "SMTP_SECURE";
	const SMTP_SKIP_CERT_CHECKS = "SMTP_SKIP_CERT_CHECKS";
	const SMTP_CA_FILE = "SMTP_CA_FILE";

	function about() {
		return array(null,
			"Sends mail via SMTP using PHPMailer. Read README.txt before enabling.",
			"fox",
			1,
			"https://git.tt-rss.org/fox/ttrss-mailer-smtp");
	}

	function init(PluginHost $host) {
		$this->host = $host;

		Config::add(self::SMTP_SERVER, "", Config::T_STRING);
		Config::add(self::SMTP_LOGIN, "", Config::T_STRING);
		Config::add(self::SMTP_PASSWORD, "", Config::T_STRING);
		Config::add(self::SMTP_SECURE, "", Config::T_STRING);
		Config::add(self::SMTP_SKIP_CERT_CHECKS, "false", Config::T_BOOL);
		Config::add(self::SMTP_CA_FILE, "", Config::T_STRING);

		$host->add_hook(PluginHost::HOOK_SEND_MAIL, $this);
	}

	function hook_send_mail(Mailer $mailer, $params) {
		if (Config::get(self::SMTP_SERVER)) {

			$phpmailer = new \PHPMailer\PHPMailer\PHPMailer();

			$phpmailer->isSMTP();

			$pair = explode(":", Config::get(self::SMTP_SERVER), 2);
			$phpmailer->Host = $pair[0];
			$phpmailer->Port = (int)$pair[1];
			$phpmailer->CharSet = "UTF-8";

			if (!$phpmailer->Port) $phpmailer->Port = 25;

			if (Config::get(self::SMTP_LOGIN)) {
				$phpmailer->SMTPAuth = true;
				$phpmailer->Username = Config::get(self::SMTP_LOGIN);
				$phpmailer->Password = Config::get(self::SMTP_PASSWORD);
			}

			if (Config::get(self::SMTP_SECURE)) {
				$phpmailer->SMTPSecure = Config::get(self::SMTP_SECURE);
			} else {
				$phpmailer->SMTPAutoTLS = false;
			}

			if (Config::get(self::SMTP_SKIP_CERT_CHECKS)) {
				$phpmailer->SMTPOptions = array(
				    'ssl' => array(
				        'verify_peer' => false,
				        'verify_peer_name' => false,
				        'allow_self_signed' => true
				    )
				);
			} else if (Config::get(self::SMTP_CA_FILE)) {
				$phpmailer->SMTPOptions = array(
				    'ssl' => array(
                        'cafile' => Config::get(self::SMTP_CA_FILE)
				    )
				);
            }

			$from_name = !empty($params["from_name"]) ? $params["from_name"] :
				Config::get(Config::SMTP_FROM_NAME);

			$from_address = !empty($params["from_address"]) ? $params["from_address"] :
				Config::get(Config::SMTP_FROM_ADDRESS);

			$phpmailer->setFrom($from_address, $from_name);
			$phpmailer->addAddress($params["to_address"], ($params["to_name"] ?? ""));
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
