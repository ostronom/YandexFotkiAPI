<?php
namespace YandexFotkiAPI;
require_once 'API.php';
require_once 'AbstractPagedLazyCollection.php';

class AlbumCollection extends AbstractPagedLazyCollection {
	protected $_api;

	protected function _requestPage($page = null) {
		return $this->_api->request(API::GET, $page ? $page : '/users/{user}/albums/');
	}

	protected function _yieldObject($feed) {
		$object = $this->_api->getAlbum();
		$object->setFeed($feed);
		return $object;
	}

	public function __construct($api) {
		$this->_api = $api;
	}
}
