<?php
require_once "config.php";
require_once "lib/Logger.php";

class Database {
	private $pdo;
	private $log;

	function __construct($page, $db_path = Config::DATABASE_PATH) {
		$this->auth = $auth;
		$this->log = new Log($page, $auth->user());

		// Connects to the database.
		$this->pdo = new PDO("sqlite:" . $db_path);
	}

	/**
	 * Checks if a component exists.
	 *
	 * @param string $mpn Part number
	 * @param string $octopart_uid Octopart UID
	 * @return boolean False if it doesn't exist, PDO->Query if it does
	 */
	public function check_exists($mpn, $octopart_uid) {
		$sql = $this->pdo->prepare("SELECT * FROM Inventory WHERE mpn = :mpn AND octopart_uid = :octopart_uid");
		$success = $sql->execute(array(
			":mpn" => $mpn,
			":octopart_uid" => $octopart_uid));

		if (!$success) {
			$this->log->post(Log::LVL_ERROR, __METHOD__ . ":" . __LINE__ . " Couldn't locate the component.");
			throw new Exception("Couldn't locate the component.");
		}

		if ($part = $sql->fetch()) {
			// The component exists!
			return $part;
		}

		return false;
	}

	/**
	 * Sums up the quantity of all items.
	 *
	 * @param PDO->Query Parts array
	 * @return integer Sum of the quantities
	 */
	public static function sum_quantity($parts) {
		$sum = 0;

		foreach ($parts as $part) {
			$sum += $part["quantity"];
		}

		return $sum;
	}

	/**
	 * Check if a string is "null" and make sure it becomes NULL.
	 *
	 * @param string $str String to nullify
	 *
	 * @return NULL or the original string
	 */
	public static function nullify($str) {
		if ($str == "null") {
			return NULL;
		}

		return $str;
	}

	/**
	 * Strips a string completely from anything that isn't a alphanumeric character.
	 *
	 * @param string $str String to be stripped
	 *
	 * @return string Stripped string.
	 */
	private function strip_str($str) {
		return preg_replace("/[^a-zA-Z0-9]+/", "", $str);
	}
}
?>
