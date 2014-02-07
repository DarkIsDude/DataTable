<?php

namespace DataTable\php;

/**
 * The main object of this framework.
 * Use it has an object and init it.
 * Add col on this object.
 * Use the show function to print the html.
 * Enjoy :)
 * @author doudou
 */
class Table {
	// Operation available
	public static $CREATE = 0x01;
	public static $READ = 0x02;
	public static $UPDATE = 0x04;
	public static $DELETE = 0x08;
	
	// Style of pagination
	public static $PAGINATION_FULL_NUMBERS = "full_numbers";
	public static $PAGINATION_NO = "";
	
	// Language available
	public static $FRANCAIS = "fr";
	public static $ENGLISH = "en";
	
	// Default parameters
	protected static $BDD_URL = "127.0.0.1";
	protected static $BDD_LOGIN = "root";
	protected static $BDD_PASSWORD = "";
	protected static $BDD_DATABASE = "pfe";
	protected static $BDD_DRIVER = "mysql";
	protected static $LANGUAGE = "en";
	protected static $ENCODING = "utf8";

	// Database parameters
	public $name = "";
	public $url = "";
	public $login = "";
	public $password = "";
	public $database = "";
	public $driver = "";
	public $language = "";
	public $encoding = "";
	
	// Allowed operation
	public $create = true;
	public $read = true;
	public $update = true;
	public $delete = true;
	public $sort = true;
	public $paginate = "";
	
	// Private attributs
	private $connect = null;
	private $error = "";
	private $link = "";
	private $idSerialize = null;
	
	// Object Collection
	public $cols;
	public $filters;
	public $messages;
	
	/**
	 * Check if some files must be delete (more old than 1 hour)
	 * @param Table $value
	 */
	public static function checkTempFiles($table) {
		$directoryPath = $table->link . 'temp/';
		
		if (is_dir($directoryPath)) {
			if ($directory = opendir($directoryPath)) {
				while (($file = readdir($directory)) !== false) {
					if ($file != '.' && $file != '..') {
						$filePath = $directoryPath . $file;
						$update = filemtime($filePath);
						$date = time();
						$dif = $date - $update;
							
						// Une heure en seconde => 60 * 60 = 3600
						if ($dif > 3600 && file_exists($filePath))
							unlink($filePath);
					}
				}
				closedir($directory);
			}
		}
	}
	
	/**
	 * Construct a table with default value
	 * @see \DataTable\Table::$BBD_*
	 */
	public function __construct() {
		$this->url = Table::$BDD_URL;
		$this->login = Table::$BDD_LOGIN;
		$this->password = Table::$BDD_PASSWORD;
		$this->database = Table::$BDD_DATABASE;
		$this->driver = Table::$BDD_DRIVER;
		$this->language = Table::$LANGUAGE;
		$this->encoding = Table::$ENCODING;
		
		$this->cols = new \ArrayObject();
		$this->filters = new \ArrayObject();
		
		$this->paginate = Table::$PAGINATION_FULL_NUMBERS;
	}
	
	/**
	 * Change the value to connect to the database and the name of the table in the database
	 * @param string $name name of the database table
	 * @param $url url of the database
	 * @param $login login to connect
	 * @param $password password to connect (empty if no password)
	 * @param $database name of the database
	 * @param $driver driver used to connect (mysql, pgsql, ...)
	 * @param $language language of the application
	 * @param $encoding of the database
	 * @return \DataTable\Table
	 */
	public function init($name, $url = "", $login = "", $password = "", $database = "", $driver = "", $language = "", $encoding = "") {		
		$this->name = $name;
		
		if (!empty($url)) $this->url = $url;
		if (!empty($login)) $this->login = $login;
		if (!empty($password)) $this->password = $password;
		if (!empty($database)) $this->database = $database;
		if (!empty($driver)) $this->driver = $driver;
		if (!empty($language)) $this->language = $language;
		if (!empty($encoding)) $this->encoding = $encoding;
		
		return $this;
	}
	
