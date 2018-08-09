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
	<div class="container navbar-container">
		<?php echo Template::Navbar(TEXT_NAVBAR_HOME, $inter->cl); ?>
	</div>

	<!-- Main area. -->
	<br>

	<div class="container">
		<div class="row">
			<!-- Projects list -->
			<div class="col-lg-4">
				<div class="project-container">
					<div class="project-cat-sep">
						<h4>Category</h4>
					</div>

					<ul class="project-list">
						<?php for ($i = 0; $i < 5; $i++) { ?>
						<li class="project-item">
							<div class="card">
								<img class="card-img-top" src="images/products/portastation/sq_base_unit.jpg" alt="Card image cap">
								<div class="card-body">
									<h4 class="card-title">Card title</h4>
									<p class="card-text">Some quick example text to build on the card title and make up the bulk of the card's content.</p>
								</div>
							</div>
						</li>
						<?php } ?>
					</ul>
				</div>
			</div>

			<!-- Project information panel -->
			<div class="col-lg-8">
				<div class="project-info-panel">
					<h1>Hello!</h1>
				</div>
			</div>
		</div>
	</div>

	<!-- Footer -->
	<br>
	<?php echo Template::Footer(); ?>
</body>
</html>
