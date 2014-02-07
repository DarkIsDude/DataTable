<?php

namespace DataTable\php;

/**
 * A simple col
 * @author doudou
 */
class Col {	
	public $table = null;
	public $label = "";
	public $name = "";
	// number, double, varchar, text, date, datetime, time, boolean, linked
	public $type = "";
	public $maxLength = "";
	
	/**
	 * Construct
	 */
	public function __construct() {
		$this->filters = new \ArrayObject();
	}
	
	/**
	 * Set the type of the column
	 * @param unknown $type
	 */
	public function setType($type) {
		$this->type = $type;
	}
	
	/**
	 * Return the label of this column
	 * @return string
	 */
	public function getHeadLabel() {
		return $this->label;
	}
	
	/**
	 * Return the name of this column
	 * @return string
	 */
	public function getHeadName() {
		return $this->name;
	}
	
	/**
	 * Return the value of this column in data
	 * @param array $data the data
	 * @return string
	 */
	public function getBody($data) {
		return $data[$this->name];
	}
	
	/**
	 * Return the value of the index
	 * If this col is used for index, return the value of this col as index
	 * @param array $data the data
	 * @return string
	 */
	public function getIndex($data) {
		return '';
	}
	
	/**
	 * Return the new table for this column
	 * For example, if this column is linked, return the linked table
	 * @return array()
	 */
	public function getTables() {
		return array();
	}
	
	/**
	 * Return the field that this column use
	 * @return string
	 */
	public function getField() {
		return $this->table->name . "." . $this->name;
	}
	
	/**
	 * Return the WHERE xxx = xxx for this column
	 * @return string
	 */
	public function getLinks() {
		return '';
	}
	
	/**
	 * If the type of this column, Table call this function to have the different option
	 * @return string SQL function (no parameters)
	 */
	public function getSelectOption() {
		return "";
	}
	
	/**
	 * Return true if this col is show at the user
	 * @return boolean
	 */
	public function isVisible() {
		return true;
	}
	
	/**
	 * Return true if you can add this column
	 * @return boolean
	 */
	public function isCreatable() {
		return true;
	}
	
	/**
	 * Return the SQL query to get the value of this colum at this index
	 * Return an array with requete, the requete in SQL and parameters, the parameters of this requete
	 * @param array $index @see \DataTable\Table::parseIndex
	 * @return multitype:string multitype:array
	 */
	public function getValue($index) {
		// Construct the basic query
		$req = array(
				"requete" => "SELECT " . $this->table->name . "." . $this->name . " FROM " . $this->table->name . " WHERE ", 
				"parameters" => array());
		
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
	 * Return the SQL query to update the row at with this index and with the new value
	 * Return an array with requete, the requete in SQL and parameters, the parameters of this requete
	 * @param array $index @see \DataTable\Table::parseIndex
	 * @param string $new the new value
	 * @return multitype:string multitype:array
	 */
	public function update($index, $new) {
		// Construct the basic query
		$req = array(
				"requete" => 'UPDATE ' . $this->table->name . ' SET ' . $this->name . ' = :value WHERE ', 
				"parameters" => array());
		$req["parameters"]["value"] = $new;
		
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
	 * Return the value if i want create this colum with this value
	 * @param string $value value that the user give (can be null)
	 * @return string
	 */
	public function create($value) {
		if ($this->isVisible())
			return $value;
		else
			return '';
	}
}