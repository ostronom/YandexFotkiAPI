<?php
namespace YandexFotkiAPI;
require_once 'AbstractAtom.php';

abstract class AbstractPagedLazyCollection extends AbstractAtom implements \Iterator {
	protected $_position = 0;
	protected $_page = 0;
	protected $_collection = null;

	abstract protected function _requestPage($page);
	abstract protected function _yieldObject($feed);

	protected function _requestFeed() {
		return $this->_requestPage($this->_page);
	}

	protected function _getCollection() {
		$this->_collection = $this['entry'];
	}

	protected function _hasNextPage() {
		return count($this['link@rel=next']) > 0; 
	}

	protected function _loadNextPage() {
		$this->_page = $this['link@rel=next'][0]['href'];
		$this->_releaseFeed();
		$this->_collection = array_merge($this->_collection, $this['entry']);
	}

	/* Iterator implementation */
	public function current(){
		return $this->_yieldObject( $this->_collection[$this->_position] );
	}

	public function key(){
		return $this->_position;
	}

	public function next(){
		$this->_position++;
		if ($this->_position == 100 && $this->_hasNextPage()) $this->_loadNextPage();
	}

	public function rewind(){
		$this->_position = 0;
	}

	public function valid(){
		if ($this->_collection === null) $this->_getCollection();
		return $this->_position < count($this->_collection);
	}

}
