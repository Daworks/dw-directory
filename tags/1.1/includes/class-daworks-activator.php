<?php

/**
 * Fired during plugin activation
 *
 * @link       http://daworks.org
 * @since      1.0.0
 *
 * @package    Daworks
 * @subpackage Daworks/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Daworks
 * @subpackage Daworks/includes
 * @author     디자인아레테 <dhlee@daworks.org>
 */
class Daworks_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */

	public static function activate() {

	}

	public function install_plugins() {
		global $wpdb;
		$wpdb->show_errors();
		global $daworks_db_version;		
		$daworks_db_version = "1.0";
		$table_name = $wpdb->prefix . "dw_directory";
		$table_name2 = $wpdb->prefix . "dw_directory_category";
		$charset_collate = $wpdb->get_charset_collate();

		$query = "CREATE TABLE $table_name (
					  num int(11) NOT NULL AUTO_INCREMENT,
					  c_no int(11) NOT NULL DEFAULT '0',
					  ref int(11) NOT NULL DEFAULT '0',
					  ref_n int(11) NOT NULL DEFAULT '0',
					  lev int(11) NOT NULL DEFAULT '0',
					  step float NOT NULL DEFAULT '0',
					  name varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
					  email varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
					  homepage varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
					  title varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
					  content text COLLATE utf8mb4_unicode_ci NOT NULL,
					  admin_ok int(11) NOT NULL DEFAULT '0',
					  Re_quesion int(11) NOT NULL DEFAULT '0',
					  img varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
					  request_new_cat varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
					  indate date NOT NULL DEFAULT '0000-00-00',
					  PRIMARY KEY (num)
					) $charset_collate;";

		$query2 = "CREATE TABLE $table_name2 (
					  c_no int(11) NOT NULL AUTO_INCREMENT,
					  c_title text COLLATE utf8mb4_unicode_ci NOT NULL,
					  ref int(11) NOT NULL DEFAULT '0',
					  ref_n int(11) NOT NULL DEFAULT '0',
					  lev int(11) NOT NULL DEFAULT '0',
					  step float NOT NULL DEFAULT '0',
					  PRIMARY KEY (c_no)
					) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $query );
		dbDelta( $query2 );

		add_option ('daworks_directory_db_version', $daworks_db_version);
	}


}
