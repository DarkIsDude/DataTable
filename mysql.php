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
				<h1>Les utilisateurs</h1>
			</div>
			<div class="row">
				<?php
					require_once('./DataTable/php/include.php');
					
					$table = new DataTable\php\Table();
					$table->init("user");
					$table->canExtension(true);
					$table->setSort(DataTable\php\Table::$SORT_NATURAL);
					$table->addAllCols();
					$table->addColHidden("Bloqué", "locked", TRUE);
					$table->addColLinked("Ville", "city_id", "city", "id", "*cp* - *name*");
					$table->show("./DataTable/");
				?>
			</div>
			<div class="row">
				<h1>Les groupes d'utilisateurs</h1>
			</div>
			<div class="row">
				<?php
					$table = new DataTable\php\Table();
					$table->init("user_group");
					$table->addColIndex('N', 'id', true, false);
					$table->addCol("Nom", "name");
					$table->addCol("Rôles", "roles");
					$table->show("./DataTable/");
				?>
			</div>
			<div class="row">
				<h1>Les associations</h1>
			</div>
			<div class="row">
				<?php
					$table = new DataTable\php\Table();
					$table->init("user_user_group");
					$table->addColIndexLinked("Utilisateur", "user_id", "user", "id", "*username* - *expired*");
					$table->getCol("user_id")->addFilter("id", "<> '1'");
					$table->addColIndexLinked("Group", "group_id", "user_group", "id", "*name*");
					$table->show("./DataTable/");
				?>
			</div>
			<div class="row">
				<h1>Les villes</h1>
			</div>
			<div class="row">
				<?php
					$table = new DataTable\php\Table();
					$table->init("city");
					$table->setSort(DataTable\php\Table::$SORT_NATURAL);
					$table->addAllCols();
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