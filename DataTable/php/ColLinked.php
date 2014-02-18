<?php

namespace DataTable\php;

/**
 * A simple col that is link with an other table (to show the value of this another table and not the foreign key in the main table)
 * @author doudou
 * @see \DataTable\Col
 */
class ColLinked extends Col {
	public $otherTable = "";
	public $otherTableId = "";
	public $otherTableName = "";
	
	public $filters;
	
	private $allColumn = array();
	
	/**
	 * Construct
	 */
	public function __construct() {
		parent::__construct();
	
		$this->filters = new \ArrayObject();
	}
	
	/**
	 * Save into $allColumn the real value of otherTableName
	 */
	private function parseName() {
		if (count($this->allColumn) == 0) {
			preg_match_all("#\*([\d|\w]+)\*#", $this->otherTableName, $columns, PREG_SET_ORDER);
			
			foreach($columns as $column)
				$this->allColumn[] = $column[1];
		}
	}
	
	/**
	 * Add a filter to just can select it
	 * @param string $name
	 * @param string $condition
	 */
	public function addFilter($name, $condition) {
		$filter = new Filter();
		$filter->name = $name;
		$filter->condition = $condition;
		$this->filters->append($filter);
	}
	
	/**
	 * Remove all filter with this name
	 * @param string $name
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
	 * (non-PHPdoc)
	 * @see \DataTable\Col::setType()
	 */
	public function setType($type) {
		$this->type = "linked";
	}
	
	/**
	 * Return the index of this data
	 * @param unknown $data
	 * @param string $force
	 * @return string
	 */
	public function getIndex($data, $force = false) {
		if ($force)
			return $data[$this->otherTableId];
		else
			return '';
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \DataTable\Col::getBody()
	 */
	public function getBody($data) {
		$this->parseName();
		$string = substr($this->otherTableName, 0);
		
		foreach ($this->allColumn as $column)
			$string = str_replace("*" . $column . "*", $data[$column], $string);
		
		return $string;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \DataTable\Col::getTables()
	 */
	public function getTables() {
		return array($this->otherTable);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \DataTable\Col::getField()
	 */
	public function getField() {
		$this->parseName();
		$field = $this->table->name . "." . $this->name;
		
		foreach ($this->allColumn as $column)
			$field .= ", " . $this->otherTable . "." . $column;
		
		return  $field;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \DataTable\Col::getLinks()
	 */
	public function getLinks() {
		return $this->table->name . "." . $this->name . " = " . $this->otherTable . "." . $this->otherTableId;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \DataTable\Col::getSelectOption()
	 */
	public function getSelectOption() {
		$query = "SELECT " . $this->otherTableId;
		foreach ($this->allColumn as $column)
			$query .= ", " . $column;
		$query .= " FROM " . $this->otherTable;

		$where = "";		
		foreach ($this->filters as $filter)
			if (empty($where)) $where = " WHERE " . $filter->name . " " . $filter->condition;
			else $where .= " AND " . $filter->name . " " . $filter->condition;
		
		return $query . $where;
	}
		
	/**
	 * (non-PHPdoc)
	 * @see \DataTable\Col::getValue()
	 */
	public function getValue($index) {
		$this->parseName();
		$req = array(
				"requete" => "",
				"parameters" => array());
		// Construct the basic select
		$req["requete"] .= "SELECT " . $this->otherTable . "." . $this->otherTableId;
		foreach ($this->allColumn as $column)
			$req["requete"] .= ", " . $this->otherTable . "." . $column;
		$req["requete"] .= " FROM " . $this->table->name . ", " . $this->otherTable . " WHERE ";
	
		// Construct the WHERE part for the index
		$reqIndex = '';
		foreach ($index as $id => $value) {
			if (!empty($reqIndex)) $reqIndex .= ' AND ';
			$reqIndex .= $this->table->name . '.' . $id . ' = :' . $id;
			$req["parameters"][$id] = $value;
		}
		// Add the link between both tables
		$req["requete"] .= $reqIndex . ' AND ' . $this->otherTable . '.' . $this->otherTableId . ' = ' . $this->table->name . '.' . $this->name;
	
		return $req;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \DataTable\Col::update()
	 */
	public function update($index, $new) {
		// The select if to find the correct value in the other table
		$req = array(
				"requete" => 'SELECT ' . $this->otherTableId . ' FROM ' . $this->otherTable . ' WHERE ' . $this->otherTableId . ' = :value',
				"parameters" => array());
		$req["parameters"]["value"] = $new;
		$req["requete"] = 'UPDATE ' . $this->table->name . ' SET ' . $this->name . ' = (' . $req["requete"] . ') WHERE ';
	
		// Construct the WHERE part for the index
		$reqIndex = '';
		foreach ($index as $id => $value) {
			if (!empty($reqIndex)) $reqIndex .= ' AND ';
			$reqIndex .= $this->table->name . '.' . $id . ' = :' . $id;
			$req["parameters"][$id] = $value;
		}
		$req["requete"] .= $reqIndex;
	
		return $req;
	}
}
