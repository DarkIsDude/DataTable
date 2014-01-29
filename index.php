<html>
	<head>
		<title>Test DataTable</title>
		
		<link href="./DataTable/css/bootstrap.css" rel="stylesheet">
		<link href="./DataTable/css/bootstrap-theme.css" rel="stylesheet">
		<link href="./DataTable/css/dataTables.css" rel="stylesheet">
	</head>
	<body>
		<div class="container">
			<div class="row">
				<h1>Test du PFE avec affichage en bootstrap</h1>
			</div>
			<div class="row">
				<?php
					require_once('./DataTable/php/include.php');
				
					$table = new DataTable\php\Table();
					$table->init("user");
					$table->addColIndex('N', 'id', true, false);
					$table->addCol('Login', 'login');
					$table->addCol("Mail", "mail");
					$table->addCol("Date de naissance", "naissance");
					$table->addCol("est Grand", "grand");
					$table->addCol("Peut se connecter", "allowed");
					$table->addColLinked("Groupe", "user_group", "user_group", "id", "label");
					$table->show("./DataTable/");
				?>
			</div>
		</div>
	
		<script type="text/javascript" src="./DataTable/js/jquery.js"></script>
		<script type="text/javascript" src="./DataTable/js/bootstrap.js"></script>
		<script type="text/javascript" src="./DataTable/js/dataTables.js"></script>
		<script type="text/javascript" src="./DataTable/js/table.js"></script>
	</body>

</html>