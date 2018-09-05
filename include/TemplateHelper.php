<?php
require_once "config.php";
require_once "include/HTML_Builder.php";

class TemplateDocument {
	private $original;
	private $document;

	/**
	 * Template document constructor.
	 *
	 * @param  string $path         Path to the template, NULL if you want to go create your own.
	 * @param  string $initial_text Initial content if you're not loading from a file.
	 * @return TemplateDocument     The template document object.
	 */
	function __construct($path, $initial_text = "") {
		if (is_null($path)) {
			$this->original = $initial_text;
			$this->document = $initial_text;
		} else if (file_exists($path)) {
			$this->original = file_get_contents($path);
			$this->document = $this->original;
		} else {
			throw new Exception("Template not found at \"" . $path . "\"");
		}
	}

	/**
	 * Replaces every occurence of a key inside a document with a string.
	 *
	 * @param  string $key      Key name that will be replaced.
	 * @param  string $str      String that will be put in place.
	 */
	public function replace($key, $str) {
		$this->document = str_replace("%$key%", $str, $this->document);
	}

	/**
	 * Converts the document back to a string.
	 */
	public function __toString() {
		return $this->document;
	}
}

class Template {
	const PROTOCOL = "http://";

	/**
	 * Prepends the correct language code to URLs in the page.
	 *
	 * @param  mixed   $document Template document object or string.
	 * @param  string  $lang     Language code.
	 * @param  boolean $doc_str  True if $document is a string.
	 * @return mixed             Modified template document object or string.
	 */
	private static function prepend_lang_url($document, $lang, $doc_str = false) {
		if ($lang == "pt") {
			$lang = "br";
		}

		if ($doc_str) {
			return str_replace("%lang%", $lang, $document);
		}

		return $document->replace("lang", $lang);
	}

	/**
	 * Builds the hreflang tags for SEO bullshit.
	 * @param  string $url Base URL with %lang% to be substituted.
	 * @return string      String of the tags to be appended to the HTML file.
	 */
	private static function build_hreflang_tags($url) {
		$tags = "";
		$langs = array("en", "pt", "br");

		foreach ($langs as $alt_lang) {
			// Separating hreflanf and lang in the case of brazilian portuguese.
			$hreflang = $alt_lang;
			if ($alt_lang == "br") {
				$hreflang = "pt-BR";
			}

			$tags .= "<link rel=\"alternate\" hreflang=\"$hreflang\" href=\"" . str_replace("%lang%", $alt_lang, $url) . "\" />\n";
		}

		return $tags;
	}

	/**
	 * Creates the JSON-LD required for the organization Structured Data stuff.
	 * @param  string $lang Language code.
	 * @return string Structured Data JSON-LD.
	 */
	public static function CompanyStructuredData($lang) {
		$og = json_decode(file_get_contents($_SERVER["DOCUMENT_ROOT"] . Config::WEBSITE_ROOT . "/opengraph.json"), true);
		$json = array(
			"@context" => "http://schema.org",
			"@type" => "Organization",
			"url" => $og["root_url"],
			"logo" => $og["root_url"] . $og["default_image"],
			"contactPoint" => array(
				array(
					"@type" => "ContactPoint",
					"telephone" => "+55-27-98138-7777",
					"contactType" => "customer service"
				)
			),
			"sameAs" => array(
				"http://facebook.com/innoveworkshop",
				"http://twitter.com/innoveworkshop",
				"http://linkedin.com/company/innoveworkshop",
				"http://instagram.com/innoveworkshop",
				"http://foursquare.com/v/innove-workshop-company/5a04842a446ea61ee8492828"
			)
		);

		return json_encode($json, JSON_PRETTY_PRINT);
	}

	/**
	 * Creates the JSON-LD required for the product Structured Data stuff.
	 * @param  string $name Product name ID (without the name_ prefix).
	 * @param  string $lang Language code.
	 * @return string Structured Data JSON-LD.
	 */
	public static function ProductStructuredData($name, $lang) {
		$og = json_decode(file_get_contents($_SERVER["DOCUMENT_ROOT"] . Config::WEBSITE_ROOT . "/opengraph.json"), true);
		$page = $og["pages"]["product.php"]["subpages"]["name_" . preg_replace(Config::REGEX_PRODUCT_ID, "", $name)];
		$json = array(
			"@context" => "http://schema.org",
			"@type" => "Product",
			"name" => $page["meta"]["name"],
			"image" => self::PROTOCOL . $_SERVER["SERVER_NAME"] . Config::WEBSITE_ROOT . $page["image"],
			"description" => $page["description"][$lang],
			"mpn" => $page["meta"]["mpn"],
			"brand" => array(
				"@type" => "Thing",
				"name" => "Innove Workshop Company"
			)
		);

		return json_encode($json, JSON_PRETTY_PRINT);
	}

