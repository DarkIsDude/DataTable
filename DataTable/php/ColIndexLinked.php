<?php

namespace DataTable\php;

use DataTable\php\ColLinked;
/**
 * A simple col that is link and that is an index
 * @author doudou
 * @see \DataTable\Col
 * @see \DataTable\ColLinked
 * @see \DataTable\ColIndex
 */
class ColIndexLinked extends ColLinked {	
	/**
	 * (non-PHPdoc)
	 * @see \DataTable\Col::getIndex()
	 */
	public function getIndex($data) {
		return $data[$this->name];
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \DataTable\Col::update()
	 */
	public function update($index, $new) {
		return '';
	}
}
