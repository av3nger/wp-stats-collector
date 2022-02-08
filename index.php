<?php

$curl = curl_init();

curl_setopt_array($curl, array(
	CURLOPT_URL => 'https://api.wordpress.org/plugins/info/1.0/forminator.json',
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_ENCODING => '',
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 0,
	CURLOPT_FOLLOWLOCATION => true,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => 'GET',
));

$response = curl_exec($curl);

curl_close($curl);

$response = json_decode( $response );

if ( ! isset( $response->ratings ) ) {
	return;
}

foreach ( $response->ratings as $stars => $count ) {
	$a = 1;
}
