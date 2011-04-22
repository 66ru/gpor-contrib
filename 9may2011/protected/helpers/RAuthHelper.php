<?php

abstract class RAuthHelper
{
	static public $session_user_id = 'gpor_userid';

	static function getRequestHash() {
		if(!isset($_GET["hash"]))
			return false;

		$request_hash = $_GET["hash"];

		if(!is_array($request_hash))
			$request_hash = array($request_hash);

		$request_hash = array_map("strval", $request_hash);

		foreach($request_hash as $item) {
			list($hash, $secret_hash) = explode("|", $item);

			if($secret_hash !== self::getSecretHash($hash))
				continue;

			return $hash;
		}

		return false;
	}

	static function getCurrentHash() {
		if(!Yii::app()->request->cookies["rauthhash"]->value)
			return false;

		return Yii::app()->request->cookies["rauthhash"]->value;
	}

	static function getSecretHash($hash) {
		$hash = strval($hash);
		
		return md5($hash . Yii::app()->params['gpor_secret_key']);
	}

	static function setCookieHash($hash, $remember_me = false) {
		if($remember_me) {
			$time = time() + (365 * 24 * 3600);
		} else {
			$time = false;
		}
		$cookie = new CHttpCookie("rauthhash", $hash);
		$cookie->expire = $time;
		Yii::app()->request->cookies["rauthhash"] = $cookie;
	}

	static function unsetCookieHash() {
		unset(Yii::app()->request->cookies["rauthhash"]);
	}

	static function sendRequest($action) {
		$action = strval($action);
		$hash = self::getCurrentHash();

		if($hash === false)
			return false;

		$postdata = array(
			"action" => $action,
			"hash"   => $hash . "|" . self::getSecretHash($hash)
		);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, Yii::app()->params['gpor_server_url']);
		curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
		curl_setopt($ch, CURLOPT_TIMEOUT, 5);

		$response = curl_exec($ch);
		if(curl_errno($ch)) {
			return array(
				"answer" => array(),
				"error"  => array(
					"curl_error: " . curl_error($ch)
				)
			);
		}

		curl_close($ch);

		$response = @unserialize($response);
		$response = (array)$response;

		if(!isset($response["answer"]))
			$response["answer"] = array();

		if(!isset($response["error"]))
			$response["error"] = array();

		return $response;
	}

	static function getResponse($action) {
		$response = self::sendRequest($action);
		return $response["answer"];
	}

	static function saveUid($user_id) {
		Yii::app()->session[self::$session_user_id] = $user_id;
	}

	static function getUid() {
		return Yii::app()->session[self::$session_user_id];
	}
}