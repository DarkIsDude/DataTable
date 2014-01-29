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
		$requete['requete'] = 'SELECT COLUMN_NAME, DATA_TYPE, CHARACTER_MAXIMUM_LENGTH FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME=:name AND TABLE_SCHEMA=:schema;';
		$requete['parameters'] = array("name" => $this->getTable()->name, "schema" => "public");
		
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
}