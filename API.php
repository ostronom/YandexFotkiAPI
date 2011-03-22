<?php
namespace YandexFotkiAPI;
require_once 'Transport.php';
require_once 'AbstractAtom.php';
require_once 'AlbumCollection.php';
require_once 'Album.php';
require_once 'PhotoCollection.php';
require_once 'Photo.php';

class APIException extends \Exception {}

class API {
	const GET    = 1;
	const POST   = 2;
	const PUT    = 3;
	const DELETE = 4;

	const API_URL = 'http://api-fotki.yandex.ru/api';

	private $transport;
	private $user;
	private $password;

	public function request($method, $url, $params = null) {
		$url = preg_replace('/\{user\}/', $this->user, $url);
		if (strtolower(substr($url, 0, 7)) != 'http://') $url = $this::API_URL . $url;
		return $this->transport->request($method, $url, $params);
	}

	public function __construct() {
		$this->transport = new Transport();
	}

	public function setUser($user) { 
		$this->user = $user; 
		return $this; 
	}

	public function setPassword($password) {
		$this->password = $password;
		return $this;
	}

	public function authorize() {
		$this->transport->authorize($this->user, $this->password);
		return $this;
	}

	public function getAlbumsCollection() {
		return new AlbumCollection($this);
	}

	public function getAlbum($id = null) {
		return new Album($this, $id);
	}

	public function getPhotoCollection($album_id) {
		return new PhotoCollection($this, $album_id);
	}

	public function getPhoto($photo_id) {
		return new Photo($this, $photo_id);
	}
}