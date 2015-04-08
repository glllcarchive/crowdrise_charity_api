# crowdrise_charity_api
simple class for crowdrise charity api

example:
include('class.gl_crowdrise_charity_api.php');

$charity = new gl_crowdrise_charity_api();

$charity->api_key = '';
$charity->api_token = '';

$time_from = strtotime('1980-01-01');

$donation_count = $charity->get_charity_donation_count($time_from);

echo $donation_count;