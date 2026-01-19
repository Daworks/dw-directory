<?php
/**
 * AJAX Handler Functions
 *
 * @link       https://github.com/Daworks/dw-directory
 * @since      1.0.0
 * @package    Daworks
 * @subpackage Daworks/includes
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Output AJAX URL for frontend
 */
add_action( 'wp_head', 'daworks_front_ajax_url', 10 );
function daworks_front_ajax_url() {
	if ( ! is_admin() ) {
		?>
		<script type="text/javascript">
			var ajaxurl = "<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>";
			var daworks_nonce = "<?php echo esc_js( wp_create_nonce( 'daworks_ajax_nonce' ) ); ?>";
		</script>
		<?php
	}
}

/**
 * Register admin nonce for AJAX
 */
add_action( 'admin_head', 'daworks_admin_ajax_nonce' );
function daworks_admin_ajax_nonce() {
	?>
	<script type="text/javascript">
		var daworks_nonce = "<?php echo esc_js( wp_create_nonce( 'daworks_ajax_nonce' ) ); ?>";
	</script>
	<?php
}

/**
 * Helper function to verify nonce and capability
 *
 * @param bool $require_admin Whether to require admin capability.
 * @return bool
 */
function daworks_verify_ajax_request( $require_admin = false ) {
	// Check nonce
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'daworks_ajax_nonce' ) ) {
		wp_send_json_error( array( 'message' => __( 'Security check failed.', 'daworks' ) ) );
		return false;
	}

	// Check admin capability if required
	if ( $require_admin && ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => __( 'Permission denied.', 'daworks' ) ) );
		return false;
	}

	return true;
}

/**
 * Delete directory item (Admin only)
 */
add_action( 'wp_ajax_dw_del_item', 'dw_delete_item' );
function dw_delete_item() {
	daworks_verify_ajax_request( true );

	global $wpdb;
	$table = $wpdb->prefix . 'dw_directory';

	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$num = isset( $_POST['num'] ) ? $_POST['num'] : null;

	if ( is_array( $num ) ) {
		$num = array_map( 'absint', $num );
		$deleted = 0;
		foreach ( $num as $id ) {
			$result = $wpdb->delete( $table, array( 'num' => $id ), array( '%d' ) );
			if ( false !== $result ) {
				$deleted++;
			}
		}
		echo ( $deleted > 0 ) ? 'success' : 'fail';
	} else {
		$num = absint( $num );
		$result = $wpdb->delete( $table, array( 'num' => $num ), array( '%d' ) );
		echo ( false !== $result ) ? 'success' : 'fail';
	}

	wp_die();
}

/**
 * Get root categories
 */
add_action( 'wp_ajax_get_cat_root', 'dw_get_cat_root' );
add_action( 'wp_ajax_nopriv_get_cat_root', 'dw_get_cat_root' );
function dw_get_cat_root() {
	global $wpdb;
	$table = $wpdb->prefix . 'dw_directory_category';

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$results = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT c_no, c_title FROM {$table} WHERE lev = %d AND c_no = ref",
			0
		)
	);

	if ( false === $results ) {
		$out = array(
			'status' => 'fail',
			'data'   => __( '1단계 카테고리 로딩 오류', 'daworks' ),
		);
	} else {
		$options = '<option value="">' . esc_html__( '1단계 카테고리', 'daworks' ) . '</option>';
		foreach ( $results as $opt ) {
			$options .= '<option value="' . esc_attr( $opt->c_no ) . '">' . esc_html( $opt->c_title ) . '</option>';
		}
		$out = array(
			'status' => 'success',
			'data'   => $options,
		);
	}

	wp_send_json( $out );
}

/**
 * Load level 1 categories
 */
add_action( 'wp_ajax_dw_load_cat_lev1', 'dw_load_cat_lev1' );
add_action( 'wp_ajax_nopriv_dw_load_cat_lev1', 'dw_load_cat_lev1' );
function dw_load_cat_lev1() {
	global $wpdb;
	$table = $wpdb->prefix . 'dw_directory_category';
	$c_no = isset( $_POST['c_no'] ) ? absint( $_POST['c_no'] ) : 0;

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$results = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT c_no, c_title, ref, ref_n, lev FROM {$table} WHERE ref = %d AND lev = 1 ORDER BY c_no ASC",
			$c_no
		)
	);

	wp_send_json( $results );
}

/**
 * Load level 2 categories
 */
