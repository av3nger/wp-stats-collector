<?php
/**
 * StatsCollector class.
 *
 * @package StatsCollector
 *
 * @since 1.0.0
 */

namespace StatsCollector;

use Exception;
use stdClass;

/**
 * StatsCollector class.
 */
class StatsCollector {

	/**
	 * Fetch plugin stats via WordPress API.
	 *
	 * @since 1.0.0
	 *
	 * @param  string  $plugin
	 *
	 * @return stdClass
	 * @throws Exception
	 */
	private function fetch_stats( string $plugin ) : stdClass {

		$curl = curl_init();

		curl_setopt_array( $curl, array(
			CURLOPT_URL            => "https://api.wordpress.org/plugins/info/1.0/$plugin.json",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING       => '',
			CURLOPT_MAXREDIRS      => 10,
			CURLOPT_TIMEOUT        => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST  => 'GET',
		) );

		$response = curl_exec( $curl );

		curl_close( $curl );

		$response = json_decode( $response );

		if ( ! isset( $response->ratings ) ) {
			throw new Exception( "Rating data not available for plugin: $plugin", 400 );
		}

		return $response->ratings;

	}

	/**
	 * Run collector.
	 *
	 * @since 1.0.0
	 *
	 * @return void
	 */
	public function run() {

		$ratings = $this->fetch_stats( 'forminator' );

		foreach ( $ratings as $stars => $count ) {
			$a = 1;
		}

	}

}

$collector = new StatsCollector();
$collector->run();
