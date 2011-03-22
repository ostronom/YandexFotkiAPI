<?php
namespace YandexFotkiAPI;
require_once 'API.php';

class Album extends AbstractAtom {
	protected $_api;
	protected $_id = null;

	protected function _requestFeed() {
		return $this->_api->request(API::GET, '/users/{user}/album/'.$this->_id.'/');
	}

	protected function _instance_evaluated() {
		$id = array_pop(split(':', $this['id']));
		$this->_id = $id;
	}

	public function __construct($api, $id = null) {
		$this->_api = $api;
		if ($id !== null) $this->_id = $id;
	}

	public function getPhotoCollection() {
		return $this->_api->getPhotoCollection($this->_id);
	}

	public function delete(){
		$this->_api->request(API::DELETE, $this['link@rel=self'][0]['href']);
		$this->_releaseFeed();
	}

	public function offsetSet($offset, $value) {
		if (!$this->_evaluated) $this->_evaluate();
		switch($offset){
			case 'title': 
				$this->_feed->title = new \SimpleXMLElement(sprintf('<title>%s</title>', $value)); break;
			case 'summary':
				$this->_feed->summary = new \SimpleXMLElement(sprintf('<summary>%s</summary>', $value)); break;
			case 'f:password':
				$this['f:password'][0]['value'] = $value; break;
			default:
				throw new APIException('Trying to change read-only or non-existent property.');
		}
	}
}