	/**
	 * Serialize and save this in a file
	 * @param boolean $save save in a file or not
	 * @param string $link serialize in this emplacement. If null, use the standart link of object Table
	 */
	public function serializeTable($save, $link = null) {
		if ($this->idSerialize == null)
			$this->idSerialize = date_format(new \DateTime(), 'YmdHis') . spl_object_hash($this);
	
		if ($save) {
			// Impossible de sérializer une instance de PDO
			$this->connect = null;
			if ($link == null)
				file_put_contents($this->link . 'temp/' . $this->idSerialize . '.temp', serialize($this));
			else
				file_put_contents($link . 'temp/' . $this->idSerialize . '.temp', serialize($this));
		}
	}
	
	/**
	 * Remove the serialized file
	 * @param string $link serialize in this emplacement. If null, use the standart link of object Table
	 */
	public function removeSerializedTable($link = null) {
		$this->serializeTable(false);
		if ($link == null)
			$filePath = $this->link . 'temp/' . $this->idSerialize . '.temp';
		else
			$filePath = $link . 'temp/' . $this->idSerialize . '.temp';
	
		if (file_exists($filePath)) {
			unlink($filePath);
			$code["success"] = true;
			$code["message"] = $this->messages["eNone"];
		}
		else {
			$code["success"] = false;
			$code["message"] = $this->messages["eFile"];
		}
		
		return $code;
	}
	
	/**
	 * Set the option to create data
	 * @param boolean $create
	 */
	public function canCreate($create) {
		$this->create = $write;
	}
	
	/**
	 * Set option tu read data
	 * @param boolean $read
	 */
	public function canRead($read) {
		$this->read = $read;
	}
	
	/**
	 * Set option to update data
	 * @param boolean $update
	 */
	public function canUpdate($update) {
		$this->update = $update;
	}
	
	/**
	 * Set option to delete data
	 * @param boolean $delete
	 */
	public function canDelete($delete) {
		$this->delete = $delete;
	}
	
	/**
	 * Set all flags (CREATE, READ, UPDATE, DELETE)
	 * For example $table->can(Table::CREATE | Table::DELETE)
	 * @param $flags
	 */
	public function can($flags) {
		if ($flags && Table::$CREATE)
			$this->create = true;
		else
			$this->create = false;
		
		if ($flags && Table::$READ)
			$this->read = true;
		else
			$this->read = false;
		
		if ($flags && Table::$UPDATE)
			$this->update = true;
		else
			$this->update = false;
		
		if ($flags && Table::$DELETE)
			$this->delete = true;
		else
			$this->delete = false;
	}
	
	/**
	 * Set option to enable or disable the sort on table
	 * @param boolean $sort
	 */
	public function canSort($sort) {
		$this->sort = $sort;
	}
	
	/**
	 * Set option to enable or disable the pagination and change style on table (see Tabe::$PAGINATION_*)
	 * @param string $paginate
	 */
	public function setPaginateStyle($paginate) {
		$this->paginate = $paginate;
	}
	
	/**
	 * Return the filter that has this name
	 * Return null if there are no filter with this name
	 * @param $name the name of the filter
	 * @return \DataTable\Filter|null
	 */
	private function getFilter($name) {
		$f = null;
	
		foreach ($this->filters as $filter)
			if ($filter->name == $name)
				$f = $filter;
	
		return $f;
	}
	
	/**
	 * Add a filter
	 * @param string $name the name of the col
	 * @param string $condition the condition
	 */
	public function addFilter($name, $condition) {
		$filter = $this->getFilter($name);
		
		if ($filter == null) {
			$filter = new Filter();
			$filter->name = $name;
			$filter->condition = $condition;
			$this->filters->append($filter);
		}
		else {
			$this->removeFilter($name);
			$filter = new Filter();
			$filter->name = $name;
			$filter->condition = $condition;
			$this->filters->append($filter);
		}
	}
	
	/**
	 * Remove all filter with this name
	 * @param unknown $name
	 */
	public function removeFilter($name) {
		$i = 0;
		
		foreach ($this->filters as $filter)
			if ($filter->name == $name)
				$this->filters->offsetUnset($i);
			else
				$i++;
	}
	
	/**
	 * Return every cols stored in the table
	 * @return \ArrayObject
	 */
	private function getCols() {
		return $this->cols;
	}
	
	/**
	 * Return the col that has this name
	 * Return null if there are no col with this name
	 * @param $name the name of the col
	 * @return \DataTable\Col|\DataTable\ColHidden|\DataTable\ColIndex|\DataTable\ColIndexLinked|\DataTable\ColLinked|null
	 */
	public function getCol($name) {
		$c = null;
		
		foreach ($this->cols as $col)
			if ($col->name == $name)
				$c = $col;
		
		return $c;
	}
	
