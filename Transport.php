<?php
namespace YandexFotkiAPI;
require_once 'API.php';
require_once 'Encryptor.php';

class Transport {
    private $token = null;
    
    public function request($method, $url, $params = null) {
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$headers = array();
	switch($method){
		case API::POST:
			curl_setopt($ch, CURLOPT_POST, 1);
            		if ($params != null) curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
			break;
		case API::PUT:
			$apiPutStr = tmpfile();
			fwrite($apiPutStr, (string)$params);
			fseek($apiPutStr, 0);
			$headers[] = 'Content-Type: application/atom+xml; charset=utf-8; type=entry';
			curl_setopt($ch, CURLOPT_PUT, 1);
			curl_setopt($ch, CURLOPT_INFILE, $apiPutStr);
			curl_setopt($ch, CURLOPT_INFILESIZE, strlen($params));
			break;
		case API::DELETE:
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
			break;
        	default:
            		curl_setopt($ch, CURLOPT_HTTPGET, 1);
	}
        if ($this->token) $headers[] = 'Authorization: FimpToken realm="fotki.yandex.ru", token="'.$this->token.'"';
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	$result = curl_exec($ch);
	if ($method == API::PUT) fclose($apiPutStr);
	if (curl_errno($ch)) {
    	    $error = curl_error($ch);
    	    curl_close($ch);
	    throw new APIException($error);
	}
        curl_close($ch);
        return $result;
    }
    
    public function authorize($username, $password) {
        $response = $this->request(API::GET, 'http://auth.mobile.yandex.ru/yamrsa/key/');
	$response = new \SimpleXMLElement($response);
        $request_id = $response->request_id;
        $rsa_key = $response->key;
        
        $credentials = sprintf('<credentials login="%s" password="%s"/>', $username, $password);
        $credentials = Encryptor::encrypt($rsa_key, $credentials);
        $response = $this->request(API::POST,
                                   'http://auth.mobile.yandex.ru/yamrsa/token/', 
                                   array('request_id'  => $request_id, 
				   'credentials' => $credentials));
	$response = new \SimpleXMLElement($response);
        $this->token = $response->token;
    }
    
}