	/**
	 * Open Graph header template.
	 *
	 * @param  string $path    Page path.
	 * @param  string $lang    Language for internationalization.
	 * @param  string $subpage Subpage name.
	 * @return string          Page header.
	 */
	public static function OGHeader($path, $lang, $subpage = null) {
		$document = new TemplateDocument($_SERVER["DOCUMENT_ROOT"] . Config::WEBSITE_ROOT . "/templates/header.html");
		$og = json_decode(file_get_contents($_SERVER["DOCUMENT_ROOT"] . Config::WEBSITE_ROOT . "/opengraph.json"), true);
		$page = $og["pages"][basename($path)];
		$param_sep = "?";

		// Handle the Google Analytics stuff.
		if (Config::DEVELOPMENT) {
			$document->replace("ga_open", "<!--");
			$document->replace("ga_close", "-->");
		} else {
			$document->replace("ga_open", "");
			$document->replace("ga_close", "");
		}

		// Looks like we have a subpage.
		if (!is_null($subpage)) {
			$subpage = preg_replace(Config::REGEX_PRODUCT_ID, "", $subpage);
			$page = $page["subpages"][$subpage];
			$param_sep = "&";
		}

		// Check if there is a page title.
		if (is_null($page["title"][$lang])) {
			$title = $og["default_title"];
			$document->replace("title", $title);
			$document->replace("og_title", $title);
		} else {
			$title = $page["title"][$lang] . " - " . $og["default_title"];
			$document->replace("title", $title);

			if (strlen($title) > 50) {
				// Reduce the size of the title so it will show up in WhatsApp.
				$title = substr($title, 0, 47) . "...";
			}

			$document->replace("og_title", $page["title"][$lang]);
		}

		// Open Graph image.
		if (!is_null($page["image"])) {
			$document->replace("og_image", $og["root_url"] . $page["image"]);
		} else {
			$document->replace("og_image", $og["root_url"] . $og["default_image"]);
		}

		$document->replace("description", $page["description"][$lang]);
		$document->replace("og_lang", $lang);
		$document->replace("og_url", $og["root_url"] . self::prepend_lang_url("/%lang%", $lang, true) . $page["url"]);
		$document->replace("hreflang_tags", self::build_hreflang_tags($og["root_url"] . "/%lang%" . $page["url"]));
		$document->replace("web_root", self::PROTOCOL . $_SERVER["SERVER_NAME"] . Config::WEBSITE_ROOT);

		return $document;
	}

	/**
	 * Navbar template
	 *
	 * @param  string $home     Home label.
	 * @param  string $lang     Current language.
	 * @return string           Nice NavBar for the page.
	 */
	public static function Navbar($home, $lang) {
		$document = new TemplateDocument($_SERVER["DOCUMENT_ROOT"] . Config::WEBSITE_ROOT . "/templates/navbar.html");

		$document->replace("home", $home);

		// Set the language for the URLs.
		self::prepend_lang_url($document, $lang);

		// Build the string for the language change link URL.
		$url_arr = array_values(array_filter(explode("/", $_SERVER["REQUEST_URI"])));

		// Pop the initial path off the array if we are in a development environment.
		if (Config::DEVELOPMENT) {
			array_shift($url_arr);
		}

		// Checking if the first url item is a language selection and remove it.
		if (count($url_arr) > 0) {
			if (preg_match("/(pt|br|en)/", $url_arr[0])) {
				array_shift($url_arr);
			}
		}

		// Put the language selection string into the path.
		array_unshift($url_arr, "%lang_sel%");

		$lang_url = implode("/", $url_arr);
		$document->replace("lang_sel_en", "%web_root%/" . str_replace("%lang_sel%", "en", $lang_url));
		$document->replace("lang_sel_br", "%web_root%/" . str_replace("%lang_sel%", "br", $lang_url));
		$document->replace("web_root", self::PROTOCOL . $_SERVER["SERVER_NAME"] . Config::WEBSITE_ROOT);

		return $document;
	}

