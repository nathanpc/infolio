<?php
require_once "config.php";

class TemplateDocument {
	private $original;
	private $document;

	function __construct($path) {
		if (file_exists($path)) {
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
	 * @param  string $products Products label.
	 * @param  string $about    About label.
	 * @param  string $contact  Contact label.
	 * @param  string $lang     Current language.
	 * @return string           Nice NavBar for the page.
	 */
	public static function Navbar($home, $products, $about, $contact, $lang) {
		$document = new TemplateDocument($_SERVER["DOCUMENT_ROOT"] . Config::WEBSITE_ROOT . "/templates/navbar.html");

		$document->replace("home", $home);
		$document->replace("products", $products);
		$document->replace("about", $about);
		$document->replace("contact", $contact);

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
	 * Highlight banner template.
	 *
	 * @param  string  $title      Banner title.
	 * @param  string  $background Background image.
	 * @param  string  $text       Banner text.
	 * @param  string  $lang       Language code.
	 * @param  boolean $product    Is it a product?
	 * @param  string  $moreinfo   More info label.
	 * @param  string  $prod_id    Product ID.
	 * @return string              Highlight banner.
	 */
	public static function HighlightBanner($title, $background, $text, $lang, $product = false, $moreinfo = "", $prod_id = "") {
		$document = new TemplateDocument($_SERVER["DOCUMENT_ROOT"] . Config::WEBSITE_ROOT . "/templates/highlight_banner.html");

		$document->replace("title", $title);
		$document->replace("img", $background);
		$document->replace("text", $text);

		if ($product) {
			$document->replace("product", "product");
			$document->replace("moreinfo", "<p><a role=\"button\" class=\"btn btn-info\" href=\"%web_root%/%lang%/product/" . $prod_id . "\">" . $moreinfo . "</a></p>");
		} else {
			$document->replace("product", "");
			$document->replace("moreinfo", "");
		}

		self::prepend_lang_url($document, $lang);
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
	 * Loads a product page.
	 *
	 * @param  string $name Product name.
	 * @param  string lang  Language of the text.
	 * @return string       Product page.
	 */
	public static function Product($product, $lang) {
		// Make the GET parameter safer.
		$product = preg_replace(Config::REGEX_PRODUCT_ID, "", $product);
		$document = "<h3>In theory you shouldn't see this.</h3>";

		try {
			// Read all the files needed to populate this page.
			$document = new TemplateDocument($_SERVER["DOCUMENT_ROOT"] . Config::WEBSITE_ROOT . "/templates/products/" . $product . ".html");
			$lang_json = json_decode(file_get_contents($_SERVER["DOCUMENT_ROOT"] . Config::WEBSITE_ROOT . "/templates/products/" . $product . ".json"), true);
			$data_json = json_decode(file_get_contents($_SERVER["DOCUMENT_ROOT"] . Config::WEBSITE_ROOT . "/products/" . $product . ".json"), true);
			$docs_json = json_decode(file_get_contents($_SERVER["DOCUMENT_ROOT"] . Config::WEBSITE_ROOT . "/docs/$product/description.json"), true);

			// Enable the PagSeguro sandbox in case we are under development.
			if (Config::DEVELOPMENT) {
				$document->replace("pgsand", "sandbox.");
			} else {
				$document->replace("pgsand", "");
			}

			// Do a bunch of stuff with the prices JSON.
			$price_arr = array();
			$usd_prices = array("starting" => 0, "full" => 0);
			$checkboxes = "";
			$product_grid = "";

			// Create everything related to the product options.
			for ($i = 0; $i < count($data_json["options"]); $i++) {
				$item = $data_json["options"][$i];

				// Populate the options price JSON array for the page script.
				$price_arr[$item["html_id"]] = $item["price"]["BRL"];

				// Calculate the starting price in USD.
				if ($item["starting"]) {
					$usd_prices["starting"] += $item["price"]["USD"];
				}

				// Calculate the fully optioned version.
				if (!$item["optional"]) {
					$usd_prices["full"] += $item["price"]["USD"];
				}

				// Populate the options checkboxes.
				$checkboxes .= "<div class=\"form-check\"><input class=\"form-check-input\" type=\"checkbox\" value=\"" .$item["html_id"] . "\" id=\"opt-" .$item["html_id"] . "\" onchange=\"update_price(" . $data_json["max_installments"] . ")\" checked><label class=\"form-check-label\" for=\"opt-" .$item["html_id"] . "\">" . $item["name"][$lang] . " (" . sprintf("R$ %.2f", $item["price"]["BRL"]) . ")</label></div>";

				// Populate the product grid.
				if ($i % 3 == 0) {
					// Create row container.
					if (($i > 0)) {
						$product_grid .= "</div>";
					}

					$product_grid .= "<div class=\"row\">";
				}

				// Add item to the product grid.
				$product_grid .= "<div class=\"col\"><div class=\"card\"><img class=\"card-img-top\" src=\"%web_root%/images/products/portastation/sq_" . $item["html_id"] . ".jpg\" alt=\"" . $item["name"][$lang] . "\"><div class=\"card-body\"><h5 class=\"card-title\">" . $item["name"][$lang] . "</h5><p class=\"card-text\">" . $item["description"][$lang] . "</p></div></div></div>";
			}
			$product_grid .= "</div>";

			// Populate the documentation section.
			$docs_html = "";
			for ($i = 0; $i < count($docs_json["categories"]); $i++) {
				$category = $docs_json["categories"][$i];
				$docs_html .= "<b>" . $category["name"][$lang] . "</b><ul class=\"list-unstyled\">";

				for ($j = 0; $j < count($category["items"]); $j++) {
					$item = $category["items"][$j];
					$docs_html .= "<li>";

					if ($item["type"] == "application/pdf") {
						$docs_html .= "<span class=\"file-icon pdf-icon\"><i class=\"fas fa-file-pdf\"></i></span>";
					} else if ($item["type"] == "application/zip") {
						$docs_html .= "<span class=\"file-icon zip-icon\"><i class=\"fas fa-file-archive\"></i></span>";
					} else {
						$docs_html .= "<span class=\"file-icon\"><i class=\"fas fa-file\"></i></span>";
					}

					$docs_html .= " <a href=\"%web_root%/docs/$product/" . $item["file"] . "\">" . $item["name"][$lang] . "</a>";

					if ($item["license"] == "CC-BY-NC-SA") {
						$docs_html .= " <a rel=\"license\" href=\"http://creativecommons.org/licenses/by-nc-sa/4.0/\"><img alt=\"Creative Commons License\" style=\"border-width:0\" src=\"https://i.creativecommons.org/l/by-nc-sa/4.0/80x15.png\" /></a>";
					}

					$docs_html .= "</li>";
				}

				$docs_html .= "</ul>";
			}

			// Populate the data from the prices JSON into the page.
			$document->replace("product_name", $product);
			$document->replace("prices_json", json_encode($price_arr));
			$document->replace("opts_brl", $checkboxes);
			$document->replace("starting_usd", sprintf("%.2f", $usd_prices["starting"]));
			$document->replace("fullopt_usd", sprintf("%.2f", $usd_prices["full"]));
			$document->replace("max_installments", $data_json["max_installments"]);
			$document->replace("docs_list", $docs_html);
			$document->replace("product_grid", $product_grid);
			$document->replace("share_buttons", self::ShareButtons());

			// Internationalize the page.
			foreach ($lang_json[$lang] as $key => $value) {
				$document->replace($key, $value);
			}
		} catch (Exception $e) {
			// Looks like the product doesn't exist.
			$document = "<h2 style=\"text-align:center;\">Product not found" . $product . "</h2>";
		}

		$document->replace("web_root", self::PROTOCOL . $_SERVER["SERVER_NAME"] . Config::WEBSITE_ROOT);
		return $document;
	}

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
