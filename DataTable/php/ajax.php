<?php

require_once('./include.php');

$code["success"] = false;
$code["message"] = "";

$serialize = false;

if (isset($_POST['id']) && isset($_POST['function'])) {
	$path = '../temp/' . $_POST['id'] . '.temp';
	
	if (file_exists($path)) {
		$table = unserialize(file_get_contents($path));
		
		if ($table instanceof DataTable\php\Table) {
			switch ($_POST['function']) {
				case 'update':
					if (isset($_POST['name']) && isset($_POST['index']) && isset($_POST['oldValue']) && isset($_POST['newValue'])) {
						$code = $table->updateData($_POST['name'], $_POST['index'], $_POST['oldValue'], $_POST['newValue']);
						$serialize = true;
					}
					else {
						$code["success"] = false;
						$code["message"] = $table->messages["eMissingPatameters"];
					}
					break;
				case 'delete':
					if (isset($_POST['index'])) {
						$code = $table->deleteData($_POST['index']);
						$serialize = true;
					}
					else {
						$code["success"] = false;
						$code["message"] = $table->messages["eMissingPatameters"];
					}
					break;
				case 'create':
					if (isset($_POST['json'])) {
						$code = $table->createData($_POST['json']);
						$serialize = true;
					}
					else {
						$code["success"] = false;
						$code["message"] = $table->messages["eMissingPatameters"];
					}
					break;
				case 'removeSerialized':
					$code = $table->removeSerializedTable('../');
					break;
				default:
					$code["success"] = false;
					$code["message"] = $table->messages["eFunctionUnkown"];
					break;
			}
			
			if ($serialize)
				$table->serializeTable(true, "../");
		}
		else {
			$code["success"] = false;
			$code["message"] = $table->messages["eUnserialize"];
		}
	}
    else {
        $code["success"] = false;
        $code["message"] = "File not found. Maybe a right’s problem";
    }
}

print json_encode($code);

?>