	/**
	 * Project container template.
	 *
	 * @param  ProjectOrganizer $organizer Project organizer object.
	 * @param  string           $id        Project name.
	 * @return string                      Project container.
	 */
	public static function Project($organizer, $id) {
		// Check if the requested project exists.
		if (!isset($organizer->project_list[$id])) {
			trigger_error("Project \"$id\" not found.", E_USER_NOTICE);
			return "<br>";
		}

		// Load stuff.
		$document = new TemplateDocument($_SERVER["DOCUMENT_ROOT"] . Config::WEBSITE_ROOT . "/templates/project-container.html");
		$project = $organizer->project_list[$id];

		// Simple replaces.
		$document->replace("title", $project->name);
		$document->replace("brief", $project->brief);
		$document->replace("description", $project->description);
		$document->replace("highlight_line", $project->highlight_line());

		$document->replace("image_carousel",<<<'EOT'
		<div class="row">
			<div class="col">
				<a href="images/projects/power12/show.jpg" data-toggle="lightbox" data-gallery="portastation-images">
					<img src="images/projects/power12/show.jpg" class="img-fluid img-thumbnail">
				</a>
			</div>
			<div class="col">
				<a href="images/projects/power12/open.jpg" data-toggle="lightbox" data-gallery="portastation-images">
					<img src="images/projects/power12/open.jpg" class="img-fluid img-thumbnail">
				</a>
			</div>
			<div class="col">
				<a href="images/projects/power12/open-connected.jpg" data-toggle="lightbox" data-gallery="portastation-images">
					<img src="images/projects/power12/open-connected.jpg" class="img-fluid img-thumbnail">
				</a>
			</div>
		</div>
EOT
		);

		
		$document->replace("schbrd_carousel", <<<'EOT'
			<div class="row">
				<div class="col">
					<a href="images/projects/power12/schematic.png" data-toggle="lightbox" data-gallery="portastation-schbrd">
						<img src="images/projects/power12/schematic.png" class="img-fluid img-thumbnail">
					</a>
				</div>
				<div class="col">
					<a href="images/projects/power12/board.png" data-toggle="lightbox" data-gallery="portastation-schbrd">
						<img src="images/projects/power12/board.png" class="img-fluid img-thumbnail">
					</a>
				</div>
			</div>
EOT
		);

		$document->replace("links", $project->links_list());

		//self::prepend_lang_url($document, $lang);
		$document->replace("web_root", self::PROTOCOL . $_SERVER["SERVER_NAME"] . Config::WEBSITE_ROOT);
		return $document;
	}

	/**
	 * Creates a share button thingy for the Content Titles.
	 *
	 * @return string Share button thingy.
	 */
	public static function ShareButtons() {
		return "<div class=\"sharing-area\">
			<a href=\"#\" class=\"share-button\" role=\"button\" alt=\"Share\" onclick=\"return false;\"><i class=\"fas fa-share-alt\"></i></a>
			<span class=\"share-buttons\">
				<a id=\"facebook-share\" href=\"https://www.facebook.com/sharer/sharer.php?u=\"><i class=\"fab fa-facebook-f facebook\"></i></a>
				<a id=\"twitter-share\" href=\"https://twitter.com/home?status=\"><i class=\"fab fa-twitter twitter\"></i></a>
				<a id=\"linkedin-share\" href=\"https://www.linkedin.com/shareArticle?mini=true&url=\"><i class=\"fab fa-linkedin linkedin\"></i></a>
				<a id=\"gplus-share\" href=\"https://plus.google.com/share?url=\"><i class=\"fab fa-google-plus google-plus\"></i></a>
				<a id=\"pinterest-share\" href=\"https://pinterest.com/pin/create/button/?url=\"><i class=\"fab fa-pinterest pinterest\"></i></a>
				<a id=\"whatsapp-share\" href=\"whatsapp://send?text=\" data-action=\"share/whatsapp/share\"><i class=\"fab fa-whatsapp whatsapp\"></i></a>
				<a id=\"email-share\" href=\"mailto:?\"><i class=\"fas fa-envelope email-icon\"></i></a>
			</span>
		</div>
		<script>populate_share_buttons();</script>";
	}

	/**
	 * Sitemap page template.
	 *
	 * @param  array  $list Link list.
	 * @return string       Sitemap page.
	 */
	public static function SitemapPage($list) {
		$document = new TemplateDocument($_SERVER["DOCUMENT_ROOT"] . Config::WEBSITE_ROOT . "/templates/sitemap.html");

		$document->replace("navbar", self::Navbar("Home", "Products", "About", "Contact", "en"));
		$document->replace("share_buttons", self::ShareButtons());
		$document->replace("footer", self::Footer());
		$document->replace("list", $list);
		$document->replace("web_root", self::PROTOCOL . $_SERVER["SERVER_NAME"] . Config::WEBSITE_ROOT);

		return $document;
	}

	/**
	 * Footer template.
	 *
	 * @return string Page footer
	 */
	public static function Footer() {
		$document = new TemplateDocument($_SERVER["DOCUMENT_ROOT"] . Config::WEBSITE_ROOT . "/templates/footer.html");

		$document->replace("year", date("Y"));
		$document->replace("web_root", self::PROTOCOL . $_SERVER["SERVER_NAME"] . Config::WEBSITE_ROOT);

		return $document;
	}
}
?>
