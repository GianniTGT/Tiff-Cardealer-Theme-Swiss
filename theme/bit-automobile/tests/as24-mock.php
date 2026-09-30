<?php
/**
 * Attrappe von AutoScout24 – NUR fuer lokale Tests, nie auf dem echten Server.
 *
 * Faengt alle HTTP-Aufrufe an *.autoscout24.* ab und antwortet wie der echte
 * Server: Token-Anmeldung und Inseratsliste (aus demo/as24-demo.json,
 * IDs mit «probe-» davor, damit sie nicht mit den Demo-Fahrzeugen kollidieren).
 * Es geht dabei keine einzige Anfrage ins Internet.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function bit_as24_mock_response( $pre, $args, $url ) {
	$host = (string) wp_parse_url( $url, PHP_URL_HOST );
	if ( ! str_contains( $host, 'autoscout24' ) ) {
		return $pre;
	}
	$json = function ( $data, $code = 200 ) {
		return array(
			'headers'  => array( 'content-type' => 'application/json' ),
			'body'     => wp_json_encode( $data ),
			'response' => array( 'code' => $code, 'message' => 200 === $code ? 'OK' : 'Error' ),
			'cookies'  => array(),
			'filename' => null,
		);
	};
	$path = (string) wp_parse_url( $url, PHP_URL_PATH );
	if ( str_ends_with( $path, '/oauth/token' ) ) {
		$ok = ( $args['body']['client_secret'] ?? '' ) === 'test-secret';
		return $ok ? $json( array( 'access_token' => 'mock-token', 'expires_in' => 86400 ) ) : $json( array( 'error' => 'invalid_client' ), 401 );
	}
	if ( str_contains( $path, '/listings' ) ) {
		if ( ( $args['headers']['Authorization'] ?? '' ) !== 'Bearer mock-token' ) {
			return $json( array( 'error' => 'unauthorized' ), 401 );
		}
		$items = bit_as24_extract_items( json_decode( (string) file_get_contents( BIT_DIR . '/demo/as24-demo.json' ), true ) );
		foreach ( $items as &$it ) {
			$it['id'] = 'probe-' . $it['id'];
			unset( $it['_bitFlag'] );
		}
		unset( $it );
		return $json( array( 'content' => $items, 'totalElements' => count( $items ) ) );
	}
	return $json( array( 'error' => 'not found' ), 404 );
}
