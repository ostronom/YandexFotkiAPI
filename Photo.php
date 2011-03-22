<?php
namespace YandexFotkiAPI;
require_once 'API.php';

class Photo extends AbstractAtom {
	protected $_api;
	protected $_id = null;

	protected function _requestFeed() {
		return $this->_api->request(API::GET, '/users/{user}/photo/'.$this->_id.'/');
	}

	protected function _instance_evaluated() {
		$id = array_pop(split(':', $this['id']));
		$this->_id = $id;
	}

	public function __construct($api, $id = null) {
		$this->_api = $api;
		if ($id !== null) $this->_id = $id;
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
			case 'f:xxx':
				$this['f:xxx'][0]['value'] = $value; break;
			case 'f:disable_comments':
				$this['f:disable_comments'][0]['value'] = $value; break;
			case 'f:hide_original':
				$this['f:hide_original'][0]['value'] = $value; break;
			case 'f:access':
				$this['f:access'][0]['value'] = $value; break;
			case 'link@rel=album':
				if ($value instanceof Album) $value = $value['link@rel=self'][0]['href'];
				$this['link@rel=album'][0]['href'] = $value;
				break;
			default:
				throw new APIException('Trying to change read-only or non-existent property.');
		}
	}
}
