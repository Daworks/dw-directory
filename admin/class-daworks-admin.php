<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/Daworks/dw-directory
 * @since      1.0.0
 *
 * @package    Daworks
 * @subpackage Daworks/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for the admin-specific
 * stylesheet and JavaScript.
 *
 * @package    Daworks
 * @subpackage Daworks/admin
 * @author     디자인아레테 <dhlee@daworks.org>
 */
class Daworks_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param    string $plugin_name       The name of this plugin.
	 * @param    string $version           The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'css/daworks-admin.css',
			array(),
			$this->version,
			'all'
		);
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'js/daworks-admin.js',
			array( 'jquery' ),
			$this->version,
			true
		);

		// Localize script with nonce and ajax URL.
		wp_localize_script(
			$this->plugin_name,
			'daworks_admin',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'daworks_ajax_nonce' ),
			)
		);
	}

	/**
	 * Add admin menu pages.
	 *
	 * @since    1.0.0
	 */
	public function add_admin_menu() {
		add_menu_page(
			__( '디렉토리 서비스', 'daworks' ),
			__( '디렉토리 서비스', 'daworks' ),
			'manage_options',
			'dw-directory-standby-slug',
			array( $this, 'render_admin_page' ),
			'dashicons-list-view',
			60
		);

		add_submenu_page(
			'dw-directory-standby-slug',
			__( '등록 대기 관리', 'daworks' ),
			__( '등록 대기 관리', 'daworks' ),
			'manage_options',
			'dw-directory-standby-slug',
			array( $this, 'render_admin_page' )
		);

		add_submenu_page(
			'dw-directory-standby-slug',
			__( '카테고리 관리', 'daworks' ),
			__( '카테고리 관리', 'daworks' ),
			'manage_options',
			'dw-directory-manage-cat-slug',
			array( $this, 'render_admin_subpage_category' )
		);

		add_submenu_page(
			'dw-directory-standby-slug',
			__( '아이템 관리', 'daworks' ),
			__( '아이템 관리', 'daworks' ),
			'manage_options',
			'dw-directory-manage-item-slug',
			array( $this, 'render_admin_subpage_item' )
		);
	}

	/**
	 * Render the main admin page.
	 *
	 * @since    1.0.0
	 */
	public function render_admin_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'daworks' ) );
		}
		require DAWORKS_PLUGIN_DIR . 'admin/partials/daworks-admin-main.php';
	}

	/**
	 * Render the category management page.
	 *
	 * @since    1.0.0
	 */
	public function render_admin_subpage_category() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'daworks' ) );
		}
		require DAWORKS_PLUGIN_DIR . 'admin/partials/daworks-admin-category.php';
	}

	/**
	 * Render the item management page.
	 *
	 * @since    1.0.0
	 */
	public function render_admin_subpage_item() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'daworks' ) );
		}
		require DAWORKS_PLUGIN_DIR . 'admin/partials/daworks-admin-item.php';
	}
}
