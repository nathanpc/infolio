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
		<div class="project-container">
			<hr>
			<h1>PortaStation</h1>
			<p>Uma estação de solda portátil, única no mercado, capaz de ser utilizada como uma estação primária em uma bancada de eletrônica, em um ambiente industrial de produção ou ser levada a campo para realização de instalações, reparos e manutenção.</p>

			<div class="project-image-carousel container">
				<div class="row">
					<div class="col">
						<img src="images/projects/power12/show.jpg" class="img-fluid img-thumbnail">
					</div>
					<div class="col">
						<img src="images/projects/power12/open.jpg" class="img-fluid img-thumbnail">
					</div>
					<div class="col">
						<img src="images/projects/power12/open-connected.jpg" class="img-fluid img-thumbnail">
					</div>
				</div>

				<br>
			</div>

			<p>Um amplificador stereo compacto de 6W por canal completamente discreto, ou seja, todo o circuito foi feito sem utilizar nenhum tipo de circuito integrado. Este foi o meu segundo amplificador e foi desenvolvido com o intuito de aprender mais sobre eletrônica analógica e amplificadores operacionais discretos. Este projeto é utilizado diariamente desde 2014 como a principal saída de áudio do meu computador e em todos esses anos nunca teve nenhum tipo de falha e continua tendo um som extremamente livre de distorções com um THD medido de 0.03% em 2016.</p>

			<b>PORTÁTIL E À BATERIA</b>
			<p>Além de ser pequena e extremamente leve pesando apenas 340g, a estação difere das outras no mercado por ser capaz de operar remotamente utilizando uma bateria, perfeita para ser utilizada em campo.</p>

			<b>INTERFACE MODERNA E INTUITIVA</b>
			<p>Muitas estações de solda no mercado possuem apenas um knob para ajuste da temperatura de forma analógica, onde a seleção é impressa ao redor knob, ou nas versões digitais possuem um visor de LED de 7 segmentos e botões para aumentar ou diminuir a temperatura. Porém a nossa estação utiliza um display gráfico capaz de apresentar a temperatura de solda, além de muitas outras informações importantes, também possui um menu de configuração para personalização.</p>

			<div class="schematic-container">
				<h4>Schematic and Board</h4>

				<div class="project-image-carousel container">
					<div class="row">
						<div class="col">
							<img src="images/projects/power12/schematic.png" class="img-fluid img-thumbnail">
						</div>
						<div class="col">
							<img src="images/projects/power12/board.png" class="img-fluid img-thumbnail">
						</div>
					</div>

					<br>
				</div>
			</div>
		</div>
	</div>

	<!-- Footer -->
	<div class="footer-container">
		<br>
		<?php echo Template::Footer(); ?>
	</div>
</body>
</html>