add_action( 'wp_ajax_dw_load_cat_lev2', 'dw_load_cat_lev2' );
function dw_load_cat_lev2() {
	global $wpdb;
	$table = $wpdb->prefix . 'dw_directory_category';
	$c_no = isset( $_POST['c_no'] ) ? absint( $_POST['c_no'] ) : 0;

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$results = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT c_no, c_title FROM {$table} WHERE ref_n = %d AND lev = 2 ORDER BY c_no ASC",
			$c_no
		)
	);

	wp_send_json( $results );
}

/**
 * Get single category with children
 */
add_action( 'wp_ajax_dw_get_single_category', 'dw_get_single_category' );
add_action( 'wp_ajax_nopriv_dw_get_single_category', 'dw_get_single_category' );
function dw_get_single_category() {
	global $wpdb;
	$tb = $wpdb->prefix . 'dw_directory_category';

	$out = array();

	if ( ! isset( $_POST['c_no'] ) ) {
		wp_send_json( array(
			'status' => 'fail',
			'data'   => __( '정상적인 접근 방법이 아닙니다.', 'daworks' ),
		) );
	}

	$c_no = absint( $_POST['c_no'] );

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$lev = $wpdb->get_var( $wpdb->prepare( "SELECT lev FROM {$tb} WHERE c_no = %d", $c_no ) );

	if ( null === $lev ) {
		wp_send_json( array(
			'status' => 'fail',
			'data'   => __( '카테고리를 찾을 수 없습니다.', 'daworks' ),
		) );
	}

	$lev = absint( $lev );

	if ( 0 === $lev ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$cats = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT c_no, c_title FROM {$tb} WHERE ref = %d AND ref_n = ref AND lev = 1",
				$c_no
			)
		);
		$pos = 1;
	} else {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$cats = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT c_no, c_title FROM {$tb} WHERE ref_n = %d AND ref_n != ref AND lev > 1",
				$c_no
			)
		);
		$pos = 2;
	}

	if ( false === $cats ) {
		wp_send_json( array(
			'status' => 'fail',
			'data'   => __( 'DB 쿼리 오류', 'daworks' ),
		) );
	}

	$cdata = array();
	$cdata[] = array(
		'c_no'    => '',
		'c_title' => __( '카테고리 선택', 'daworks' ),
	);

	foreach ( $cats as $cat ) {
		$title_parts = explode( '>', $cat->c_title );
		$cdata[] = array(
			'c_no'    => $cat->c_no,
			'c_title' => isset( $title_parts[ $pos ] ) ? trim( $title_parts[ $pos ] ) : $cat->c_title,
		);
	}

	wp_send_json( array(
		'status' => 'success',
		'data'   => $cdata,
		'lev'    => $lev,
	) );
}

/**
 * Grant (approve) item (Admin only)
 */
add_action( 'wp_ajax_dw_grant_item', 'dw_grant_item' );
function dw_grant_item() {
	daworks_verify_ajax_request( true );

	global $wpdb;
	$table = $wpdb->prefix . 'dw_directory';

	$num   = isset( $_POST['num'] ) ? absint( $_POST['num'] ) : 0;
	$c_no  = isset( $_POST['c_no'] ) ? absint( $_POST['c_no'] ) : 0;
	$ref   = isset( $_POST['ref'] ) ? absint( $_POST['ref'] ) : 0;
	$ref_n = isset( $_POST['ref_n'] ) ? absint( $_POST['ref_n'] ) : 0;
	$lev   = isset( $_POST['lev'] ) ? absint( $_POST['lev'] ) : 0;

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$result = $wpdb->update(
		$table,
		array(
			'c_no'     => $c_no,
			'ref'      => $ref,
			'ref_n'    => $ref_n,
			'lev'      => $lev,
			'admin_ok' => 1,
		),
		array( 'num' => $num ),
		array( '%d', '%d', '%d', '%d', '%d' ),
		array( '%d' )
	);

	echo ( false !== $result ) ? '1' : '0';
	wp_die();
}

/**
 * Set item to standby (Admin only)
 */
add_action( 'wp_ajax_dw_standby_item', 'dw_standby_item' );
function dw_standby_item() {
	daworks_verify_ajax_request( true );

	global $wpdb;
	$table = $wpdb->prefix . 'dw_directory';

	$num   = isset( $_POST['num'] ) ? absint( $_POST['num'] ) : 0;
	$c_no  = isset( $_POST['c_no'] ) ? absint( $_POST['c_no'] ) : 0;
	$ref   = isset( $_POST['ref'] ) ? absint( $_POST['ref'] ) : 0;
	$ref_n = isset( $_POST['ref_n'] ) ? absint( $_POST['ref_n'] ) : 0;
	$lev   = isset( $_POST['lev'] ) ? absint( $_POST['lev'] ) : 0;

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$result = $wpdb->update(
		$table,
		array(
			'c_no'     => $c_no,
			'ref'      => $ref,
			'ref_n'    => $ref_n,
			'lev'      => $lev,
			'admin_ok' => 0,
		),
		array( 'num' => $num ),
		array( '%d', '%d', '%d', '%d', '%d' ),
		array( '%d' )
	);

	echo ( false !== $result ) ? '1' : '0';
	wp_die();
}

