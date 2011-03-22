<?php
namespace YandexFotkiAPI;
require_once 'API.php';
require_once 'AbstractPagedLazyCollection.php';

class PhotoCollection extends AbstractPagedLazyCollection {
	protected $_api;
	protected $_album_id;

	protected function _requestPage($page = null) {
		return $this->_api->request(API::GET, $page ? $page : '/users/{user}/album/'.$this->_album_id.'/photos/');
	}

	protected function _yieldObject($feed) {
		$object = $this->_api->getAlbum();
		$object->setFeed($feed);
		return $object;
	}

	public function __construct($api, $album_id) {
		$this->_api = $api;
		$this->_album_id = $album_id;
	}
}