	/**
	 * Add a simple col
	 * @param $label the label of the col
	 * @param $name the name in the database of the col
	 */
	public function addCol($label, $name) {
		$col = $this->getCol($name);
		
		if ($col == null) {
			$col = new Col();
			$col->table = $this;
			$col->name = $name;
			$col->label = $label;
			$this->cols->append($col);
		}
		else if ($col instanceof Col) {
			$col->table = $this;
			$col->label = $label;
		}
		else {
			$this->removeCol($name);
			$col = new Col();
			$col->table = $this;
			$col->name = $name;
			$col->label = $label;
			$this->cols->append($col);
		}
	}
	
	/**
	 * Add a col use for the index of the table
	 * @param $label the label of the col
	 * @param $name the name in the database of the col
	 * @param $visible set true to show at the user this col
	 * @param $creatable set true to allow add
	 */
	public function addColIndex($label, $name, $visible, $creatable) {
		$col = $this->getCol($name);
	
		if ($col == null) {
			$col = new ColIndex();
			$col->table = $this;
			$col->label = $label;
			$col->name = $name;
			$col->visible = $visible;
			$col->creatable = $creatable;
			$this->cols->append($col);
		}
		else if ($col instanceof ColIndex) {
			$col->table = $this;
			$col->label = $label;
			$col->visible = $visible;
			$col->creatable = $creatable;
		}
		else {
			$this->removeCol($name);
			$col = new ColIndex();
			$col->table = $this;
			$col->label = $label;
			$col->name = $name;
			$col->visible = $visible;
			$col->creatable = $creatable;
			$this->cols->append($col);
		}
	}
	
	/**
	 * Add a col linked with antoher table
	 * For example, to link the table with the table user to show the login
	 * @param $label the label of the col
	 * @param $name the name in the database of the col
	 * @param $otherTable the name of the other table
	 * @param $otherIndex index of other table (must be one column)
	 * @param $otherName name on the colum use for show the label (for example, username)
	 */
	public function addColLinked($label, $name, $otherTable, $otherIndex, $otherName) {
		$col = $this->getCol($name);
		
		if ($col == null) {
			$col = new ColLinked();
			$col->table = $this;
			$col->name = $name;
			$col->label = $label;
			$col->otherTable = $otherTable;
			$col->otherTableId = $otherIndex;
			$col->otherTableName = $otherName;
			$this->cols->append($col);
		}
		else if ($col instanceof ColLinked) {
			$col->table = $this;
			$col->label = $label;
			$col->otherTable = $otherTable;
			$col->otherTableId = $otherIndex;
			$col->otherTableName = $otherName;
		}
		else {
			$this->removeCol($name);
			$col = new ColLinked();
			$col->table = $this;
			$col->name = $name;
			$col->label = $label;
			$col->otherTable = $otherTable;
			$col->otherTableId = $otherIndex;
			$col->otherTableName = $otherName;
			$this->cols->append($col);
		}
	}
	
	/**
	 * Add a col that is not show, but with a default value when you create a new row
	 * @param $label the label of the col
	 * @param $name the name in the database of the col
	 * @param $default the default value
	 */
	public function addColHidden($label, $name, $default) {
		$col = $this->getCol($name);
	
		if ($col == null) {
			$col = new ColHidden();
			$col->table = $this;
			$col->name = $name;
			$col->label = $label;
			$col->default = $default;
			$this->cols->append($col);
		}
		else if ($col instanceof ColHidden) {
			$col->table = $this;
			$col->label = $label;
			$col->default = $default;
		}
		else {
			$this->removeCol($name);
			$col = new ColHidden();
			$col->table = $this;
			$col->name = $name;
			$col->label = $label;
			$col->default = $default;
			$this->cols->append($col);
		}
	}
	
