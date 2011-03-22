<?php
namespace YandexFotkiAPI;
require_once 'API.php';

/* Meat goes here */
abstract class AbstractAtom implements \ArrayAccess {
	protected $_root;
	protected $_feed = null;
	protected $_evaluated = false;

	abstract protected function _requestFeed();

	protected function _normalizeField($fieldName, $fieldValue){
		if (count($fieldValue) == 0) return null;
		switch ($fieldName) {
			case 'id':	    return strval($fieldValue[0]);
			case 'author':      return strval($fieldValue[0]->name);
			case 'title':       return strval($fieldValue[0]);
			case 'summary':     return strval($fieldValue[0]);
			case 'content':     return $fieldValue[0];
			case 'updated':     return strptime((string)$fieldValue[0], "%Y-%m-%dT%H:%M:%SZ");
			case 'published':   return strptime((string)$fieldValue[0], "%Y-%m-%dT%H:%M:%SZ");
			case 'protected':   foreach($fieldValue[0]->attributes() as $k => $v) if ($k == 'value') return $v == 'true'; return null;
			case 'image-count': foreach($fieldValue[0]->attributes() as $k => $v) if ($k == 'value') return intval($v); return null;
			default:	    return $fieldValue;
		}
	}

	protected function _releaseFeed() {
		$this->_feed = null;
		$this->_evaluated = false;
	}

	protected function _evaluate() {
		if ($this->_feed === null)
			$this->_feed = $this->_requestFeed();
		$this->_parse();
		$this->_evaluated = true;
		if (method_exists($this, '_instance_evaluated')) $this->_instance_evaluated();
	}

	protected function _parse() {
		if (is_string($this->_feed))
			$this->_feed = simplexml_load_string($this->_feed);
		$this->_root = $this->_feed->getName();
	}

	public function setFeed($feed) {
		$this->_feed = $feed;
		$this->_evaluate = false;
	}

	public function getParentId() {
		$parent = $this['link@rel=album'];
		if ($parent !== null) {
			$pp = split('/', $parent[0]['href']);
			$parent = 'urn:yandex:fotki:'.implode(':', array_slice($pp, count($pp)-4, 3));
		}
		return $parent;
	}

	/* ArrayAccess implementation */

	public function offsetSet($offset, $value) {
		throw new Exception('Cannot change this kind of entity.');
	}

	public function offsetExists($offset) {
		return ($this[$offset]) == null;
	}

	public function offsetUnset($offset) {
		throw new Exception('Trying to delete property.');
	}

	public function offsetGet($offset) {
		if (!$this->_evaluated) $this->_evaluate();
		$res     = array();
		$ns      = '';
		$attr    = '';
		$attrval = '';
		if (strchr($offset, ':') !== FALSE) list($ns, $offset) = split(':', $offset);
		if (strchr($offset, '@') !== FALSE) {
			list($offset, $attr) = split('@', $offset);
			list($attr, $attrval) = split('=', $attr);
		}

		foreach ($this->_feed->children($ns, (bool)$ns) as $child)
			if ($child->getName() == $offset) {
				if ($attr) { if ($child[$attr] == $attrval) $res[] = $child; }
				else $res[] = $child;
			}
	
		return $this->_normalizeField($offset, $res);
	}

	public function commit() {
		if (!$this->_evaluated) $this->_evaluate();
		$this->_api->request( API::PUT, $this['link@rel=self'][0]['href'], $this->_feed->asXML() );
	}

}
