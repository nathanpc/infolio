<?php
/**
 * ProjectOrganizer.php
 * A helper utility to organize and manage the projects for infolio.
 *
 * @author Nathan Campos <nathan@innoveworkshop.com>
 */

require_once "config.php";

class ProjectOrganizer {
	private $project_dir;
	private $project_list;

	function __construct() {
		// Set projects root directory.
		$this->project_dir = $_SERVER["DOCUMENT_ROOT"] . Config::WEBSITE_ROOT . "/projects";

		// Grab the project directories.
		$this->project_list = array();
		foreach (glob($this->project_dir . '/*', GLOB_ONLYDIR) as $project) {
			array_push($this->project_list, basename($project));
		}

		print_r($this->project_list);
	}
}

?>

