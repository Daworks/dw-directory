<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://daworks.org
 * @since      1.0.0
 *
 * @package    Daworks
 * @subpackage Daworks/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Daworks_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Daworks_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/daworks-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Daworks_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Daworks_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/daworks-admin.js', array( 'jquery' ), $this->version, false );

	}

	public function add_admin_menu(){
		add_menu_page(
			'디렉토리 서비스', 
			'디렉토리 서비스', 
			'manage_options', 
			'dw-directory-standby-slug',
			array($this,'render_admin_page'),
			'',
			60
		);
		
		add_submenu_page( 
			'dw-directory-standby-slug', 
			'등록 대기 관리', 
			'등록 대기 관리',
		    'manage_options', 
		    'dw-directory-standby-slug',
		    array($this,'render_admin_page')
		);

		add_submenu_page( 
			'dw-directory-standby-slug', 
			'카테고리 관리', 
			'카테고리 관리',
		    'manage_options', 
		    'dw-directory-manage-cat-slug',
		    array($this,'render_admin_subpage_category')
		);
		add_submenu_page( 
			'dw-directory-standby-slug', 
			'아이템 관리', 
			'아이템 관리',
		    'manage_options', 
		    'dw-directory-manage-item-slug',
		    array($this,'render_admin_subpage_item')
		);

	}

	public function render_admin_page(){
		require plugin_dir_path(__FILE__) . 'partials/daworks-admin-main.php';
	}

	public function render_admin_subpage_category(){
		require plugin_dir_path(__FILE__) . 'partials/daworks-admin-category.php';
	}

	public function render_admin_subpage_item(){
		require plugin_dir_path(__FILE__) . 'partials/daworks-admin-item.php';
	}

}