	/**
	 * This col is show, else we use a classic index col
	 * @see \DataTable\Table::addColIndex($label, $name, $visible)
	 * @see \DataTable\Table::addColLinked($label, $name, $otherTable, $otherIndex, $otherName)
	 * @param $label the label of the col
	 * @param $name the name in the database of the col
	 * @param $otherTable the name of the other table
	 * @param $otherIndex index of other table (must be one column)
	 * @param $otherName name on the colum use for show the label (for example, username)
	 */
	public function addColIndexLinked($label, $name, $otherTable, $otherIndex, $otherName) {
	$col = $this->getCol($name);
		
		if ($col == null) {
			$col = new ColIndexLinked();
			$col->table = $this;
			$col->name = $name;
			$col->label = $label;
			$col->otherTable = $otherTable;
			$col->otherTableId = $otherIndex;
			$col->otherTableName = $otherName;
			$this->cols->append($col);
		}
		else if ($col instanceof ColIndexLinked) {
			$col->table = $this;
			$col->label = $label;
			$col->otherTable = $otherTable;
			$col->otherTableId = $otherIndex;
			$col->otherTableName = $otherName;
		}
		else {
			$this->removeCol($name);
			$col = new ColIndexLinked();
			$col->table = $this;
			$col->name = $name;
			$col->label = $label;
			$col->otherTable = $otherTable;
			$col->otherTableId = $otherIndex;
			$col->otherTableName = $otherName;
			$this->cols->append($col);
		}
	}
	
	/**
	 * Remove the col that has this name
	 * @param $name the name of the col that you want remove
	 */
	public function removeCol($name) {
		$i = 0;
		
		foreach ($this->cols as $col)
			if ($col->name == $name)
				$this->cols->offsetUnset($i);
			else
				$i++;
	}
		
	/**
	 * Print the table as HTML
	 * @param string $nameVar the name of the variable
	 * @param string $link the link where the framework is (must end with /)
	 */
	public function show($link) {
		$this->serializeTable(false);
		$this->link = $link;
		$this->messages = json_decode(file_get_contents($this->link . "translations/" . $this->language . ".json"), true);

		$this->setType();
		
		print $this->showTable() . $this->showModal();
		Table::checkTempFiles($this);
		$this->serializeTable(true);
	}
	
	/**
	 * Return a string with this table at the HTML format
	 * @return string
	 */
	private function showTable() {
		$print = "";
		
		// Begining of the print
		$print .= '<table sortable="' . $this->sort . '" pagination="' . $this->paginate . '" identifier="' . $this->idSerialize . '" language="' . $this->language . '" link="' . $this->link . '" dataTable="' . $this->name . 'Table" create="' . $this->create . '" read="' . $this->read . '" update="' . $this->update . '" delete="' . $this->delete . '" width="100%">';
		
			// The head
			$print .= '<thead>';
				$print .= '<tr>';
					foreach ($this->cols as $col) {
						if ($col->isVisible()) {
							$print .= '<th dataname="' . $col->getHeadName() . '">' . $col->getHeadLabel() . '</th>';
						}
					}
				$print .= '</tr>';
			$print .= '</thead>'; 
		
			// The data
			$print .= '<tbody>';
				$print .= $this->showBody();
			$print .= '</tbody>';
		
		$print .= '</table>';
		// End of the table
		
		return $print;
	}
	
	/**
	 * Return the string to print the body at the HTML format
	 * @return string
	 */
	private function showBody() {
		$print = "";
		
		if ($this->read) {
			$allData = $this->getData();
			if ($allData) {
				foreach ($allData as $data) {// For each row
					$toPrint = '';
					$index = '';
					foreach ($this->cols as $col) {
						$attrIndex = '';
						$temp = $col->getIndex($data);
						
						if (!empty($temp)) {// If there are data for this column
							$index .= $col->name . ':' . $col->getIndex($data) . ',';
							$attrIndex = 'index="true" ';
						}
		
						if ($col->isVisible()) {// If this column is visible
							$toPrint .= '<td type="' . $col->type . '" ' . $attrIndex . 'dataName="' . $col->getHeadName() . '" value="' . htmlentities($col->getBody($data)) . '">' . htmlentities($col->getBody($data)) . '</td>';
						}
					}
					
					$index = substr($index, 0, strlen($index) - 1);
					$print .= '<tr index="' . $index . '">' . $toPrint . '</tr>';
				}
			}
			else {
				print $this->error;
			}
		}
		
		return $print;
	}
	
