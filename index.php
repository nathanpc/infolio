<?php
header("Content-Type: text/html; charset=utf-8");

//require_once "config.php";
require_once "include/TemplateHelper.php";
require_once "include/Internationalize.php";

$inter = new Internationalize();
if (isset($_GET["lang"])) {
	$inter->set_language($_GET["lang"]);
}
require_once $inter->get_lang_include($_SERVER["PHP_SELF"]);
?>
<!DOCTYPE html>
<html lang="<?= $inter->cl ?>">
<head>
<?php
	echo Template::OGHeader($_SERVER["PHP_SELF"], $inter->cl);
	echo "<script type=\"application/ld+json\">\n" . Template::CompanyStructuredData($inter->cl) . "\n</script>";
?>
</head>
<body>
	<!-- Main navbar -->
	<?php echo Template::Navbar(TEXT_NAVBAR_HOME, TEXT_NAVBAR_PRODUCTS, TEXT_NAVBAR_ABOUT, TEXT_NAVBAR_CONTACT, $inter->cl); ?>

	<!-- Highlight banners. -->
	<br>

	<!-- Footer -->
	<br>
	<?php echo Template::Footer(); ?>
</body>
</html>
