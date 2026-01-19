<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/Daworks/dw-directory
 * @since      1.0.0
 *
 * @package    Daworks
 * @subpackage Daworks/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for the public-facing
 * stylesheet and JavaScript.
 *
 * @package    Daworks
 * @subpackage Daworks/public
 * @author     디자인아레테 <dhlee@daworks.org>
 */
class Daworks_Public {

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
	 * @param    string $plugin_name       The name of the plugin.
	 * @param    string $version           The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		wp_enqueue_style(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'css/daworks-public.css',
			array(),
			$this->version,
			'all'
		);

		wp_enqueue_style(
			$this->plugin_name . '-font-awesome',
			plugin_dir_url( __FILE__ ) . 'bower_components/font-awesome/css/font-awesome.min.css',
			array(),
			'4.7.0',
			'all'
		);
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script(
			$this->plugin_name,
			plugin_dir_url( __FILE__ ) . 'js/daworks-public.js',
			array( 'jquery' ),
			$this->version,
			true
		);
	}

	/**
	 * Get the first level categories for display.
	 *
	 * @since    1.0.0
	 * @return   string    HTML output for categories.
	 */
	public function get_first_cat() {
		global $wpdb, $post;

		$category = $wpdb->prefix . 'dw_directory_category';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$depth_root = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$category} WHERE lev = %d ORDER BY c_no ASC", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				0
			)
		);

		$tags = '<ul class="depth-root">';

		foreach ( $depth_root as $single ) {
			$permalink = get_permalink( $post->ID );
			$cat_url   = add_query_arg(
				array(
					'c_no' => absint( $single->c_no ),
					'ref'  => absint( $single->ref ),
					'lev'  => absint( $single->lev ),
				),
				$permalink
			);

			$tags .= '<li>';
			$tags .= '<a href="' . esc_url( $cat_url ) . '">' . esc_html( $single->c_title ) . '</a>';
			$tags .= '<ul class="sub-category">';

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$child_cats = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$category} WHERE ref = %d AND lev > 0", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					$single->c_no
				)
			);

			foreach ( $child_cats as $child_cat ) {
				if ( 1 === (int) $child_cat->lev ) {
					$title_parts = explode( '>', $child_cat->c_title );
					$title       = isset( $title_parts[1] ) ? trim( $title_parts[1] ) : '';
					$child_url   = add_query_arg(
						array(
							'c_no' => absint( $child_cat->c_no ),
							'ref'  => absint( $child_cat->ref ),
							'lev'  => absint( $child_cat->lev ),
						),
						$permalink
					);
					$tags .= '<li><a href="' . esc_url( $child_url ) . '">' . esc_html( $title ) . '</a></li>';
				}
			}

			$tags .= '</ul>';
			$tags .= '</li>';
		}

		$tags .= '</ul>';

		return $tags;
	}

	/**
	 * Get sub-categories for a given category.
	 *
	 * @since    1.0.0
	 * @param    int $c_no    Category number.
	 * @param    int $ref     Reference number.
	 * @param    int $lev     Level.
	 * @return   string       HTML output.
	 */
	public function get_sub_category( $c_no, $ref, $lev ) {
		global $wpdb, $post;

		$t_category  = $wpdb->prefix . 'dw_directory_category';
		$t_directory = $wpdb->prefix . 'dw_directory';
		$ref_n       = isset( $_GET['ref_n'] ) ? absint( $_GET['ref_n'] ) : 0;

		// Get sub-categories.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$sub_cats = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$t_category} WHERE ref = %d AND lev = %d ORDER BY c_title ASC", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				absint( $ref ),
				absint( $lev ) + 1
			)
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$upper_category_name = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT c_title FROM {$t_category} WHERE c_no = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				absint( $ref )
			)
		);

		$tags  = '<h3><a href="#" onclick="history.back(); return false;">' . esc_html( $upper_category_name ) . '</a></h3>';
		$tags .= '<div class="clear">' . $this->get_breadcumb( $c_no ) . '</div>';
		$tags .= '<ul class="sub-category">';

		$permalink = get_permalink( $post->ID );

		foreach ( $sub_cats as $sub ) {
			$title_parts = explode( '>', $sub->c_title );

			if ( $ref_n ) {
				$title = isset( $title_parts[2] ) ? trim( $title_parts[2] ) : '';
			} else {
				$title = isset( $title_parts[1] ) ? trim( $title_parts[1] ) : '';
			}

			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$has_child = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$t_category} WHERE ref_n = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					absint( $sub->c_no )
				)
			);

			$link_args = array(
				'c_no' => absint( $sub->c_no ),
				'ref'  => absint( $sub->ref ),
				'lev'  => absint( $sub->lev ),
			);

			if ( $has_child > 0 ) {
				$link_args['ref_n'] = absint( $sub->ref_n );
			}

			$link   = add_query_arg( $link_args, $permalink );
			$tags .= '<li><a href="' . esc_url( $link ) . '">' . esc_html( $title ) . '</a></li>';
		}

		$tags .= '</ul>';

		// Display content if sub-category has items.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$has_child_contents = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$t_directory} WHERE c_no = %d AND ref = %d AND lev >= 0 AND admin_ok = 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				absint( $c_no ),
				absint( $ref )
			)
		);

		if ( $has_child_contents > 0 ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$childrens = $wpdb->get_results(
				$wpdb->prepare(
					"SELECT * FROM {$t_directory} WHERE c_no = %d AND ref = %d AND lev >= 0 AND admin_ok = 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					absint( $c_no ),
					absint( $ref )
				)
			);

			$tags .= '<table class="sub-child-contents">';
			foreach ( $childrens as $single ) {
				$tags .= '<tr>';
				$tags .= '<td>' . esc_html( $single->title ) . '</td>';
				$tags .= '<td><a href="' . esc_url( $single->homepage ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $single->homepage ) . '</a></td>';
				$tags .= '<td>' . esc_html( $single->content ) . '</td>';
				$tags .= '</tr>';
			}
			$tags .= '</table>';
		}

		return $tags;
	}

	/**
	 * Extract title from category path.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    string $data    Category path string.
	 * @param    int    $lev     Level to extract.
	 * @return   string          Extracted title.
	 */
	private function exp_title( $data, $lev ) {
		$parts = explode( '>', $data );
		return isset( $parts[ $lev ] ) ? trim( $parts[ $lev ] ) : '';
	}

	/**
	 * Get breadcrumb navigation.
	 *
	 * @since    1.0.0
	 * @param    int $c_no    Category number.
	 * @return   string       HTML breadcrumb.
	 */
	public function get_breadcumb( $c_no ) {
		global $wpdb, $post;

		$root  = get_permalink( $post->ID );
		$table = $wpdb->prefix . 'dw_directory_category';

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT c_title, ref, ref_n, lev, step FROM {$table} WHERE c_no = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				absint( $c_no )
			)
		);

		if ( ! $row ) {
			return '';
		}

		$out  = '<ul class="breadcrumb">';
		$out .= '<li><a href="' . esc_url( $root ) . '">HOME</a></li>';

		if ( 0 === (int) $row->lev ) {
			$out .= '<li><a href="' . esc_url( $root ) . '">' . esc_html( $this->exp_title( $row->c_title, $row->lev ) ) . '</a></li>';
		} elseif ( 1 === (int) $row->lev ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$upper = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT c_no, ref, ref_n, lev, c_title FROM {$table} WHERE c_no = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					absint( $row->ref )
				)
			);

			if ( $upper ) {
				$link = add_query_arg(
					array(
						'c_no' => absint( $upper->c_no ),
						'ref'  => absint( $upper->ref ),
						'lev'  => absint( $upper->lev ),
					),
					$root
				);
				$out .= '<li><a href="' . esc_url( $link ) . '">' . esc_html( $upper->c_title ) . '</a></li>';
			}
			$out .= '<li>' . esc_html( $this->exp_title( $row->c_title, $row->lev ) ) . '</li>';
		} elseif ( 2 === (int) $row->lev ) {
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$upper = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT c_no, ref, ref_n, lev, c_title FROM {$table} WHERE c_no = %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
					absint( $row->ref_n )
				)
			);

			if ( $upper ) {
				$link = add_query_arg(
					array(
						'c_no' => absint( $upper->c_no ),
						'ref'  => absint( $upper->ref ),
						'lev'  => absint( $upper->lev ),
					),
					$root
				);
				$out .= '<li><a href="' . esc_url( $link ) . '">' . esc_html( $upper->c_title ) . '</a></li>';
			}
			$out .= '<li>' . esc_html( $this->exp_title( $row->c_title, $row->lev ) ) . '</li>';
		}

		$out .= '</ul>';

		return $out;
	}

	/**
	 * Get directory data with pagination.
	 *
	 * @since    1.0.0
	 * @param    int $c_no    Category number.
	 * @param    int $ref     Reference number.
	 * @param    int $lev     Level.
	 * @return   string       HTML output.
	 */
	public function get_data( $c_no, $ref, $lev ) {
		global $wpdb, $post;

		$table = $wpdb->prefix . 'dw_directory';

		$page     = isset( $_GET['pno'] ) ? absint( $_GET['pno'] ) : 1;
		$page     = max( 1, $page );
		$per_page = 20;
		$start    = ( $page - 1 ) * $per_page;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$total = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE c_no = %d AND ref = %d AND lev >= %d AND admin_ok = 1", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				absint( $c_no ),
				absint( $ref ),
				absint( $lev )
			)
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE c_no = %d AND ref = %d AND lev >= %d AND admin_ok = 1 ORDER BY num ASC LIMIT %d, %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				absint( $c_no ),
				absint( $ref ),
				absint( $lev ),
				$start,
				$per_page
			)
		);

		$tags  = '<div class="page-info">';
		/* translators: 1: Current page number, 2: Total items */
		$tags .= sprintf( esc_html__( 'Page: %1$d / Total: %2$d', 'daworks' ), $page, $total );
		$tags .= '</div>';
		$tags .= $this->get_breadcumb( $c_no );
		$tags .= '<table class="dw-content">';

		if ( ! empty( $result ) ) {
			$page_no = $start + 1;

			foreach ( $result as $single ) {
				$tags .= '<tr>';
				$tags .= '<td>' . esc_html( $page_no ) . '</td>';
				$tags .= '<td>' . esc_html( $single->title ) . '</td>';
				$tags .= '<td><a href="' . esc_url( $single->homepage ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $single->homepage ) . '</a></td>';
				$tags .= '<td>' . esc_html( $single->content ) . '</td>';
				$tags .= '</tr>';
				++$page_no;
			}
		} else {
			$tags .= '<tr><td colspan="4">' . esc_html__( '데이터가 없습니다.', 'daworks' ) . '</td></tr>';
		}

		$tags .= '</table>';

		// Pagination.
		$tags .= $this->render_pagination( $page, $total, $per_page, $c_no, $ref, $lev );

		return $tags;
	}

	/**
	 * Render pagination HTML.
	 *
	 * @since    2.0.0
	 * @param    int $page       Current page.
	 * @param    int $total      Total items.
	 * @param    int $per_page   Items per page.
	 * @param    int $c_no       Category number.
	 * @param    int $ref        Reference number.
	 * @param    int $lev        Level.
	 * @return   string          Pagination HTML.
	 */
	private function render_pagination( $page, $total, $per_page, $c_no, $ref, $lev ) {
		global $post;

		$total_pages = ceil( $total / $per_page );

		if ( $total_pages <= 1 ) {
			return '';
		}

		$permalink = get_permalink( $post->ID );
		$ref_n     = isset( $_GET['ref_n'] ) ? absint( $_GET['ref_n'] ) : 0;
		$step      = isset( $_GET['step'] ) ? absint( $_GET['step'] ) : 0;

		$base_args = array(
			'c_no' => absint( $c_no ),
			'ref'  => absint( $ref ),
			'lev'  => absint( $lev ),
		);

		if ( $ref_n ) {
			$base_args['ref_n'] = $ref_n;
		}
		if ( $step ) {
			$base_args['step'] = $step;
		}

		$page_tag = '<ul class="dw-pagination">';

		// Previous link.
		if ( $page > 1 ) {
			$prev_args        = $base_args;
			$prev_args['pno'] = $page - 1;
			$page_tag        .= '<li class="prev"><a href="' . esc_url( add_query_arg( $prev_args, $permalink ) ) . '">' . esc_html__( '이전', 'daworks' ) . '</a></li>';
		}

		// Page numbers.
		for ( $i = 1; $i <= $total_pages; $i++ ) {
			if ( $i === $page ) {
				$page_tag .= '<li class="active">' . esc_html( $i ) . '</li>';
			} else {
				$page_args        = $base_args;
				$page_args['pno'] = $i;
				$page_tag        .= '<li><a href="' . esc_url( add_query_arg( $page_args, $permalink ) ) . '">' . esc_html( $i ) . '</a></li>';
			}
		}

		// Next link.
		if ( $page < $total_pages ) {
			$next_args        = $base_args;
			$next_args['pno'] = $page + 1;
			$page_tag        .= '<li class="next"><a href="' . esc_url( add_query_arg( $next_args, $permalink ) ) . '">' . esc_html__( '다음', 'daworks' ) . '</a></li>';
		}

		$page_tag .= '</ul>';

		return $page_tag;
	}

	/**
	 * Display the directory.
	 *
	 * @since    1.0.0
	 * @return   void
	 */
	public function show_directory() {
		$user_name  = null;
		$user_email = null;

		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			$user_name    = $current_user->display_name;
			$user_email   = $current_user->user_email;
		}

		$c_no  = isset( $_GET['c_no'] ) ? absint( $_GET['c_no'] ) : 0;
		$ref   = isset( $_GET['ref'] ) ? absint( $_GET['ref'] ) : 0;
		$ref_n = isset( $_GET['ref_n'] ) ? absint( $_GET['ref_n'] ) : 0;
		$lev   = isset( $_GET['lev'] ) ? absint( $_GET['lev'] ) : 0;
		$step  = isset( $_GET['step'] ) ? absint( $_GET['step'] ) : 0;

		$partial_view = DAWORKS_PLUGIN_DIR . 'public/partials/daworks-public-display.php';

		if ( file_exists( $partial_view ) ) {
			include $partial_view;
		}
	}

	/**
	 * Register the shortcode.
	 *
	 * @since    1.0.0
	 */
	public function register_shortcode() {
		add_shortcode( 'dw-directory', array( $this, 'show_directory' ) );
	}
}
