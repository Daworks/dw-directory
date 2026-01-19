<?php
/**
 * Fired during plugin activation
 *
 * @link       https://github.com/Daworks/dw-directory
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
	 * Plugin activation handler.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		// Flush rewrite rules
		flush_rewrite_rules();
	}

	/**
	 * Create database tables.
	 *
	 * @since    1.0.0
	 */
	public static function install_tables() {
		global $wpdb;

		$daworks_db_version = '2.0';
		$charset_collate    = $wpdb->get_charset_collate();

		$table_directory = $wpdb->prefix . 'dw_directory';
		$table_category  = $wpdb->prefix . 'dw_directory_category';

		$sql_directory = "CREATE TABLE $table_directory (
			num int(11) NOT NULL AUTO_INCREMENT,
			c_no int(11) NOT NULL DEFAULT 0,
			ref int(11) NOT NULL DEFAULT 0,
			ref_n int(11) NOT NULL DEFAULT 0,
			lev int(11) NOT NULL DEFAULT 0,
			step float NOT NULL DEFAULT 0,
			name varchar(50) NOT NULL DEFAULT '',
			email varchar(100) NOT NULL DEFAULT '',
			homepage varchar(255) NOT NULL DEFAULT '',
			title varchar(255) NOT NULL DEFAULT '',
			content text NOT NULL,
			admin_ok int(11) NOT NULL DEFAULT 0,
			Re_quesion int(11) NOT NULL DEFAULT 0,
			img varchar(255) NOT NULL DEFAULT '',
			request_new_cat varchar(255) DEFAULT NULL,
			indate date NOT NULL,
			PRIMARY KEY (num),
			KEY c_no (c_no),
			KEY ref (ref),
			KEY admin_ok (admin_ok)
		) $charset_collate;";

		$sql_category = "CREATE TABLE $table_category (
			c_no int(11) NOT NULL AUTO_INCREMENT,
			c_title text NOT NULL,
			ref int(11) NOT NULL DEFAULT 0,
			ref_n int(11) NOT NULL DEFAULT 0,
			lev int(11) NOT NULL DEFAULT 0,
			step float NOT NULL DEFAULT 0,
			PRIMARY KEY (c_no),
			KEY ref (ref),
			KEY lev (lev)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql_directory );
		dbDelta( $sql_category );

		add_option( 'daworks_directory_db_version', $daworks_db_version );
	}
}
