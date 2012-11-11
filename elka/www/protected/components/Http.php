<?php
class Http
{
	var $error = '';
	var $bytes_recieved;
	var $timeout = 60;
	var $follow_redirect = 0;
	var $binary_transfer = 0;

	var $referer = '';

	var $receive_headers = 1;
	var $receive_headers_only = 0;
	var $received_headers = array();
	var $cookies = false;
	var $received_cookies = array();

	var $post_data = false;

	var $user_agent = false;

	var $proxy = false;

	function get($url) {

		$this->received_headers = array();

		if(!$this->user_agent) {

			$this->user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'User-Agent: Mozilla/5.0 (Windows; U; Windows NT 5.1; ru; rv:1.8.0.4) Gecko/20060508 Firefox/1.5.0.4';
		}

		$this->bytes_recieved = 0;

		$link = curl_init();

		curl_setopt($link, CURLOPT_URL, $url);
		curl_setopt($link, CURLOPT_TIMEOUT, $this->timeout);
        //curl_setopt($link, CURLOPT_MUTE, 1);
		curl_setopt($link, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($link, CURLOPT_FAILONERROR, 1);
		curl_setopt($link, CURLOPT_FOLLOWLOCATION, $this->follow_redirect);
		curl_setopt($link, CURLOPT_BINARYTRANSFER, $this->binary_transfer);

		curl_setopt($link, CURLOPT_REFERER, $this->referer);
		curl_setopt($link, CURLOPT_USERAGENT, $this->user_agent);

		if($this->proxy !== false) {

			curl_setopt($link, CURLOPT_PROXY, $this->proxy);
		}

		if($this->cookies !== false) {

			curl_setopt($link, CURLOPT_COOKIE, $this->cookies);
		}

        curl_setopt($link, CURLOPT_HEADER, $this->receive_headers);
		curl_setopt($link, CURLOPT_NOBODY, $this->receive_headers_only);

		if($this->post_data !== false) {

			curl_setopt($link, CURLOPT_POST, 1);
			curl_setopt($link, CURLOPT_POSTFIELDS, $this->post_data);
			$this->post_data = false;
		}

		if($result = curl_exec($link)) {

			$this->bytes_recieved = curl_getinfo($link, CURLINFO_SIZE_DOWNLOAD);

			if($this->receive_headers === 1) {

				$headers = substr($result, 0, strpos($result, "\r\n\r\n"));

				if($header_count = preg_match_all("/(.*?):\040(.*?)\r\n/i", $headers, $headers_temp)) {

					for($i = 0; $i < $header_count; $i++) {

						if(preg_match('#Set-Cookie#i', $headers_temp[1][$i])) {

							$tmp = preg_replace('#;.*#', '', $headers_temp[2][$i]);
							$tmp = explode('=', $tmp, 2);
							$this->received_cookies[$tmp[0]] = isset($tmp[1]) ? $tmp[1] : '';
						}

						else {

							$this->received_headers[$headers_temp[1][$i]] = $headers_temp[2][$i];
						}
					}

					unset($headers, $headers_temp, $headers_count, $i);
				}

				if($this->receive_headers_only === 0) {

					return substr($result, strpos($result, "\r\n\r\n") + 4, strlen($result));
				}
			}

			return $result;
		}

		else {

			$this->error = 'Class '.get_class($this).' error: ('.curl_errno($link).') '.curl_error($link);
			return false;
		}

		curl_close($link);
	}
}
?>