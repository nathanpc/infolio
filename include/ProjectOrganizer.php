<?php
/**
 * ProjectOrganizer.php
 * A helper utility to organize and manage the projects for infolio.
 *
 * @author Nathan Campos <nathan@innoveworkshop.com>
 */

require_once "config.php";
require_once "include/HTML_Builder.php";

class Project {
	const IMAGE_FILTER = '/\.(png|jpg|jpeg|bmp|gif)$/';
	const IMAGE_CATEGORIES = array("main", "board", "schematic", "misc");

	public $id;
	public $root;
	public $lang;
	private $def_json;

	public $name;
	public $category;
	public $brief;
	public $description;
	public $images;

	/**
	 * Creates a project object.
	 *
	 * @param string $id   Project ID.
	 * @param string $root Project root directory.
	 */
	function __construct($id, $lang, $root = NULL) {
		$this->id = $id;
		$this->lang = $lang;

		// Populate the class if the project exists.
		if (!is_null($root)) {
			$this->root = "$root/$id";

			$this->parse_definition();
			$this->description = $this->get_main_description();

			foreach (self::IMAGE_CATEGORIES as $cat) {
				$this->images[$cat] = $this->parse_images($cat);
			}
		}
	}

	/**
	 * Builds the highlight line HTML.
	 *
	 * @return string Highlight line HTML.
	 */
	public function highlight_line() {
		$str = "";

		foreach ($this->def_json["highlights"] as $provider => $val) {
			$hl = array(
				"type" => $provider,
				"icon" => "",
				"url" => "");

			// Create a simple definition for the highlight based on the provider.
			if ($provider == "github") {
				$hl["icon"] = "fab fa-github";
				$hl["url"] = "http://github.com/$val";
			} else if ($provider == "tindie") {
				$hl["icon"] = "fas fa-shopping-cart";
				$hl["url"] = "https://www.tindie.com/products/$val/";
			} else if ($provider == "news") {
				$hl["icon"] = "far fa-newspaper";
				$hl["url"] = $val;
			}

			$str .= Builder::root("a", array("href" => $hl["url"]),
				Builder::child("i", array("class" => $hl["icon"]), "")
			)->saveHTML();
		}

		return $str;
	}
	
	/**
	 * Builds a image carousel HTML.
	 *
	 * @param  mixed  $category Image category.
	 * @return string           Image carousel HTML.
	 */
	public function image_carousel($category) {
		$str = "";
		$cols = array();
		$image_list = NULL;

		if (is_string($category)) {
			$image_list = $this->images[$category];
		} else if (is_array($category)) {
			$image_list = $this->images[$category[0]];

			for ($i = 1; $i < count($category); $i++) {
				$image_list = array_merge($image_list, $this->images[$category[$i]]);
			}
		}

		foreach ($image_list as $idx => $image) {
			array_push($cols, Builder::child("div", array("class" => "col"),
				Builder::child("a", array("href" => $image, "data-toggle" => "lightbox", "data-gallery" => implode("-", array($this->id, implode("-", (array)$category)))),
					Builder::child("img", array("src" => $image, "class" => "img-fluid img-thumbnail"))
				)
			));

			if (($idx + 1) % 3 == 0) {
				$str .= Builder::root("div", array("class" => "row"), $cols)->saveHTML();
				$str .= "<br>";
				$cols = array();
			}
		}

		if (count($cols) > 0) {
			$str .= Builder::root("div", array("class" => "row"), $cols)->saveHTML();
		}

		return $str;
	}
	
	/**
	 * Builds the links list HTML.
	 *
	 * @return string Links list HTML.
	 */
	public function links_list() {
		$str = "";

		foreach ($this->def_json["links"] as $link) {
			$str .= Builder::root("li", NULL,
				Builder::child("a", array("href" => $link["url"]), $link["title"])
			)->saveHTML();
		}

		return $str;
	}

	/**
	 * Parses the definition file and populates the class with the contents.
	 */
	private function parse_definition() {
		$project = json_decode(file_get_contents($this->root . "/project." .
			$this->lang . ".json"), true);
		$this->def_json = $project;
			
		$this->name = $project["name"];
		$this->brief = $project["brief"];
		$this->category = $project["category"];
	}

	/**
	 * Parses the images for a given category.
	 *
	 * @param  string $category Image category.
	 * @return array            List of relative image paths.
	 */
	private function parse_images($category) {
		$image_list = array();
		$relpath = "images/$category";

		foreach (glob($this->root . "/$relpath/*") as $image) {
			if (preg_match(self::IMAGE_FILTER, $image)) {
				// We got a valid image, now lets make it into a relative path.
				preg_match("/(projects\/" . $this->id ."\/images.+)/", $image, $matches);
				array_push($image_list, $matches[1]);
			}
		}

		return $image_list;
	}

	/**
	 * Gets the main project description.
	 *
	 * @return string HTML description of the project.
	 */
	private function get_main_description() {
		return utf8_encode(file_get_contents($this->root . "/description." .
			$this->lang . ".html"));
	}
}

class ProjectOrganizer {
	private $project_dir;
	public $project_list;
	public $categories;

	/**
	 * Constructs the project organizer class.
	 *
	 * @param string $lang Language code.
	 */
	function __construct($lang) {
		// Set projects root directory.
		$this->project_dir = $_SERVER["DOCUMENT_ROOT"] . Config::WEBSITE_ROOT . "/projects";

		// Grab the project directories and populate with project objects.
		$this->project_list = array();
		$this->categories = array();
		foreach (glob($this->project_dir . '/*', GLOB_ONLYDIR) as $project) {
			if (!preg_match('/\.ignore$/', $project)) {
				$id = basename($project);
				$this->project_list[$id] = new Project($id, $lang, $this->project_dir);

				if (!in_array($this->project_list[$id]->category, $this->categories)) {
					array_push($this->categories, $this->project_list[$id]->category);
				}
			}
		}
	}

	/**
	 * Retrieves all the projects contained in that specific category.
	 *
	 * @param  string $cat Project category.
	 * @return array       Projects inside that category.
	 */
	public function in_category($cat) {
		$list = array();

		foreach ($this->project_list as $project) {
			if ($project->category == $cat) {
				array_push($list, $project);
			}
		}

		return $list;
	}
}

?>

