<?php

function is_user_logged_in() {
	global $__test_logged_in;
	return $__test_logged_in;
}

function wp_get_current_user() {
	return new class {
		public function has_cap( $cap ) {
			return true;
		}
	};
}

function wp_login_url() {
	return 'https://example.com/login';
}

function apply_filters( $filter, $value ) {
	return $value;
}

//wp_die( $error_codes[ $response->getStatusCode() ], $response->getStatusCode() . $response->getStatusCode(), [
//				'response'  => $response->getContent(),
//				'back_link' => true
//			] );
function wp_die( $message, $title, $args = [] ) {
	throw new Exception( $message . ': '. json_encode($args), $title );
}

function wp_redirect( $url, $status = 302) {
	//ignore it
}