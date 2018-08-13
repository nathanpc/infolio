<?php

ini_set("display_errors", 1);
ini_set("log_errors", 1);
ini_set("log_errors_max_len", 0);
ini_set("display_startup_errors", 1);
error_reporting(-1);
ini_set("error_log", Config::LOG_PATH . "php.log");

class Config {
	// Paths
	const WEBSITE_ROOT  = "";
	const LOG_PATH      = "logs/";

	// Misc.
	const REGEX_PRODUCT_ID = "/[^a-zA-Z0-9 -_]/";
	const REF_TIMESTAMP = "YmdHis";

	// Development stuff.
	const DEVELOPMENT = TRUE;
}

?>