	/**
	 * Print the modal on the HTML format
	 * @return string
	 */
	private function showModal() {
		$print = "";
		
		$print .= '<div class="modal fade" id="' . $this->name . 'Modal" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">';
			$print .= '<div class="modal-dialog">';
				$print .= '<div class="modal-content">';
					$print .= '<div class="modal-header">';
						$print .= '<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>';
						$print .= '<h4 class="modal-title" id="modalLabel">Ajouter un enregistrement</h4>';
					$print .= '</div>';
					$print .= '<div class="modal-body">';
					
						// Début de l'affichage du formulaire
						$print .= '<form role="form" class="form-horizontal">';
					
						foreach ($this->getCols() as $col)
							if ($col->isVisible() && $col->isCreatable())
								$print .= $this->showInput($col);
						
						$print .= '</form>';
						// Fin de l'ajout du formulaire
						
					$print .= '</div>';
					$print .= '<div class="modal-footer">';
						$print .= '<button type="button" class="btn btn-default" data-dismiss="modal">Annuler</button>';
						$print .= '<button type="button" class="btn btn-primary">Enregistrer</button>';
					$print .= '</div>';
				$print .= '</div>';
			$print .= '</div>';
		$print .= '</div>';			
		
		return $print;
	}
	
	/**
	 * Show, for this column, the correct input
	 * @param unknown $col
	 * @return string
	 */
	public function showInput($col) {
		$print = "";
		
		switch ($col->type) {
			case "linked":
				$options = $this->prepareExecute($col->getSelectOption(), array());
				$print .= '<div class="form-group">';
				$print .= '<label class="col-sm-2 control-label" for="' . $this->name . $col->name . '">' . $col->label. '</label>';
				$print .= '<div class="col-sm-10">';
				$print .= '<select dataname="' . $col->name . '" class="form-control" id="' . $this->name . $col->name . '" typeData="' . $col->type . '">';
				foreach ($options as $option)
					$print .= "<option value=" . $option[0] . ">" . $option[1] . "</option>";
				$print .= '</select>';
				$print .= '</div>';
				$print .= '</div>';
				break;
			case "boolean":
				$print .= '<div class="form-group">';
				$print .= '<div class="col-sm-offset-2 col-sm-10">';
				$print .= '<div class="checkbox">';
				$print .= '<label>';
				$print .= '<input dataname="' . $col->name . '" type="checkbox" id="' . $this->name . $col->name . '" typeData="' . $col->type . '">' . $col->label . '';
				$print .= '</label>';
				$print .= '</div>';
				$print .= '</div>';
				$print .= '</div>';
				break;
			case "text":
				$print .= '<div class="form-group">';
				$print .= '<label class="col-sm-2 control-label" for="' . $this->name . $col->name . '">' . $col->label. '</label>';
				$print .= '<div class="col-sm-10">';
				$print .= '<textarea rows="3" dataname="' . $col->name . '" class="form-control" id="' . $this->name . $col->name . '" typeData="' . $col->type . '"></textarea>';
				$print .= '</div>';
				$print .= '</div>';
				break;
			case "varchar":
				$print .= '<div class="form-group">';
				$print .= '<label class="col-sm-2 control-label" for="' . $this->name . $col->name . '">' . $col->label. '</label>';
				$print .= '<div class="col-sm-10">';
				$print .= '<input dataname="' . $col->name . '" type="text" class="form-control" id="' . $this->name . $col->name . '" maxlength="' . $col->maxLength . '" typeData="' . $col->type . '">';
				$print .= '</div>';
				$print .= '</div>';
				break;
			case "number":
				$print .= '<div class="form-group">';
				$print .= '<label class="col-sm-2 control-label" for="' . $this->name . $col->name . '">' . $col->label. '</label>';
				$print .= '<div class="col-sm-10">';
				$print .= '<input dataname="' . $col->name . '" type="number" class="form-control" id="' . $this->name . $col->name . '" typeData="' . $col->type . '">';
				$print .= '</div>';
				$print .= '</div>';
				break;
			case "double":
				$print .= '<div class="form-group">';
				$print .= '<label class="col-sm-2 control-label" for="' . $this->name . $col->name . '">' . $col->label. '</label>';
				$print .= '<div class="col-sm-10">';
				$print .= '<input dataname="' . $col->name . '" type="number" class="form-control" id="' . $this->name . $col->name . '" typeData="' . $col->type . '">';
				$print .= '</div>';
				$print .= '</div>';
				break;
			case "date":
				$print .= '<div class="form-group">';
				$print .= '<label class="col-sm-2 control-label" for="' . $this->name . $col->name . '">' . $col->label. '</label>';
				$print .= '<div class="col-sm-10">';
				$print .= '<input dataname="' . $col->name . '" type="date" class="form-control" id="' . $this->name . $col->name . '" typeData="' . $col->type . '">';
				$print .= '</div>';
				$print .= '</div>';
				break;
			case "datetime":
				$print .= '<div class="form-group">';
				$print .= '<label class="col-sm-2 control-label" for="' . $this->name . $col->name . '">' . $col->label. '</label>';
				$print .= '<div class="col-sm-10">';
				$print .= '<input dataname="' . $col->name . '" type="datetime-local" class="form-control" id="' . $this->name . $col->name . '" typeData="' . $col->type . '">';
				$print .= '</div>';
				$print .= '</div>';
				break;
			case "time":
				$print .= '<div class="form-group">';
				$print .= '<label class="col-sm-2 control-label" for="' . $this->name . $col->name . '">' . $col->label. '</label>';
				$print .= '<div class="col-sm-10">';
				$print .= '<input dataname="' . $col->name . '" type="time" class="form-control" id="' . $this->name . $col->name . '" typeData="' . $col->type . '">';
				$print .= '</div>';
				$print .= '</div>';
				break;
			default:
				$print .= '<div class="form-group">';
				$print .= '<label class="col-sm-2 control-label" for="' . $this->name . $col->name . '">' . $col->label. '</label>';
				$print .= '<div class="col-sm-10">';
				$print .= '<input dataname="' . $col->name . '" class="form-control" id="' . $this->name . $col->name . '" typeData="' . $col->type . '">';
				$print .= '</div>';
				$print .= '</div>';
		}
		
		return $print;
	}
	
