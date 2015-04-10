# crowdrise_charity_api
simple class for the crowdrise charity api

## examples:

```php
include('class.gl_crowdrise_charity_api.php');

$charity = new gl_crowdrise_charity_api();

$charity->api_key   = '';
$charity->api_token = '';

$time_from = strtotime("2010-01-01 00:00:00");

$total_donation_amount = $charity->get_total_donation_amount($time_from);

echo number_format(round($total_donation_amount), 0);
```