/**
 * Search categories by keyword (Admin only)
 */
add_action( 'wp_ajax_dw_search_category', 'dw_search_keyword_result' );
function dw_search_keyword_result() {
	daworks_verify_ajax_request( true );

	global $wpdb;
	$table = $wpdb->prefix . 'dw_directory_category';
	$keyword = isset( $_POST['keyword'] ) ? sanitize_text_field( wp_unslash( $_POST['keyword'] ) ) : '';

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$results = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT * FROM {$table} WHERE c_title LIKE %s ORDER BY c_no ASC",
			'%' . $wpdb->esc_like( $keyword ) . '%'
		),
		ARRAY_A
	);

	wp_send_json( $results );
}

/**
 * Get categories by level (Admin only)
 */
add_action( 'wp_ajax_dw_get_cat', 'dw_get_cat' );
function dw_get_cat() {
	daworks_verify_ajax_request( true );

	global $wpdb;
	$table = $wpdb->prefix . 'dw_directory_category';

	$lev  = isset( $_POST['lev'] ) ? absint( $_POST['lev'] ) : 0;
	$c_no = isset( $_POST['c_no'] ) ? absint( $_POST['c_no'] ) : 0;

	if ( 0 === $lev ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$results = $wpdb->get_results(
			"SELECT c_no, c_title, ref, ref_n, lev FROM {$table} WHERE c_no = ref AND ref_n = 0 AND lev = 0",
			ARRAY_A
		);
	} elseif ( 1 === $lev ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT c_no, c_title, ref, ref_n, lev FROM {$table} WHERE ref = %d AND ref_n = %d AND lev = 1",
				$c_no,
				$c_no
			),
			ARRAY_A
		);
	} else {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$results = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT c_no, c_title, ref, ref_n, lev FROM {$table} WHERE ref_n = %d AND ref != ref_n AND lev = %d",
				$c_no,
				$lev
			),
			ARRAY_A
		);
	}

	if ( false === $results ) {
		wp_send_json( array(
			'status' => 'fail',
			'data'   => __( 'DB 쿼리 오류', 'daworks' ),
		) );
	}

	wp_send_json( array(
		'status' => 'success',
		'data'   => $results,
	) );
}

/**
 * Add new category (Admin only)
 */
