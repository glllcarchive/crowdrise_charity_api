<?php
class gl_crowdrise_charity_api
{
	public $username;
	public $password;
	public $api_key;
	public $api_token;
	public $api_timeout;
	public $url_charity_count;
	public $url_charity_donations;
	public $error_stack;
	public $flag_debug;

	public function __construct()
	{
		$this->username = '';
		$this->password = '';

		$this->api_key = '';
		$this->api_token = '';

		$this->api_timeout = 0;

		$this->error_stack = array();
		$this->flag_debug = false;

		$this->url_charity_count = "https://www.crowdrise.com/api/get_charity_donation_count/{time_from}/{time_to}/?api_key={api_key}&api_token={api_token}";
		$this->url_charity_donations = "https://www.crowdrise.com/api/get_charity_donations/{time_from}/{time_to}/{limit}/{offset}?api_key={api_key}&api_token={api_token}";
	}

	/**
	* get_total_donation_amount
	*
	* aggregates the total amount of donations for a given time period
	*
	* @param string timestamp $time_from
	* @param string timestamp $time_to
	*/
	public function get_total_donation_amount($time_from = '', $time_to = '')
	{
		if(empty($time_to))
		{
			$time_to = time();
		}

		//total count of donations requested wihch should match $donation_count if a total donation amount is desired.
		$the_total_count = 0;

		/**
		* The API will throw a 500/timeout if too much is requested at once. Safely
		* chunk out the total number of dontations
		*/
		$donation_count = $this->get_donation_count($time_from, $time_to);

		if(! empty($donation_count))
		{
			$chunk_rate = 5000;   //the total number of records to request crowdrise at one time
			$chunk_offset = 0;
			$chunk_total = 1;     //how many times to loop and make a get_charity_donations request

			//if the total donations exceed the set chunk limit
			if($donation_count > $chunk_rate)
			{
				//find out how many chunks/requests are needed
				$chunk_total = ceil($donation_count / $chunk_rate);
			}

			//set a counter for the loop
			$chunk_current = $chunk_total;

			$chunk_current_offset = $chunk_offset;

			//initialise the stack for total donation amount
			$donations_total = array();

			//start the request process
			while(! empty($chunk_current))
			{
				$donations = $this->get_donations($time_from, $time_to, $chunk_rate, $chunk_current_offset);

				if(! empty($donations))
				{
					$the_total_count += count($donations['result'][0]);

					foreach($donations['result'][0] as $donation_result)
					{
							$donations_total[$chunk_current] += $donation_result['donation_amount']; //the gross donation amount
					}
				}
				else
				{
					$this->error_stack[] = "skipped offset $chunk_current_offset";
				}

				//bump the offset up a notch
				$chunk_current_offset += $chunk_rate;

				//work our way down in the request chunks
				$chunk_current--;
			}
		}

		$total = 0;

		foreach($donations_total as $donation_chunk_key => $donation_chunk_amount)
		{
			$total += $donation_chunk_amount;
		}

		return $total;
	}

	public function get_donations($time_from = 0, $time_to = 0, $limit = 0, $offset = 0)
	{
		$result = array();

		if(empty($time_to))
		{
			$time_to = time();
		}

		if(! empty($time_from))
		{
			$binds = array();
			$binds['{time_from}'] = $time_from;
			$binds['{time_to}'] = $time_to;
			$binds['{api_key}'] = $this->api_key;
			$binds['{api_token}'] = $this->api_token;
			$binds['{limit}'] = $limit;
			$binds['{offset}'] = $offset;

			$url = str_replace(array_keys($binds), array_values($binds), $this->url_charity_donations);

			$response = $this->gl_call_endpoint($url);

			if(! empty($response))
			{
				$result = json_decode($response, true);

				if(empty($result['result']))
				{
					$result = false;
				}
			}
		}

		return $result;
	}

	public function get_donation_count($time_from = 0, $time_to = 0)
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
				$result = json_decode($response, true);

				if(! empty($result['result']))
				{
					$charity_count = preg_replace('~[^0-9]~', '', $result['result'][0]['donation_count']);
				}
			}
		}

		return $charity_count;
	}

	public function gl_call_endpoint($url = "", $post_vars = array(), &$error)
	{
		$ch = curl_init();

		if(! empty($this->username) && ! empty($this->password))
		{
			curl_setopt($ch, CURLOPT_USERPWD, "{$this->username}:{$this->password}");
		}

		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLOPT_VERBOSE, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $this->api_timeout);
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->api_timeout);

		curl_setopt($ch, CURLOPT_URL, $url);

		$response = curl_exec($ch);
		$error = curl_error($ch);

		return $response;
	}

	public function __destruct()
	{
	}
}
?>