	/**
	 * Connect to the database
	 * If this table is already connected, nothing is done
	 * @return \PDO
	 */
	private function connect() {
		if ($this->connect == null) {
			try {
				$this->connect = new \PDO($this->driver . ':host=' . $this->url . ';dbname=' . $this->database . '', $this->login, $this->password);
				$this->connect->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);// To have an exception if i can't exceute the query
				$this->connect->exec("SET CHARACTER SET " . $this->encoding);
			}
			catch (\Exception $e) {
				$this->connect = null;
				$this->error = 'Impossible de se connecter: ' . $e->getMessage();
			}
		}
	}
	
	/**
	 * Prepare and execute the requete with the parameters array
	 * @param string $requete the requete
	 * @param array $parameters the parameters of this requete (:.... in the requete)
	 */
	private function prepareExecute($requete, $parameters) {
		$this->connect();
		$data = false;
	
		try {
			if ($this->connect) {
				$req = $this->connect->prepare($requete);
				$req->execute($parameters);
				$data = new \ArrayObject();
				
				// To transform the row into an ArrayObject
				if (!(substr($requete, 0, strlen("INSERT")) == "INSERT") && !(substr($requete, 0, strlen("UPDATE")) == "UPDATE") && !(substr($requete, 0, strlen("DELETE")) == "DELETE"))
					while ($row = $req->fetch())
						$data->append($row);
	
				$req->closeCursor();
			}
		}
		catch (\Exception $e) {
			$data = false;
			$this->connect = null;
			$this->error = 'Exécution de la requete: ' . $e->getMessage();
		}
			
		return $data;
	}
	
	/**
	 * Pour chaque colonne, mets en place son type, sa longueur maximale
	 */
	private function setType() {
		$driver = null;
		switch ($this->driver) {
			case "pgsql":
				$driver = new TableDriverPGSQL();
				break;
			default:
				$driver = new TableDriverDefault();
				break;
		}
		
		$driver->setTable($this);
		$requete = $driver->getSQL();
		$response = $this->prepareExecute($requete['requete'], $requete['parameters']);
		
		foreach ($response as $aInfo) {
			$columnName = $driver->getColumnName($aInfo);
			$dataType = $driver->getColumnType($aInfo);
			$maxLength = $driver->getColumnLength($aInfo);
		
			$col = $this->getCol($columnName);
			if ($col != null) {
				switch ($dataType) {
					case "boolean":
					case "tinyint":
						$col->setType("boolean");
						$col->maxLength = "";
						break;
					case "decimal":
					case "float":
					case "real":
					case "double":
						$col->setType("double");
						$col->maxLength = $maxLength;
						break;
					case "varchar":
					case "text":
					case "date":
					case "time":
					case "datetime":
						$col->setType($dataType);
						$col->maxLength = $maxLength;
						break;
					default:
						if (strpos($dataType, 'int') !== false) {
							$col->setType("number");
							$col->maxLength = $maxLength;
						}
						else {
							$col->setType("");
							$col->maxLength = 0;
						}
						break;
				}
			}
		}
	}
	
	/**
	 * Construct the SQL requete to have data and return the data
	 * @return \ArrayObject
	 */
	private function getData() {
		return $this->prepareExecute($this->getDataSQL(), array());
	}
	
	/**
	 * Return the SQL query to get the data
	 * @return string
	 */
	private function getDataSQL() {
		$tables[] = $this->name;
		$links = "";
		$req = "SELECT";
		
		// For each col
		foreach ($this->cols as $col) {
			// Add the SELECT
			$tempReq = $col->getField();
			if ($req == "SELECT") $req = $req . " " . $tempReq;
			else $req = $req . ", " . $tempReq;
			
			// Add all table
			$tempTables = $col->getTables();
			if (count($tempTables) > 0)
			foreach ($tempTables as $table)
				$tables[] = $table;
			
			// Add the WHERE
			$tempLink = $col->getLinks();
			if ($tempLink != "") {
				if ($links != "") $links = $links . " AND ";
				$links = $links . " " . $tempLink;
			}	
		}
		
		// Add the filter
		foreach ($this->filters as $filter) {
			if ($links != "") $links = $links . " AND ";
			$links = $links . $this->name . "." . $filter->name . " " . $filter->condition;
		}
		
		// Add the table in the query
		$tables = array_unique($tables);
		$allTables = "";
		foreach ($tables as $table)
		if ($allTables == "") $allTables = $allTables . " FROM " . $table;
		else $allTables = $allTables . ", " . $table;
		$req = $req . $allTables;
		
		// Add the link beetween the table
		if ($links != "")
			$req = $req . " WHERE " . $links;
		
		return $req;
	}
		
	/**
	 * Transform a string with this format id:value,id:value... in an array
	 * Return null if a column of id can't find
	 * @param string $index index with this format: id:value,id:value
	 * @return array|null
	 */
	private function parseIndex($index) {
		$error = false;
		$indexCSV = array();
		$allIndex = explode(',', $index);
	
		foreach ($allIndex as $aCSV) {
			$aIndex = explode(':', $aCSV);
			$name = $aIndex[0];
			$value = $aIndex[1];
			$indexCSV[$name] = $value;
				
			// Vérification que la colonne existe
			$col = $this->getCol($name);
			if ($col == null)
				$error = true;
		}
	
		if ($error)
			return null;
		else
			return $indexCSV;
	}
	
	/**
	 * Update the value of the col name on the row index with old value and new the new value
	 * Return an array with success true if the update is done and with message to the error or the new value
	 * @param $name the name of the column
	 * @param $index the index field @see \DataTable\Table::parseIndex
	 * @param $old the old value
	 * @param $new the new value
	 * @return array
	 */
	public function updateData($name, $index, $old, $new) {
		$index = $this->parseIndex($index);
		$code = array("success" => true, "message" => "");
		
		if ($this->update) {
			if ($index != null) {
				$col = $this->getCol($name);
				
				if ($col != null) {
					$req = $col->update($index, $new);
					
					if ($req != null) {
						if ($req && $this->prepareExecute($req["requete"], $req["parameters"])) {
							$reqValue = $col->getValue($index);
							
							$code["success"] = true;
							$temp = $this->prepareExecute($reqValue["requete"], $reqValue["parameters"])->offsetGet(0);
							$code["message"] = $temp[0];
						}
						else {
							$code["success"] = false;
							$code["message"] = $this->error;
						}
					}
					else {
						$code["success"] = false;
						$code["message"] = $this->messages["eQuery"];
					}
				}
				else {
					$code["success"] = false;
					$code["message"] = $this->messages["eFindCol"];
				}
			}
			else {
				$code["success"] = false;
				$code["message"] = $this->messages["eFindCol"];
			}
		}
		else {
			$code["success"] = false;
			$code["message"] = $this->messages["eAction"];
		}
		
		return $code;
	}
	
	/**
	 * Delete the row with this index
	 * Return an array with success true if the update is done and with message to the error or the new value
	 * @param $index the index field @see \DataTable\Table::parseIndex
	 * @return array
	 */
	public function deleteData($index) {
		$index = $this->parseIndex($index);
		$code = array("success" => true, "message" => "");
		
		if ($this->delete) {
			if ($index != null) {
				$req = $this->deleteSQL($index);
				
				if ($req != null) {
					if ($req && $this->prepareExecute($req["requete"], $req["parameters"])) {
						$code["success"] = true;
						$code["message"] = $this->messages["eNone"];
					}
					else {
						$code["success"] = false;
						$code["message"] = $this->error;
					}
				}
				else {
					$code["success"] = false;
					$code["message"] = $this->messages["eQuery"];
				}
			}
			else {
				$code["success"] = false;
				$code["message"] = $this->messages["eFindCol"];
			}
		}
		else {
			$code["success"] = false;
			$code["message"] = $this->messages["eAction"];
		}
		
		return $code;
	}
	
	/**
	 * Return the SQL query to delete the row with this index
	 * Return an array with requete, the requete in SQL and parameters, the parameters of this requete
	 * Return null is can't create the requete
	 * @param array $index @see \DataTable\Table::parseIndex
	 * @return multitype:string multitype:array
	 */
	private function deleteSQL($index) {
		$req = array("requete" => 'DELETE FROM ' . $this->name . ' WHERE ', "parameters" => array());
		
		// Construct the WHERE part for the index
		$reqIndex = '';
		foreach ($index as $id => $value) {
			if (!empty($reqIndex)) $reqIndex .= ' AND ';
			$reqIndex .= $id . ' = :' . $id;
			$req["parameters"][$id] = $value;
		}
		$req["requete"] .= $reqIndex;
		
		return $req; 
	}
	
	/**
	 * Add a new row in the database with this data and this index
	 * Return an array with success true if the update is done and with message to the error or the new value
	 * @param array $index the column of this data
	 * @param array $data the data
	 * @return array
	 */
	public function createData($json) {
		$code = array("success" => true, "message" => "");
		$values = json_decode($json, true);
		
		if ($this->create) {
			$req = $this->createSQL($values);
			
			if ($req != null) {
				if ($req && $this->prepareExecute($req["requete"], $req["parameters"])) {
					$code["success"] = true;
					$code["message"] = $this->showTable();
				}
				else {
					$code["success"] = false;
					$code["message"] = $this->error;
				}
			}
			else {
				$code["success"] = false;
				$code["message"] = $this->messages["eQuery"];
			}
		}
		else {
			$code["success"] = false;
			$code["message"] = $this->messages["eAction"];
		}
		
		return $code;
	}
	
	/**
	 * Return the SQL query to create the row with this $data and this $index
	 * Return an array with requete, the requete in SQL and parameters, the parameters of this requete
	 * Return null is can't create the requete
	 * @param array $index the column of this data
	 * @param array $data the data
	 * @return multitype:string multitype:array
	 */
	private function createSQL($values) {
		// Link the two table
		$error = false;
		foreach ($values as $index => $value) {
			$col = $this->getCol($index);
			
			if ($col == null)
				$error = true;
		}
		
		// Create the query
		if (!$error) {
			$req = array("requete" => "", "parameters" => array());
			$reqColumn = "";
			$reqValue = "";
			
			foreach ($this->getCols() as $col) {
				if (array_key_exists($col->name, $values))
					$value = $col->create($values[$col->name]);
				else
					$value = $col->create('');
			
				if ($value) {
					if (!empty($reqColumn)) {
						$reqColumn = $reqColumn . ", ";
						$reqValue = $reqValue . ", ";
					}
					
					$reqColumn .= $col->name;
					$reqValue .= ":" . $col->name;
					$req["parameters"][$col->name] = $value;
					
				}
			}
			$req["requete"] = "INSERT INTO " . $this->name . " (" . $reqColumn . ") VALUES (" . $reqValue . ");";
			
			return $req;
		}
		else
			return null;
	}
}