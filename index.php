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
use Google_Client;
use Google_Service_Sheets;
use stdClass;

/**
 * StatsCollector class.
 */
class StatsCollector {

	/**
	 * Instance variable.
	 *
	 * @since 1.0.0
	 *
	 * @var null|StatsCollector
	 */
	private static $instance = null;

	/**
	 * Get class instance.
	 *
	 * @return StatsCollector
	 * @since 1.0.0
	 *
	 */
	public static function getInstance(): StatsCollector {

		if ( ! self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;

	}

	/**
	 * Class constructor.
	 *
	 * @since 1.0.0
	 */
	private function __construct() {
		require __DIR__ . '/vendor/autoload.php';
	}

	/**
	 * Fetch plugin stats via WordPress API.
	 *
	 * @param  string  $plugin
	 *
	 * @return stdClass
	 * @throws Exception
	 * @since 1.0.0
	 *
	 */
	private function fetchStats( string $plugin ): stdClass {

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
	 * Returns an authorized API client.
	 * @return Google_Client the authorized client object
	 * @throws Exception
	 */
	private function getClient(): Google_Client {

		$client = new Google_Client();
		$client->setApplicationName( 'WordPress Stats Collector' );
		$client->setScopes( Google_Service_Sheets::SPREADSHEETS_READONLY );
		$client->setAuthConfig( 'credentials.json' );
		$client->setAccessType( 'offline' );
		$client->setPrompt( 'select_account consent' );

		// Load previously authorized token from a file, if it exists.
		// The file token.json stores the user's access and refresh tokens, and is
		// created automatically when the authorization flow completes for the first
		// time.
		$tokenPath = 'token.json';
		if ( file_exists( $tokenPath ) ) {
			$accessToken = json_decode( file_get_contents( $tokenPath ), true );
			$client->setAccessToken( $accessToken );
		}

		// If there is no previous token, or it's expired.
		if ( $client->isAccessTokenExpired() ) {
			// Refresh the token if possible, else fetch a new one.
			if ( $client->getRefreshToken() ) {
				$client->fetchAccessTokenWithRefreshToken( $client->getRefreshToken() );
			} else {
				// Request authorization from the user.
				$authUrl = $client->createAuthUrl();
				printf( "Open the following link in your browser:\n%s\n", $authUrl );
				print 'Enter verification code: ';
				$authCode = trim( fgets( STDIN ) );

				// Exchange authorization code for an access token.
				$accessToken = $client->fetchAccessTokenWithAuthCode( $authCode );
				$client->setAccessToken( $accessToken );

				// Check to see if there was an error.
				if ( array_key_exists( 'error', $accessToken ) ) {
					throw new Exception( join( ', ', $accessToken ) );
				}
			}
			// Save the token to a file.
			if ( ! file_exists( dirname( $tokenPath ) ) ) {
				mkdir( dirname( $tokenPath ), 0700, true );
			}

			file_put_contents( $tokenPath, json_encode( $client->getAccessToken() ) );
		}

		return $client;

	}

	/**
	 * Run collector.
	 *
	 * @return void
	 * @since 1.0.0
	 *
	 */
	public function run() {

		$ratings = $this->fetchStats( 'forminator' );

		// Get the API client and construct the service object.
		try {
			$client  = $this->getClient();
			$service = new Google_Service_Sheets( $client );
		} catch ( Exception $e ) {
			$error = $e->getMessage();
		}

		// Prints the names and majors of students in a sample spreadsheet:
		// https://docs.google.com/spreadsheets/d/1Y6GgKdtWPGP4QFIls059qUrVEuJvjUtRWHPqHQy7EJg/edit
		$spreadsheetId = '1Y6GgKdtWPGP4QFIls059qUrVEuJvjUtRWHPqHQy7EJg';
		$range         = 'Class Data!A2:E';
		$response      = $service->spreadsheets_values->get( $spreadsheetId, $range );
		$values        = $response->getValues();

		if ( empty( $values ) ) {
			print "No data found.\n";
		} else {
			print "Name, Major:\n";
			foreach ( $values as $row ) {
				// Print columns A and E, which correspond to indices 0 and 4.
				printf( "%s, %s\n", $row[0], $row[4] );
			}
		}

		foreach ( $ratings as $stars => $count ) {
			$a = 1;
		}

	}
}

StatsCollector::getInstance()->run();
