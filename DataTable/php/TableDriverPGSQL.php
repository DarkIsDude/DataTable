<?php

namespace DataTable\php;

/**
 * @author doudou
 */
class TableDriverPGSQL extends TableDriver {
	public $table = null;
	
	/**
	 * (non-PHPdoc)
	 * @see \DataTable\TableDriver::getSQL()
	 */
	public function getSQL() {
		$requete['requete'] = 'SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME=:name AND TABLE_CATALOG=:schema;';
		$requete['parameters'] = array("name" => $this->getTable()->name, "schema" => $this->getTable()->database);
		
		return $requete;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \DataTable\TableDriver::getAllColumn()
	 */
	public function getAllColumn() {
		$requete['requete'] = 'SELECT COLUMN_NAME FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME=:name AND TABLE_CATALOG=:schema;';
		$requete['parameters'] = array("name" => $this->getTable()->name, "schema" => $this->getTable()->database);
		
		return $requete;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \DataTable\TableDriver::setTable()
	 */
	public function setTable($table) {
		$this->table = $table;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \DataTable\TableDriver::getTable()
	 */
	public function getTable() {
		return $this->table;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \DataTable\TableDriver::getColumnName()
	 */
	public function getColumnName($aRow) {
		return $aRow['column_name'];
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \DataTable\TableDriver::getColumnType()
	 */
	public function getColumnType($aRow) {
		return $aRow['data_type'];
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \DataTable\TableDriver::getColumnLength()
	 */
	public function getColumnLength($aRow) {
		return $aRow['character_maximum_length'];
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \DataTable\TableDriver::isIndex()
	 */
	public function isIndex($aRow) {
		$requete['requete'] = 'SELECT * FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE WHERE TABLE_NAME=:name AND TABLE_CATALOG=:schema AND COLUMN_NAME = :column AND CONSTRAINT_NAME=:constraint;';
		$requete['parameters'] = array("name" => $this->getTable()->name, "schema" => $this->getTable()->database, "constraint" => $this->getTable()->name . "_pkey", "column" => $this->getColumnName($aRow));
		$data = $this->table->prepareExecute($requete["requete"], $requete["parameters"]);
		
		return count($data) == 1;
	}
}