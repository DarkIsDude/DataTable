<?php

namespace DataTable\php;

/**
 * A simple col that is the index of the main table
 * It's impossible to edit this col
 * @author doudou
 * @see \DataTable\Col
 */
class ColIndex extends Col {
	public $visible = true;
	public $creatable = false;
	
	/**
	 * (non-PHPdoc)
	 * @see \DataTable\Col::getHeadLabel()
	 */
	public function getHeadLabel() {
		if ($this->visible) return $this->label;
		else return '';
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \DataTable\Col::getHeadName()
	 */
	public function getHeadName() {
		if ($this->visible) return $this->name;
		else return '';
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \DataTable\Col::getBody()
	 */
	public function getBody($data) {
		if ($this->visible) return $data[$this->name];
		else return '';
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \DataTable\Col::getIndex()
	 */
	public function getIndex($data) {
		return $data[$this->name];
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \DataTable\Col::isVisible()
	 */
	public function isVisible() {
		return $this->visible;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \DataTable\Col::isCreatable()
	 */
	public function isCreatable() {
		return $this->creatable;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \DataTable\Col::update()
	 */
	public function update($index, $new) {
		return '';
	}
}
