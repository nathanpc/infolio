<?php
require_once "config.php";

class Internationalize {
	public $cl;

	/**
	 * Internationalize class constructor. This also checks if the current language cookie is set, if not sets the appropriate one.
	 */
	function __construct() {
		if (!isset($_COOKIE["lang"])) {
			// No cookie set, check for the country of the IP.
			$country = $this->fetch_country_code($_SERVER["REMOTE_ADDR"]);

			if (($country == "BR") or ($country == "PT")) {
				// Looks like it's from a portuguese-speaking country.
				setcookie("lang", "pt", time() + 31536000, "/");
				$this->cl = "pt";
			} else {
				// Anything else.
				setcookie("lang", "en", time() + 31536000, "/");
				$this->cl = "en";
			}
		} else {
			$this->cl = $_COOKIE["lang"];
		}
	}

	/**
	 * Sets the current language cookie.
	 *
	 * @param string $lang Language ID.
	 */
	public function set_language($lang) {
		// Convert BR to PT.
		if ($lang == "br") {
			$lang = "pt";
		}

		setcookie("lang", $lang, time() + 31536000, "/");
		$this->cl = $lang;
	}

	/**
	 * Gets the path of the include language definition file.
	 *
	 * @param  string $script_name Filename of the current script.
	 * @return string Path to the language definition include file.
	 */
	public function get_lang_include($script_name) {
		return "lang/" . $this->cl . "/" . basename($script_name);
	}

	/**
	 * Fetches the country code from freegeoip.net
	 *
	 * @param  string $ip Any identifier, could even be a domain.
	 * @return string     Country code.
	 */
	private function fetch_country_code($ip) {
		$content = @file_get_contents("http://freegeoip.net/json/" + $ip);

		if ($content === FALSE) {
			// Looks like we had a problem with the API.
			return "ERROR";
		} else {
			$json = json_decode($content, TRUE);
			return $json["country_code"];
		}
	}
}

?>
