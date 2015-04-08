<?php
class gl_crowdrise_charity_api
{
	public function _construct()
	{
		$this->username = '';
		$this->password = '';

		$this->api_key = '';
		$this->api_token = '';

		$this->url_charity_count = "https://www.crowdrise.com/api/get_charity_donation_count/{time_from}/{time_to}/?api_key={api_key}&api_token={api_token}";
	}

	public function get_charity_donation_count($time_from = 0, $time_to = 0)
	{
		if(empty($time_to))
		{
			$time_to = time();
		}

		$charity_count = 0;

		if(! empty($time_from))
		{
			$binds = array();
			$binds['{time_from}'] = $time_from;
			$binds['{time_to}'] = $time_to;
			$binds['{api_key}'] = $this->api_key;
			$binds['{api_token}'] = $this->api_token;

			$url = str_replace(array_keys($binds), array_values($binds), $this->url_charity_count);

			$response = $this->gl_call_endpoint($url);

			if(! empty($response))
			{
				$result = json_decode($response);

				if(! empty($result['result']))
				{
					$charity_count = preg_replace('~[^0-9]~', '', $result['result']);
				}
			}
		}

		return $charity_count;
	}

	public function gl_call_endpoint($url = "", $post_vars = array(), &$error)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_VERBOSE, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_USERPWD, "{$this->username}:{$this->password}");
		curl_setopt($ch, CURLOPT_URL, $url);

		$response = curl_exec($ch);
		$error = curl_error($ch);

		return $response;
	}

	public function _destruct()
	{
	}
}
?>