add_action( 'wp_ajax_dw_add_category', 'dw_add_new_category' );
function dw_add_new_category() {
	daworks_verify_ajax_request( true );

	global $wpdb;
	$table = $wpdb->prefix . 'dw_directory_category';

	$c_title = isset( $_POST['c_title'] ) ? sanitize_text_field( wp_unslash( $_POST['c_title'] ) ) : '';
	$ref     = isset( $_POST['ref'] ) ? absint( $_POST['ref'] ) : 0;
	$ref_n   = isset( $_POST['ref_n'] ) ? absint( $_POST['ref_n'] ) : 0;
	$lev     = isset( $_POST['lev'] ) ? absint( $_POST['lev'] ) : 0;

	if ( empty( $c_title ) ) {
		wp_send_json( array(
			'status' => 'fail',
			'data'   => __( '카테고리 제목을 입력하세요.', 'daworks' ),
		) );
	}

	if ( 0 === $lev ) {
		// Level 0 (root) category
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->insert(
			$table,
			array( 'c_title' => $c_title ),
			array( '%s' )
		);

		$c_no = $wpdb->insert_id;
		if ( ! $c_no ) {
			wp_send_json( array(
				'status' => 'fail',
				'data'   => __( 'DB 입력 오류', 'daworks' ),
			) );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->update(
			$table,
			array(
				'ref'   => $c_no,
				'ref_n' => $c_no,
				'lev'   => 0,
				'step'  => 0,
			),
			array( 'c_no' => $c_no ),
			array( '%d', '%d', '%d', '%f' ),
			array( '%d' )
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE c_no = %d", $c_no ),
			ARRAY_A
		);

	} elseif ( 1 === $lev ) {
		// Level 1 category
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$lev1_title = $wpdb->get_var( $wpdb->prepare( "SELECT c_title FROM {$table} WHERE c_no = %d", $ref ) );

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->insert(
			$table,
			array(
				'c_title' => $lev1_title . ' > ' . $c_title,
				'ref'     => $ref,
				'ref_n'   => $ref,
				'lev'     => 1,
				'step'    => 0.01,
			),
			array( '%s', '%d', '%d', '%d', '%f' )
		);

		$c_no_lev2 = $wpdb->insert_id;
		if ( ! $c_no_lev2 ) {
			wp_send_json( array(
				'status' => 'fail',
				'data'   => __( 'DB 입력 오류', 'daworks' ),
			) );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE c_no = %d", $c_no_lev2 ),
			ARRAY_A
		);

	} else {
		// Level 2+ category
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$lev2_title = $wpdb->get_var( $wpdb->prepare( "SELECT c_title FROM {$table} WHERE c_no = %d", $ref_n ) );

		if ( ! $lev2_title ) {
			wp_send_json( array(
				'status' => 'fail',
				'data'   => __( '상위 카테고리를 찾을 수 없습니다.', 'daworks' ),
			) );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$wpdb->insert(
			$table,
			array(
				'c_title' => $lev2_title . ' > ' . $c_title,
				'ref'     => $ref,
				'ref_n'   => $ref_n,
				'lev'     => $lev,
				'step'    => 0.0101,
			),
			array( '%s', '%d', '%d', '%d', '%f' )
		);

		$c_no_lev3 = $wpdb->insert_id;
		if ( ! $c_no_lev3 ) {
			wp_send_json( array(
				'status' => 'fail',
				'data'   => __( 'DB 입력 오류', 'daworks' ),
			) );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->get_results(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE c_no = %d", $c_no_lev3 ),
			ARRAY_A
		);
	}

	wp_send_json( array(
		'status' => 'success',
		'data'   => $result,
	) );
}

/**
 * Delete category (Admin only)
 */
add_action( 'wp_ajax_dw_del_category', 'dw_del_category' );
function dw_del_category() {
	daworks_verify_ajax_request( true );

	global $wpdb;
	$cat_table = $wpdb->prefix . 'dw_directory_category';
	$dir_table = $wpdb->prefix . 'dw_directory';

	$c_no = isset( $_POST['c_no'] ) ? absint( $_POST['c_no'] ) : 0;

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$cat_data = $wpdb->get_row(
		$wpdb->prepare( "SELECT * FROM {$cat_table} WHERE c_no = %d", $c_no )
	);

	if ( null === $cat_data ) {
		wp_send_json( array(
			'status' => 'fail',
			'data'   => __( '조회할 카테고리 정보 없음', 'daworks' ),
		) );
	}

	$item_count = 0;

	if ( 0 === (int) $cat_data->lev ) {
		// Delete level 0 category and all children
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$cat_table} WHERE (c_no = %d AND c_no = ref AND lev = 0) OR (ref = %d AND lev > 0)",
				$c_no,
				$c_no
			)
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$item_count = $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$dir_table} WHERE ref = %d AND admin_ok = 1", $c_no )
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$dir_table} SET c_no = 0, ref = 0, ref_n = 0, lev = 0, admin_ok = 0 WHERE ref = %d AND admin_ok = 1",
				$c_no
			)
		);

	} elseif ( 1 === (int) $cat_data->lev ) {
		// Delete level 1 category
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query(
			$wpdb->prepare(
				"DELETE FROM {$cat_table} WHERE (c_no = %d OR ref = %d) AND lev = 1",
				$c_no,
				$c_no
			)
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$item_count = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$dir_table} WHERE ref = %d AND ref_n = %d AND lev = 1 AND admin_ok = 1",
				$c_no,
				$c_no
			)
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$dir_table} SET c_no = %d, ref = %d, ref_n = 0, lev = 0, admin_ok = 0 WHERE c_no = %d AND admin_ok = 1",
				$cat_data->ref,
				$cat_data->ref,
				$c_no
			)
		);

	} else {
		// Delete level 2+ category
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query(
			$wpdb->prepare( "DELETE FROM {$cat_table} WHERE c_no = %d", $c_no )
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$item_count = $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$dir_table} WHERE c_no = %d AND admin_ok = 1", $c_no )
		);

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query(
			$wpdb->prepare(
				"UPDATE {$dir_table} SET c_no = %d, ref = %d, ref_n = 0, admin_ok = 0 WHERE c_no = %d AND admin_ok = 1",
				$cat_data->ref,
				$cat_data->ref,
				$c_no
			)
		);
	}

	wp_send_json( array(
		'status' => 'success',
		'data'   => $item_count,
	) );
}

/**
 * Update category title (Admin only)
 */
