<?php

namespace DataTable\php;

/**
 * A simple col that is not shown but when you had a new row, this col has a default value
 * @author doudou
 * @see \DataTable\Col
 */
class ColHidden extends Col {
	public $default;
	
	/**
	 * (non-PHPdoc)
	 * @see \DataTable\Col::getHeadLabel()
	 */
	public function getHeadLabel() {
		return '';
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \DataTable\Col::getHeadName()
	 */
	public function getHeadName() {
		return '';
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \DataTable\Col::getBody()
	 */
	public function getBody($data) {
		return '';
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \DataTable\Col::isVisible()
	 */
	public function isVisible() {
		return false;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \DataTable\Col::isCreatable()
	 */
	public function isCreatable() {
		return false;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see \DataTable\Col::create()
	 */
	public function create($value) {
		// When i create a new row, I always use the default value
		return $this->default;
	}
}
