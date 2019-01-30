<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

global $wpdb;
define("__DWCAT__", $wpdb->prefix.'dw_directory_category');
define("__DWDIR__", $wpdb->prefix.'dw_directory');

class Daworks_Library {

	public function get_standby_list() {
		global $wpdb;

		$query = "SELECT * FROM ".__DWDIR__." WHERE admin_ok = %d ORDER BY indate ASC";

		$result = $wpdb->get_results($wpdb->prepare($query, 0), OBJECT);
		return $result;
	}

	public function get_cat_lev1() {
		global $wpdb;
		$query = "SELECT c_no, c_title FROM ".__DWCAT__." WHERE c_no = ref and lev = %d order by c_no asc";
		$cats = $wpdb->get_results($wpdb->prepare($query, 0), OBJECT);
		return $cats;
	}
}


?>