add_action( 'wp_ajax_dw_update_cat_title', 'dw_update_cat_title' );
function dw_update_cat_title() {
	daworks_verify_ajax_request( true );

	global $wpdb;
	$table = $wpdb->prefix . 'dw_directory_category';

	$c_no    = isset( $_POST['c_no'] ) ? absint( $_POST['c_no'] ) : 0;
	$c_title = isset( $_POST['c_title'] ) ? sanitize_text_field( wp_unslash( $_POST['c_title'] ) ) : '';

	if ( empty( $c_no ) || empty( $c_title ) ) {
		wp_send_json( array(
			'status' => 'fail',
			'data'   => __( '필수 데이터가 없습니다.', 'daworks' ),
		) );
	}

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$cat_data = $wpdb->get_row(
		$wpdb->prepare( "SELECT c_no, c_title, lev, ref, ref_n FROM {$table} WHERE c_no = %d", $c_no )
	);

	if ( null === $cat_data ) {
		wp_send_json( array(
			'status' => 'fail',
			'data'   => __( '데이터 조회 오류', 'daworks' ),
		) );
	}

	if ( 0 === (int) $cat_data->lev ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->update(
			$table,
			array( 'c_title' => $c_title ),
			array( 'c_no' => $c_no ),
			array( '%s' ),
			array( '%d' )
		);
	} else {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$prev_title = $wpdb->get_var(
			$wpdb->prepare( "SELECT c_title FROM {$table} WHERE c_no = %d", $cat_data->ref_n )
		);
		$new_title = $prev_title . ' > ' . $c_title;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->update(
			$table,
			array( 'c_title' => $new_title ),
			array( 'c_no' => $c_no ),
			array( '%s' ),
			array( '%d' )
		);
	}

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$updated = $wpdb->get_row(
		$wpdb->prepare( "SELECT * FROM {$table} WHERE c_no = %d", $c_no )
	);

	wp_send_json( array(
		'status' => 'success',
		'data'   => array(
			'c_no'    => $updated->c_no,
			'c_title' => $c_title,
			'lev'     => $updated->lev,
		),
	) );
}

/**
 * Get item list (Admin only)
 */
add_action( 'wp_ajax_dw_get_item_list', 'dw_get_item_list' );
function dw_get_item_list() {
	daworks_verify_ajax_request( true );

	global $wpdb;
	$cat_table = $wpdb->prefix . 'dw_directory_category';
	$dir_table = $wpdb->prefix . 'dw_directory';

	$c_no  = isset( $_POST['c_no'] ) && '' !== $_POST['c_no'] ? absint( $_POST['c_no'] ) : null;
	$ref   = isset( $_POST['ref'] ) && '' !== $_POST['ref'] ? absint( $_POST['ref'] ) : null;
	$ref_n = isset( $_POST['ref_n'] ) && '' !== $_POST['ref_n'] ? absint( $_POST['ref_n'] ) : null;
	$page  = isset( $_POST['pno'] ) ? absint( $_POST['pno'] ) : 1;

	$per_page  = 20;
	$start_num = ( $page - 1 ) * $per_page;

	// Build WHERE clause
	if ( null === $c_no && null === $ref && null === $ref_n ) {
		$where = 'WHERE admin_ok = 1';
	} elseif ( null !== $c_no && null === $ref && null === $ref_n ) {
		$where = $wpdb->prepare( 'WHERE d.c_no = %d AND d.lev = 0 AND admin_ok = 1', $c_no );
	} elseif ( null !== $c_no && null !== $ref && null === $ref_n ) {
		$where = $wpdb->prepare( 'WHERE d.c_no = %d AND d.ref = %d AND admin_ok = 1', $ref, $c_no );
	} elseif ( null !== $c_no && null !== $ref && null !== $ref_n ) {
		$where = $wpdb->prepare( 'WHERE d.c_no = %d AND d.ref = %d AND d.ref_n = %d AND admin_ok = 1', $ref_n, $c_no, $ref );
	} else {
		$where = $wpdb->prepare( 'WHERE d.c_no = %d AND d.ref = d.c_no AND d.lev = 1 AND admin_ok = 1', $c_no );
	}

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$results = $wpdb->get_results(
		"SELECT d.*, c.c_title FROM {$dir_table} d JOIN {$cat_table} c ON d.c_no = c.c_no {$where} ORDER BY d.num LIMIT {$start_num}, {$per_page}"
	);

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$total = $wpdb->get_var(
		"SELECT COUNT(*) FROM {$dir_table} d JOIN {$cat_table} c ON d.c_no = c.c_no {$where}"
	);

	wp_send_json( array(
		'status' => 'success',
		'data'   => $results,
		'total'  => $total,
		'page'   => $page,
	) );
}

/**
 * Search items
 */
