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
				<h1>Test de DataTable</h1>
			</div>
			<div class="row">
				<?php
					require_once('./DataTable/php/include.php');
				
					$table = new DataTable\php\Table();
					$table->init("user_user_group");
					
					$table->addColIndexLinked("Utilisateur", "user_id", "studio_user", "id", "username");
					$table->addColIndexLinked("Groupe", "group_id", "user_group", "id", "name");
					
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