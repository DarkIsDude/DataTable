<?php

namespace DataTable\php;

/**
 * @author doudou
 */
abstract class TableDriver {
	/**
	 * Call this function to update the type
	 * @return array the SQL function
	 */
	public abstract function getSQL();
	
	/**
	 * Call this function to have all function
	 * @return array the SQL function
	 */
	public abstract function getAllColumn();
	
	/**
	 * Set the table of this class
	 * @param unknown $table
	 */
	public abstract function setTable($table);
	
	/**
	 * Get the table of this class
	 * @return Table the table of this class
	 */
	public abstract function getTable();
	
	/**
	 * Return the name of the column for this row
	 * @param unknown $aRow
	 */
	public abstract function getColumnName($aRow);
	
	/**
	 * Return the name of the column for this row
	 * @param unknown $aRow
	 */
	public abstract function getColumnType($aRow);
	
	/**
	 * Return the name of the column for this row
	 * @param unknown $aRow
	 */
	public abstract function getColumnLength($aRow);
	
	/**
	 * Call this function to know if aRow is index of table
	 * @param unknown $aRow
	 * @return boolean
	 */
	public abstract function isIndex($aRow);
}