add_action( 'wp_ajax_dw_search_item', 'dw_search_item' );
add_action( 'wp_ajax_nopriv_dw_search_item', 'dw_search_item' );
function dw_search_item() {
	global $wpdb;
	$table  = $wpdb->prefix . 'dw_directory';
	$ctable = $wpdb->prefix . 'dw_directory_category';

	$keyword = isset( $_POST['keyword'] ) ? sanitize_text_field( wp_unslash( $_POST['keyword'] ) ) : null;

	if ( null === $keyword ) {
		wp_send_json( array(
			'status' => 'fail',
			'data'   => __( '검색어가 없습니다.', 'daworks' ),
		) );
	}

	$like = '%' . $wpdb->esc_like( $keyword ) . '%';

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$results = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT d.*, c.c_title FROM {$table} d JOIN {$ctable} c ON c.c_no = d.c_no WHERE d.title LIKE %s OR d.homepage LIKE %s OR d.content LIKE %s ORDER BY d.num ASC",
			$like,
			$like,
			$like
		)
	);

	if ( false === $results ) {
		wp_send_json( array(
			'status' => 'fail',
			'data'   => __( '쿼리 오류', 'daworks' ),
		) );
	}

	wp_send_json( array(
		'status' => 'success',
		'data'   => $results,
		'total'  => count( $results ),
	) );
}

/**
 * Get single item (Admin only)
 */
add_action( 'wp_ajax_dw_get_single_item', 'dw_get_single_item' );
function dw_get_single_item() {
	daworks_verify_ajax_request( true );

	global $wpdb;
	$table  = $wpdb->prefix . 'dw_directory';
	$ctable = $wpdb->prefix . 'dw_directory_category';

	$num = isset( $_POST['num'] ) ? absint( $_POST['num'] ) : 0;

	if ( ! $num ) {
		wp_send_json( array(
			'status' => 'fail',
			'data'   => __( '정상적인 방법으로 접근하세요.', 'daworks' ),
		) );
	}

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$result = $wpdb->get_row(
		$wpdb->prepare( "SELECT * FROM {$table} WHERE num = %d", $num ),
		ARRAY_A
	);

	if ( null === $result ) {
		wp_send_json( array(
			'status' => 'fail',
			'data'   => __( 'DB 오류 발생', 'daworks' ),
		) );
	}

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$cat_name = $wpdb->get_var(
		$wpdb->prepare( "SELECT c_title FROM {$ctable} WHERE c_no = %d", $result['c_no'] )
	);

	wp_send_json( array(
		'status'  => 'success',
		'data'    => $result,
		'catInfo' => $cat_name ? $cat_name : '',
	) );
}

/**
 * Get category tree (Admin only)
 */
add_action( 'wp_ajax_dw_get_category_tree', 'dw_get_category_tree' );
function dw_get_category_tree() {
	daworks_verify_ajax_request( true );

	global $wpdb;
	$tb = $wpdb->prefix . 'dw_directory_category';

	$c_no = isset( $_POST['c_no'] ) ? absint( $_POST['c_no'] ) : 0;

	if ( ! $c_no ) {
		wp_send_json( array(
			'status' => 'fail',
			'data'   => __( '정상적인 방법으로 접근하세요.', 'daworks' ),
		) );
	}

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$ref_cats = $wpdb->get_results(
		$wpdb->prepare( "SELECT c_no, c_title FROM {$tb} WHERE ref = %d AND lev = 1 AND ref = ref_n", $c_no )
	);

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	$ref_n_cats = $wpdb->get_results(
		$wpdb->prepare( "SELECT c_no, c_title FROM {$tb} WHERE ref_n = %d AND lev > 1 AND ref != ref_n", $c_no )
	);

	wp_send_json( array(
		'status' => 'success',
		'data'   => array(
			'ref'   => $ref_cats,
			'ref_n' => $ref_n_cats,
		),
	) );
}

/**
 * Search items by category (Admin only)
 */
