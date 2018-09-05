<?php
header("Content-Type: text/html; charset=utf-8");

//require_once "config.php";
require_once "include/TemplateHelper.php";
require_once "include/Internationalize.php";
require_once "include/ProjectOrganizer.php";

// Internationalization stuff.
$inter = new Internationalize();
if (isset($_GET["lang"])) {
	$inter->set_language($_GET["lang"]);
}
require_once $inter->get_lang_include($_SERVER["PHP_SELF"]);

// infolio project organizer.
$organizer = new ProjectOrganizer();
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
		<br>
	</div>

	<!-- Main area. -->
	<div class="container">
		<!-- Projects list -->
		<div class="project-list">
			<ul>
				<li>
					<b>Category 1</b>
					<ul>
						<li>Project 1</li>
						<li>Project 2</li>
						<li>Project 3</li>
						<li>Project 4</li>
					</ul>
				</li>
				<li>
					<b>Category 2</b>
					<ul>
						<li>Project 1</li>
						<li>Project 2</li>
						<li>Project 3</li>
						<li>Project 4</li>
					</ul>
				</li>
				<li>
					<b>Category 3</b>
					<ul>
						<li>Project 1</li>
						<li>Project 2</li>
						<li>Project 3</li>
						<li>Project 4</li>
					</ul>
				</li>		
			</ul>
			<?php //echo Template::ProjectList("Testing", $inter->cl); ?>
		</div>

		<!-- Projects -->
		<?php
			foreach ($organizer->project_list as $project) {
				echo Template::Project($organizer, $project->id);
			}
		?>
	</div>

	<!-- Footer -->
	<div class="footer-container">
		<br>
		<?php echo Template::Footer(); ?>
	</div>

	<script type="text/javascript">
		$(document).on('click', '[data-toggle="lightbox"]', function(event) {
			event.preventDefault();
			$(this).ekkoLightbox({
				wrapping: false,
				showArrows: true
			});
		});
	</script>
</body>
</html>