add_action( 'wp_ajax_dw_search_item_by_cat', 'dw_search_item_by_cat' );
function dw_search_item_by_cat() {
	daworks_verify_ajax_request( true );

	global $wpdb;
	$tb_cat = $wpdb->prefix . 'dw_directory_category';
	$tb_dir = $wpdb->prefix . 'dw_directory';

	$v1 = isset( $_POST['data']['c1'] ) ? absint( $_POST['data']['c1'] ) : 0;
	$v2 = isset( $_POST['data']['c2'] ) ? absint( $_POST['data']['c2'] ) : 0;
	$v3 = isset( $_POST['data']['c3'] ) ? absint( $_POST['data']['c3'] ) : 0;

	if ( ! $v1 ) {
		wp_send_json( array(
			'status' => 'fail',
			'data'   => __( '1단계 카테고리를 선택하세요', 'daworks' ),
		) );
	}

	if ( $v1 && $v2 && $v3 ) {
		$where = $wpdb->prepare( 'd.c_no = %d AND d.ref = %d AND d.ref_n = %d', $v3, $v1, $v2 );
	} elseif ( $v1 && $v2 ) {
		$where = $wpdb->prepare( 'd.c_no = %d AND d.ref = %d', $v2, $v1 );
	} else {
		$where = $wpdb->prepare( 'd.ref = %d', $v1 );
	}

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared
	$results = $wpdb->get_results(
		"SELECT d.*, c.c_title FROM {$tb_dir} d JOIN {$tb_cat} c ON c.c_no = d.c_no WHERE {$where}"
	);

	wp_send_json( array(
		'status' => 'success',
		'data'   => $results,
		'total'  => count( $results ),
	) );
}

/**
 * Add directory item (Admin only)
 */
add_action( 'wp_ajax_dw_add_directory_item', 'dw_add_directory_item' );
function dw_add_directory_item() {
	daworks_verify_ajax_request( true );

	global $wpdb;
	$tb = $wpdb->prefix . 'dw_directory';

	$mode    = isset( $_POST['data']['mode'] ) ? sanitize_text_field( wp_unslash( $_POST['data']['mode'] ) ) : null;
	$title   = isset( $_POST['data']['title'] ) ? sanitize_text_field( wp_unslash( $_POST['data']['title'] ) ) : '';
	$url     = isset( $_POST['data']['url'] ) ? esc_url_raw( wp_unslash( $_POST['data']['url'] ) ) : '';
	$content = isset( $_POST['data']['content'] ) ? sanitize_textarea_field( wp_unslash( $_POST['data']['content'] ) ) : '';
	$cat1    = isset( $_POST['data']['cat1'] ) ? absint( $_POST['data']['cat1'] ) : 0;
	$cat2    = isset( $_POST['data']['cat2'] ) ? absint( $_POST['data']['cat2'] ) : 0;
	$cat3    = isset( $_POST['data']['cat3'] ) ? absint( $_POST['data']['cat3'] ) : 0;
	$name    = isset( $_POST['data']['name'] ) ? sanitize_text_field( wp_unslash( $_POST['data']['name'] ) ) : '';
	$email   = isset( $_POST['data']['email'] ) ? sanitize_email( wp_unslash( $_POST['data']['email'] ) ) : '';
	$num     = isset( $_POST['data']['num'] ) ? absint( $_POST['data']['num'] ) : 0;

	if ( null === $mode ) {
		wp_send_json( array(
			'stat' => 'fail',
			'data' => __( '정상적인 경로로 접근하세요.', 'daworks' ),
		) );
	}

	// Get current user info if not provided
	if ( empty( $name ) && empty( $email ) ) {
		$current_user = wp_get_current_user();
		$name         = $current_user->display_name;
		$email        = $current_user->user_email;
	}

	// Determine category hierarchy
	if ( $cat1 && $cat2 && $cat3 ) {
		$c_no  = $cat3;
		$ref   = $cat1;
		$ref_n = $cat2;
		$lev   = 2;
		$step  = 0.0101;
	} elseif ( $cat1 && $cat2 ) {
		$c_no  = $cat2;
		$ref   = $cat1;
		$ref_n = $cat1;
		$lev   = 1;
		$step  = 0.01;
	} elseif ( $cat1 ) {
		$c_no  = $cat1;
		$ref   = $cat1;
		$ref_n = 0;
		$lev   = 0;
		$step  = 0;
	} else {
		wp_send_json( array(
			'stat' => 'fail',
			'data' => __( '카테고리 설정이 잘못되었습니다.', 'daworks' ),
		) );
	}

	$data = array(
		'c_no'        => $c_no,
		'ref'         => $ref,
		'ref_n'       => $ref_n,
		'lev'         => $lev,
		'step'        => $step,
		'name'        => $name,
		'email'       => $email,
		'homepage'    => $url,
		'title'       => $title,
		'content'     => $content,
		'admin_ok'    => 1,
		'Re_quesion'  => 0,
		'indate'      => current_time( 'Y-m-d' ),
	);

	$format = array( '%d', '%d', '%d', '%d', '%f', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s' );

	if ( 'new' === $mode ) {
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
		$result = $wpdb->insert( $tb, $data, $format );

		if ( false === $result ) {
			wp_send_json( array(
				'stat' => 'fail',
				'data' => __( 'DB 입력 오류', 'daworks' ),
			) );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$row = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$tb} WHERE num = %d", $wpdb->insert_id )
		);

		wp_send_json( array(
			'stat' => 'success',
			'data' => $row->homepage,
		) );

	} elseif ( 'edit' === $mode ) {
		if ( ! $num ) {
			wp_send_json( array(
				'stat' => 'fail',
				'data' => __( '수정할 아이템의 번호가 없습니다.', 'daworks' ),
			) );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$result = $wpdb->update(
			$tb,
			$data,
			array( 'num' => $num ),
			$format,
			array( '%d' )
		);

		if ( false === $result ) {
			wp_send_json( array(
				'stat' => 'fail',
				'data' => __( '업데이트 오류', 'daworks' ),
			) );
		}

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$row = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$tb} WHERE num = %d", $num )
		);

		wp_send_json( array(
			'stat' => 'success',
			'data' => $row->homepage,
		) );
	}
}

/**
 * Apply (submit) directory item (Public - logged in users)
 */
add_action( 'wp_ajax_nopriv_dw_apply_directory_item', 'dw_apply_directory_item' );
add_action( 'wp_ajax_dw_apply_directory_item', 'dw_apply_directory_item' );
function dw_apply_directory_item() {
	// Verify nonce for public submissions
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'daworks_ajax_nonce' ) ) {
		wp_send_json( array(
			'stat' => 'fail',
			'data' => __( 'Security check failed.', 'daworks' ),
		) );
	}

	global $wpdb;
	$tb = $wpdb->prefix . 'dw_directory';

	$title   = isset( $_POST['data']['title'] ) ? sanitize_text_field( wp_unslash( $_POST['data']['title'] ) ) : '';
	$url     = isset( $_POST['data']['url'] ) ? esc_url_raw( wp_unslash( $_POST['data']['url'] ) ) : '';
	$content = isset( $_POST['data']['content'] ) ? sanitize_textarea_field( wp_unslash( $_POST['data']['content'] ) ) : '';
	$cat1    = isset( $_POST['data']['cat1'] ) ? absint( $_POST['data']['cat1'] ) : 0;
	$cat2    = isset( $_POST['data']['cat2'] ) ? absint( $_POST['data']['cat2'] ) : 0;
	$cat3    = isset( $_POST['data']['cat3'] ) ? absint( $_POST['data']['cat3'] ) : 0;
	$cat_new = isset( $_POST['data']['cat_new'] ) ? sanitize_text_field( wp_unslash( $_POST['data']['cat_new'] ) ) : '';
	$name    = isset( $_POST['data']['name'] ) ? sanitize_text_field( wp_unslash( $_POST['data']['name'] ) ) : '';
	$email   = isset( $_POST['data']['email'] ) ? sanitize_email( wp_unslash( $_POST['data']['email'] ) ) : '';

	// Determine category hierarchy
	if ( $cat1 && $cat2 && $cat3 ) {
		$c_no  = $cat3;
		$ref   = $cat1;
		$ref_n = $cat2;
		$lev   = 2;
		$step  = 0.0101;
	} elseif ( $cat1 && $cat2 ) {
		$c_no  = $cat2;
		$ref   = $cat1;
		$ref_n = $cat1;
		$lev   = 1;
		$step  = 0.01;
	} elseif ( $cat1 ) {
		$c_no  = $cat1;
		$ref   = $cat1;
		$ref_n = 0;
		$lev   = 0;
		$step  = 0;
	} elseif ( ! empty( $cat_new ) ) {
		$c_no  = 0;
		$ref   = 0;
		$ref_n = 0;
		$lev   = 0;
		$step  = 0;
	} else {
		wp_send_json( array(
			'stat' => 'fail',
			'data' => __( '카테고리 설정이 잘못되었습니다.', 'daworks' ),
		) );
	}

	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery
	$result = $wpdb->insert(
		$tb,
		array(
			'c_no'            => $c_no,
			'ref'             => $ref,
			'ref_n'           => $ref_n,
			'lev'             => $lev,
			'step'            => $step,
			'name'            => $name,
			'email'           => $email,
			'homepage'        => $url,
			'title'           => $title,
			'content'         => $content,
			'admin_ok'        => 0,
			'Re_quesion'      => 0,
			'request_new_cat' => $cat_new,
			'indate'          => current_time( 'Y-m-d' ),
		),
		array( '%d', '%d', '%d', '%d', '%f', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s' )
	);

	if ( false === $result ) {
		wp_send_json( array(
			'stat' => 'fail',
			'data' => __( 'DB 입력 오류', 'daworks' ),
		) );
	}

	wp_send_json( array( 'stat' => 'success' ) );
}
