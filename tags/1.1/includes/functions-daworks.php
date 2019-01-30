<?php 
	
	add_action('wp_head', 'front_ajax_url', 10);
	function front_ajax_url(){
		if (!is_admin()) {

		?>
		<script>
		    var ajaxurl = "<?php echo admin_url('admin-ajax.php'); ?>";
		</script>
		<?php
		}
	}

	// 아이템 삭제를 위한 ajax 처리
	add_action ( 'wp_ajax_dw_del_item', 'dw_delete_item' );
	function dw_delete_item(){
		global $wpdb;
		$table = $wpdb->prefix.'dw_directory';
		$num = intval( $_POST['num'] );

		if ( is_array($num) ) {
			try {
				foreach($num as $v){
					$r = $wpdb->delete( $table, array('num'=>$v) );
					if (false === $r) throw new Exception("fail", 1);
				}
				$result = 'success';
			} catch (Exception $e){
				$result = 'fail';
			}
			finally {
				echo $result;
			}
		}
		else {
			try {
				$r = $wpdb->delete($table, array('num'=>$num));
				if ( false === $r ) {
					throw new Exception("fail", 1);
				}else{
					$result = "success";
				}
			}
			catch(Exception $e){
				$result = "fail";
			}
			finally {
				echo $result;
			}			
		}
		$wpdb->flush();
		wp_die();
	}

	add_action('wp_ajax_get_cat_root', 'get_cat_root');
	add_action('wp_ajax_nopriv_get_cat_root', 'get_cat_root');
	function get_cat_root(){
		global $wpdb;
		$table = $wpdb->prefix.'dw_directory_category';
		$r = $wpdb->get_results($wpdb->prepare("SELECT c_no, c_title FROM $table where lev = %d and c_no = ref"), 0);
		if ( false === $r ) {
			$out['status'] = 'fail';
			$out['data'] = '1단계 카테고리 로딩 오류';
		}
		else {
			$out['status'] = 'success';
			$out['data'] = '<option value="">1단계 카테고리</option>';
			foreach($r as $opt){
				$out['data'] .= '<option value="'.$opt->c_no.'">'.$opt->c_title.'</option>';
			}
		}
		echo json_encode($out);
		$wpdb->flush();
		wp_die();
	}

	add_action( 'wp_ajax_dw_load_cat_lev1', 'dw_load_cat_lev1');
	add_action( 'wp_ajax_nopriv_dw_load_cat_lev1', 'dw_load_cat_lev1');
	function dw_load_cat_lev1(){
		global $wpdb;
		$table = $wpdb->prefix.'dw_directory_category';
		$c_no = intval( $_POST['c_no']);

		$query = "SELECT c_no, c_title, ref, ref_n, lev FROM $table WHERE ref = %d AND lev = 1 ORDER BY c_no ASC";

		echo json_encode($wpdb->get_results($wpdb->prepare($query, $c_no)));
		$wpdb->flush();
		wp_die();
	}

	add_action( 'wp_ajax_dw_load_cat_lev2', 'dw_load_cat_lev2');
	function dw_load_cat_lev2(){
		global $wpdb;
		$table = $wpdb->prefix.'dw_directory_category';
		$c_no = intval( $_POST['c_no']);

		$query = "SELECT c_no, c_title FROM $table WHERE ref_n = %d AND lev = 2 ORDER BY c_no ASC";

		echo json_encode($wpdb->get_results($wpdb->prepare($query, $c_no)));
		
		wp_die();
	}

	add_action('wp_ajax_dw_get_single_category', 'dw_get_single_category');
	add_action('wp_ajax_nopriv_dw_get_single_category', 'dw_get_single_category');
	function dw_get_single_category(){
		global $wpdb;
		$tb = $wpdb->prefix.'dw_directory_category';

		try {
			if ( !isset($_POST['c_no']) ) {
				throw new Exception ('정상적인 접근 방법이 아닙니다.');
			}
			
			$c_no = intval($_POST['c_no']);
			$lev = $wpdb->get_var("SELECT lev FROM $tb WHERE c_no = $c_no");

			if ($lev == 0) {
				$q = "SELECT c_no, c_title FROM $tb WHERE ref = $c_no AND  ref_n = ref and lev = 1";
				$pos = 1;
			}
			elseif ($lev > 0) {
				$q = "SELECT c_no, c_title FROM $tb WHERE ref_n = $c_no AND  ref_n != ref and lev > 1";
				$pos = 2;
			}
			else {
				throw new Exception('하위 단계 카테고리 없음');
			}

			$cats = $wpdb->get_results($q);
			if ( false === $cats ) throw new Exception('DB 쿼리 오류 :'.$q);

			$cdata = array();
			array_push($cdata, array('c_no'=>'', 'c_title'=>'카테고리 선택'));
			foreach($cats as $cat){
				array_push($cdata, array('c_no'=>$cat->c_no, 'c_title'=>explode('>',$cat->c_title)[$pos]));
			}

			$out = array('status'=>'success', 'data'=>$cdata, 'lev'=>$lev, 'q'=>$q);

		}
		catch(Exceptinon $e){
			$out = array('status'=>'fail', 'data'=>$e->getMessage());
		}
		finally{
			echo json_encode($out);
			
			$wpdb->flush();
			wp_die();
		}
	}

	add_action( 'wp_ajax_dw_grant_item', 'dw_grant_item');
	function dw_grant_item() {
		global $wpdb;
		$table = $wpdb->prefix.'dw_directory';

		$num = intval( $_POST['num'] );
		$c_no = intval( $_POST['c_no'] );
		$ref = intval( $_POST['ref'] );
		$ref_n = intval( $_POST['ref_n'] );
		$lev = intval($_POST['lev']);

		$exec = $wpdb->update(
					$table,
					array('c_no'=>$c_no, 'ref'=>$ref, 'ref_n'=>$ref_n, 'lev'=>$lev, 'admin_ok'=>'1'),
					array('num'=> $num )
				 );
		
		echo $exec;
		wp_die();
	}

	add_action( 'wp_ajax_dw_standby_item', 'dw_standby_item');
	function dw_standby_item() {
		global $wpdb;
		$table = $wpdb->prefix.'dw_directory';

		$num = intval( $_POST['num'] );
		$c_no = intval( $_POST['c_no'] );
		$ref = intval( $_POST['ref'] );
		$ref_n = intval( $_POST['ref_n'] );
		$lev = intval($_POST['lev']);

		$exec = $wpdb->update(
					$table,
					array('c_no'=>$c_no, 'ref'=>$ref, 'ref_n'=>$ref_n, 'lev'=>$lev, 'admin_ok'=>'0'),
					array('num'=> $num )
				 );
		
		echo $exec;
		wp_die();
	}

	// 카테고리 검색결과
	add_action('wp_ajax_dw_search_category', 'dw_search_keyword_result');
	function dw_search_keyword_result(){
		global $wpdb;
		$table = $wpdb->prefix.'dw_directory_category';
		$keyword = sanitize_text_field( $_POST['keyword'] );
		$query = "SELECT * FROM $table WHERE c_title like %s order by c_no asc";
		$res = $wpdb->get_results(
			$wpdb->prepare($query, '%'.$keyword.'%'), 
			ARRAY_A);
		echo json_encode($res);
		wp_die();
	}

	// 카테고리 목록 가져오기
	add_action('wp_ajax_dw_get_cat', 'dw_get_cat');
	function dw_get_cat(){
		global $wpdb;
		$table = $wpdb->prefix.'dw_directory_category';

		try 
		{
			$lev = intval($_POST['lev']);
			$c_no = intval($_POST['c_no']);
			
			if ( $lev == 0 ) {
				$q = "SELECT c_no, c_title, ref, ref_n, lev FROM $table WHERE c_no = ref and ref_n = 0 and lev = 0";
			}
			elseif ( $lev == 1 ) {
				$q = "SELECT c_no, c_title, ref, ref_n, lev FROM $table WHERE ref=$c_no and ref_n = $c_no and lev = 1";
			}
			elseif ( $lev > 1 ) {
				$q = "SELECT c_no, c_title, ref, ref_n, lev FROM $table WHERE ref_n=$c_no and ref != ref_n and lev = $lev";
			}
			else {
				throw new Exception($wpdb->print_error());
			}
			$out = ['status'=>'success', 'data'=>$wpdb->get_results($q, ARRAY_A)];
		}
		catch( Exception $e ) 
		{
			$out = array( 'status'=>'fail', 'data'=>$e->getCode() );
		}
		finally 
		{
			echo json_encode($out);
			$wpdb->flush();
			wp_die();

		}
	}

	// 새 카테고리 추가
	add_action('wp_ajax_dw_add_category', 'dw_add_new_category');
	function dw_add_new_category(){
		global $wpdb;

		$table = $wpdb->prefix.'dw_directory_category';
		$c_title = sanitize_text_field($_POST['c_title'] );
		$ref = intval($_POST['ref']);
		$ref_n = intval($_POST['ref_n']);
		$lev = intval($_POST['lev']);
		
		try {
			if ( $lev == 0 ) {
				// 1단계 카테고리 추가
					$insert = $wpdb->insert(
						$table,
						array( 
							'c_title' => $c_title
							),
						array('%s')
					);
					if ( !$insert ) {
						throw new Exception (array('msg'=>'DB 오류', 'code'=>$wpdb->print_error()));
					}
					else {
						$c_no = $wpdb->insert_id;
					}

					$update = $wpdb->update(
							$table, 
							array(
								'ref' => $c_no,
								'ref_n' => $c_no,
								'lev' => 0,
								'step' => 0
							),
							array('c_no'=>$c_no),
							array('%d', '%d', '%d', '%d'),
							array('%d')
						);

					if ( !$update ) {
						throw new Exception ($wpdb->print_error());
					}
					else {
						$res = $wpdb->get_results("select * from $table where c_no = $c_no", ARRAY_A);
						$out = array('status'=>'success', 'data'=>$res);
					}
			}
			elseif ( $lev  == 1 ){
				// 2단계 
					// 1단계 카테고리 제목
					$lev1Title = $wpdb->get_var("SELECT c_title FROM $table WHERE c_no = $ref");

					$insert = $wpdb->insert(
						$table,
						array( 
							'c_title' => $lev1Title.' > '.$c_title,
							'ref' => $ref,
							'ref_n' => $ref,
							'lev' => 1,
							'step' => '0.01'
							),
						array('%s','%d', '%d', '%d', '%s')
					);

					if ( !$insert ) {
						throw new Exception($wpdb->print_error());
					}
					else {
						$c_no_lev2 = $wpdb->insert_id;
						$res = $wpdb->get_results("select * from $table where c_no = $c_no_lev2", ARRAY_A);
						$out = array('status'=>'success', 'data'=>$res);
					}
			}
			elseif ( $lev > 1 ) {
				// 3단계
				// 2단계 카테고리 제목

					$lev2title = $wpdb->get_var("SELECT c_title FROM $table WHERE c_no = $ref_n");

					if ( ! $lev2title ) throw new Exception ( $wpdb->print_error() );

					$c_title = $lev2title.' > '.$c_title;
					$insert = $wpdb->insert(
						$table,
						array( 
							'c_title' => $c_title,
							'ref' => $ref,
							'ref_n' => $ref_n,
							'lev' => $lev,
							'step' => '0.0101'
							),
						array('%s','%d', '%d', '%d', '%s')
					);

					if ( !$insert ) {
						throw new Exception( $wpdb->print_error() );
					}
					else {
						$c_no_lev3 = $wpdb->insert_id;
						$res = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table WHERE c_no = %d", $c_no_lev3), ARRAY_A);
						$out = array('status'=>'success', 'data'=>$res);
					}
			}

		}
		catch( Exception $e ) {
			$out = array('status'=>'fail', 'data'=>strip_tags($e->getMessage(), ENT_QUOTES));
		}
		finally {
			echo json_encode($out);
		}
		$wpdb->flush();
		wp_die();
	}

	// 카테고리 삭제
	add_action('wp_ajax_dw_del_category', 'dw_del_category');
	function dw_del_category(){
		global $wpdb;
		$cat_table = $wpdb->prefix.'dw_directory_category';
		$dir_table = $wpdb->prefix.'dw_directory';

		try 
		{
			$c_no = intval($_POST['c_no']);
			// 삭제될 카테고리 정보
			$cat_data = $wpdb->get_row($wpdb->prepare("SELECT * FROM $cat_table WHERE c_no = %d", $c_no), OBJECT);
			if ( null === $cat_data ) throw new Exception('조회할 카테고리 정보 없음');

			// 등록 대기로 전환될 아이템의 처리
			if ( $cat_data->lev == 0 ) {
				// 카테고리 삭제
				$q = $wpdb->prepare("DELETE FROM $cat_table 
					  					WHERE (c_no = %d AND c_no = ref AND lev = 0) 
										  	  OR (ref = %d AND lev > 0)", 
										  	  $cat_data->c_no, $cat_data->c_no);
				$r = $wpdb->query($q);
				if ( false === $r ) throw new Exception('1단계 카테고리와 하위 카테고리를 삭제하는데 오류가 발생했습니다. ');

				// 1단계 카테고리 소속 아이템의 경우
				$q = "SELECT COUNT(*) FROM $dir_table WHERE ref=%d AND admin_ok = %d";
				$item_count = $wpdb->get_var($wpdb->prepare($q, $c_no, 1));
				if ( false === $item_count ) throw new Exception('1단계 카테고리에 속한 아이템의 수를 쿼리하는데 오류 발생');

				$q = "UPDATE $dir_table SET c_no = %d, ref=%d, ref_n=%d, lev=%d, admin_ok=%d WHERE ref=%d AND admin_ok=%d";
				$r = $wpdb->query($wpdb->prepare($q, 0,0,0,0,0,$c_no, 1));
				if ( false === $r ) throw new Exception('1단계 카테고리에 속한 아이템을 등록 대기로 전환하는데 오류가 발생하였습니다.');
			}
			elseif ( $cat_data->lev == 1 ) {
				// 2단계 카테고리 삭제
				$q = $wpdb->prepare(
						"DELETE FROM $cat_table 
							WHERE 
								(c_no = %d OR ref=%d) AND lev = %d",
								$c_no, $c_no, 1
					);
				$r = $wpdb->query($q);
				if ( false === $r ) throw new Exception('2단계 카테고리와 하위 카테고리를 삭제하는데 오류 발생');

				// 2단계 카테고리 소속 아이템 처리
				$q = "SELECT count(*) FROM $dir_table WHERE ref = %d AND ref_n=%d AND lev=%d AND admin_ok = %d";
				$item_count = $wpdb->get_var($wpdb->prepare($q, $c_no, $c_no, 1, 1));

				if ( false === $item_count ) throw new Exception('2단계 카테고리에 속한 아이템의 수를 쿼리하는데 오류 발생');

				$q = "UPDATE $dir_table SET c_no=%d, ref=%d, ref_n=%d, lev=%d, admin_ok=%d WHERE c_no=%d and admin_ok=%d";
				$r = $wpdb->query($wpdb->prepare($q, $cat_data->ref, $cat_data->ref, 0, 0, 0, $c_no, 1));
				if ( false === $r ) throw new Exception( '2단계 카테고리, 이하 카테고리 소속 아이템 상태 변경처리 오류' );
				
			}
			elseif ( $cat_data->lev > 1 ) {
				// 3단계 이하 카테고리 삭제
				$q = $wpdb->prepare("DELETE FROM $cat_table WHERE c_no = %d", $c_no);
				$r = $wpdb->query($q);
				if ( false === $r ) throw new Exception( '3단계 이하 카테고리 삭제 오류 ');

				// 아이템 처리
				$q = $wpdb->prepare("SELECT count(*) FROM $dir_table WHERE c_no=%d AND admin_ok = 1", $cat_data->c_no );
				$item_count = $wpdb->get_var($q);

				if ( false === $item_count ) throw new Exception('3단계 이하 아이템 수 쿼리 오류');

				$q = $wpdb->prepare(
					"UPDATE $dir_table 
						SET 
							c_no = %d, 
							ref=%d, 
							ref_n=%d, 
							admin_ok = 0 
						WHERE c_no=%d AND admin_ok=1",
							$cat_data->ref, 
							$cat_data->ref, 
							0, 
							$cat_data->c_no
						);
				$r = $wpdb->query($q);

				if ( false === $r ) throw new Exception( '3단계 카테고리 이하 소속 아이템 상태변경 오류 ');
			}

			$out = array( 'status'=>'success', 'data'=>$item_count );
		}
		catch ( Exception $e )
		{
			$out = array('status'=>'fail', 'data' => strip_tags($e->getMessage()) );
		}
		finally
		{
			echo json_encode($out);
			$wpdb->flush();
			wp_die();
		}

	}

	add_action('wp_ajax_dw_update_cat_title', 'dw_update_cat_title');
	function dw_update_cat_title(){
		global $wpdb;
		$table = $wpdb->prefix.'dw_directory_category';
		try {
			if ( !isset($_POST['c_no']) || empty($_POST['c_no']) ) throw new Exception('전달된 카테고리 고유값이 없습니다.');
			if ( !isset($_POST['c_title']) || empty($_POST['c_title']) ) throw new Exception('전달된 제목 데이터가 없습니다.');
			
			$c_no = intval($_POST['c_no']);
			$c_title = sanitize_text_field( $_POST['c_title'] );
            $q = $wpdb->preprea("SELECT c_no, c_title, lev, ref, ref_n FROM $table WHERE c_no = %d", $c_no);
			$cat_data = $wpdb->get_row($q);

			if (false === $cat_data) throw new Exception ('데이터 조회 오류');

			if ( $cat_data->lev == 0 ) {
				$r = $wpdb->update( $table, array('c_title'=>$c_title), array('c_no'=>$c_no) );
				if ( false === $r ) throw new Exception('DB 업데이트 오류');
			}
			elseif ( $cat_data->lev > 0 ) {
				$prev_title = $wpdb->get_var($wpdb->prepare("select c_title from $table where c_no = %d",$cat_data->ref_n) );
				$title = $prev_title.'>'.$c_title;
				$r = $wpdb->update($table, array('c_title'=>$title), array('c_no'=>$c_no) );
				if ( false === $r ) throw new Exception('DB 업데이트 오류');
			}
			else {
				throw new Exception('데이터 오류');
			}
			$new = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE c_no = %d", $cat_data->c_no));
			$out = array(
                    'status'=>'success',
                    'data'=>array(
                            'c_no'=>$new->c_no,
                            'c_title'=>$c_title,
                            'lev'=>$new->lev)
                );
		}
		catch (Exception $e){
			$out = array('status'=>'fail', 'data'=>$e->getMessage() );
		}
		finally{
			echo json_encode($out);
			$wpdb->flush();
			wp_die();
		}
	}

	add_action('wp_ajax_dw_get_item_list', 'dw_get_item_list');
	function dw_get_item_list(){
		global $wpdb;
		$catTable = $wpdb->prefix.'dw_directory_category';
		$dirTable = $wpdb->prefix.'dw_directory';

		// 페이징 변수
		
		// 아이템 쿼리 변수
		$c_no = (!empty(intval($_POST['c_no'])) || null != intval($_POST['c_no']) ) ? intval($_POST['c_no']) : null;
		$ref = (!empty(intval($_POST['ref'])) || null != intval($_POST['ref']) ) ? intval($_POST['ref']) : null;
		$ref_n = (!empty(intval($_POST['ref_n'])) || null != intval($_POST['ref_n'])) ? intval($_POST['ref_n']) : null;
		$page = (!empty(intval($_POST['page'])) || null != intval($_POST['page'])) ? intval($_POST['page']) : 1;

		try{

			
			if ( $c_no == null && $ref == null && $ref_n == null )
			{
				// 쿼리 조건이 없을 경우
				$where = " WHERE admin_ok = 1";
			}
			elseif ( $c_no != null && $ref == null && $ref_n == null ) {
				// 1단계 카테고리만 있을 경우
				$where = " WHERE d.c_no = $c_no AND d.lev = 0 AND admin_ok = 1";
			}
			elseif ( $c_no != null && $ref != null && $ref_n == null ) {
				// 2단계 카테고리까지 
				$where = " WHERE d.c_no = $ref AND d.ref = $c_no AND admin_ok = 1";
			}
			elseif ( $c_no != null && $ref != null && $ref_n != null ) {
				$where = " WHERE d.c_no = $ref_n AND d.ref = $c_no AND d.ref_n = $ref AND admin_ok = 1";
			}
			else {
				$where = " WHERE d.c_no = $c_no AND d.ref = d.c_no AND d.lev = 1 AND admin_ok = 1";
			}

			$startNum = ($page - 1) * 20;
			$limit = " ORDER BY d.num LIMIT $startNum, 20";

			$q = "SELECT d.*, c.c_title 
				  	FROM $dirTable d 
				  	JOIN $catTable c ON  d.c_no = c.c_no ".$where.$limit;
			
			$q_count = "SELECT count(*)
				  	FROM $dirTable d 
				  	JOIN $catTable c ON  d.c_no = c.c_no ".$where;

			$r_count = $wpdb->get_var($q_count);
			$r = $wpdb->get_results($q, OBJECT);
			if ( false === $r ) throw new Exception('DB 쿼리 오류!');
			
			if ( false === $tatal_count ) throw new Exception('DB 쿼리 오류! 에러 로그를 확인하세요.');

			$output = array('status'=>'success', 'data'=>$r, 'total'=>$r_count,'page'=>$page );
		}
		catch(Exception $e) {
			$output = array('status'=>'fail', 'data'=>$e->getMessage(), 'q'=>$q);
		}
		finally{
			$output['check'] = array('cno'=>$c_no, 'ref'=>$ref, 'refn'=>$ref_n, 'page'=>$page);

			echo json_encode($output);
			$wpdb->flush();
			wp_die();
		}

	}

	add_action('wp_ajax_dw_search_item', 'dw_search_item');
	add_action('wp_ajax_nopriv_dw_search_item', 'dw_search_item');
	function dw_search_item(){
		// 아이템 검색
		global $wpdb;
		$table = $wpdb->prefix.'dw_directory';
		$ctable = $wpdb->prefix.'dw_directory_category';
		try {

			if ( null !== $_POST['keyword'] ) {
				$keyword = sanitize_text_field($_POST['keyword']);
			}
			else {
				$keyword = null;
			}
			
			if ( $keyword === null ) {
				throw new Exception('검색어가 없습니다.');
			}
			else {
				$q = $wpdb->prepare(
						"SELECT d.*, c.c_title FROM $table d JOIN $ctable c ON c.c_no = d.c_no WHERE title like %s OR homepage like %s OR content like %s ORDER BY num ASC",
						"%$keyword%", "%$keyword%", "%$keyword%"
					);
				$result = $wpdb->get_results($q);
				if ( false === $result ) throw new Exception('쿼리 오류 : '.$q);
				$out = array('status'=>'success', 'data'=>$result, 'total'=>$wpdb->num_rows);
			}
		}
		catch(Exception $e){
			$out = array('status'=>'fail', 'data'=>$e->getMessage());
		}
		finally{
			echo json_encode($out);
			$wpdb->flush();
			wp_die();
		}
	}

	add_action('wp_ajax_dw_get_single_item', 'dw_get_single_item');
	function dw_get_single_item(){
		global $wpdb;
		$table = $wpdb->prefix.'dw_directory';
		$ctable = $wpdb->prefix.'dw_directory_category';

		try {
			if ( null !== intval($_POST['num']) ) {
				$num = intval($_POST['num']);
			}
			else {
				throw new Exception ('정상적인 방법으로 접근하세요.');
			}
			
			$q = $wpdb->prepare(
				"SELECT * FROM $table WHERE num = %d",$num
				);
			$result = $wpdb->get_row($q, ARRAY_A);
			if ( false === $wpdb->get_row($q) ) throw new Exception('DB오류 발생');

			// 카테고리명 가져오기
			$catName = $wpdb->get_var("SELECT c_title FROM {$ctable} WHERE c_no = {$result['c_no']}");
			if ( !$catName ) throw new Exception('카테고리 정보 없음');

			$out = array('status'=>'success', 'data'=>$result, 'catInfo'=>$catName);
		}
		catch(Exception $e){
			$out = array('status'=>'fail', 'data'=>$e->getMessage());
		}
		finally{
			echo json_encode($out);
			$wpdb->flush();
			wp_die();
		}
	}

	add_action('wp_ajax_dw_get_category_tree', 'dw_get_category_tree');
	function dw_get_category_tree(){
		global $wpdb;
		$tb = $wpdb->prefix.'dw_directory_category';

		try {
			if ( null !== intval($_POST['c_no']) )  {
				throw new Exception('정상적인 방법으로 접근하세요.');
			}
			else {
				$c_no = intval($_POST['c_no']);
			}

			$lev = $wpdb->get_var($wpdb->prepare("select lev from $tb where c_no=%d", $c_no));
			$cat_tree= [];

			// 2단계 카테고리
			$cat_tree['ref'] = $wpdb->get_results("SELECT c_no, c_title FROM $tb WHERE ref = $c_no and lev = 1 AND ref = ref_n");
			if ($cat_tree['ref'] === false ) throw new Exception('2단계 카테고리 반환 오류');

			$cat_tree['ref_n'] = $wpdb->get_results("SELECT c_no, c_title FROM $tb WHERE ref_n = $c_no and lev > 1 AND ref != ref_n");
			
			if ( $cat_tree['ref_n']=== false ) throw new Exception('3단계 카테고리 반환 오류');

			$out = array('status'=>'success', 'data'=>$cat_tree);
		}
		catch(Exception $e){
			$out = array('status'=>'fail', 'data'=>$e->getMessage());
		}
		finally {
			echo json_encode($out);
			$wpdb->flush();
			wp_die();
		}
	}

	// 카테고리 셀렉터로 아이템 검색결과 리턴
	add_action('wp_ajax_dw_search_item_by_cat', 'dw_search_item_by_cat');
	function dw_search_item_by_cat(){
		global $wpdb;
		$tb_cat = $wpdb->prefix.'dw_directory_category';
		$tb_dir = $wpdb->prefix.'dw_directory';

		try {

			if ( null !== intval($_POST['data']['c1']) ) {
				$v1 = intval($_POST['data']['c1']);
			} 
			else {
				throw new Exception("1단계 카테고리를 선택하세요");	
			}

			if (null !== intval($_POST['data']['c2'])) {
				$v2 = intval($_POST['data']['c2']);
			} 
			else { $v2 = ''; }

			if (null !== intval($_POST['data']['c3'])) {
				$v3 = intval($_POST['data']['c3']);
			}
			else { $v3 = ''; }

			$q = "SELECT d.*, c.c_title FROM $tb_dir d JOIN $tb_cat c ON c.c_no = d.c_no WHERE ";
			$q_count = "SELECT count(*) FROM $tb_dir d JOIN $tb_cat c ON c.c_no = d.c_no WHERE ";

			if ( $v1 !== '' && $v2 !== '' ) {
				// 2단계 카테고리 정보
				$where = "d.c_no = $v2 AND d.ref = $v1";
			}
			elseif ( $v1 !== '' && $v2 !== '' && $v3 !== '' ) {
				$where = "d.c_no = $v3 AND d.ref = $v1 AND d.ref_n = $v2";
			}
			else {
				$where = "d.ref = $v1";
			}

			$result = $wpdb->get_results($q.$where);

			if ( false === $result || false === $total_count ) throw new Exception('DB 조회 실패 : '.$q);

			$out = array('status'=>'success', 'data'=> $result, 'total' => $total_count );
			
		} catch (Exception $e) {
			$out = array('status'=>'fail', 'data'=>$e->getMessage() );
		}
		finally{
			echo json_encode($out);
			$wpdb->flush();
			wp_die();
		}
	}

	add_action('wp_ajax_dw_add_directory_item', 'dw_add_directory_item');
	function dw_add_directory_item() {
		global $wpdb;
		$tb = $wpdb->prefix.'dw_directory';

		try {
			$mode = ( null !== sanitize_text_field($_POST['data']['mode']) ) ? sanitize_text_field($_POST['data']['mode']) : null;
			if ($mode === null ) throw new Exception("정상적인 경로로 접근하세요.");

			$title = (null !== ($_POST['data']['title'])) ? sanitize_text_field($_POST['data']['title']) : null;
			$url = (null !== ($_POST['data']['url'])) ? sanitize_text_field($_POST['data']['url']) : null;
			$content = (null !== ($_POST['data']['content'])) ? sanitize_text_field($_POST['data']['content']) : null;
			$cat1 = (null !== ($_POST['data']['cat1'])) ? sanitize_text_field($_POST['data']['cat1']) : null;
			$cat2 = (null !== ($_POST['data']['cat2'])) ? sanitize_text_field($_POST['data']['cat2']) : null;
			$cat3 = (null !== ($_POST['data']['cat3'])) ? sanitize_text_field($_POST['data']['cat3']) : null;
			$name = (null !== ($_POST['data']['name'])) ? sanitize_text_field($_POST['data']['name']) : null;
			$email = (null !== ($_POST['data']['email'])) ? sanitize_text_field($_POST['data']['email']) : null;
			$num = (null !== ($_POST['data']['num'])) ? sanitize_text_field($_POST['data']['email']) : null;

			if ( $name == null && $email == null ) {
				$current_user = wp_get_current_user();
				$name = $current_user->display_name;
				$email = $current_user->user_email;
			}

			switch ( $mode ) {
				case 'new' :
					if ( $cat1 != null && $cat2 != null && $cat3 != null ) {
						$c_no = $cat3;
						$ref = $cat1;
						$ref_n = $cat2;
						$lev = 2;
						$step = 0.0101;
					}
					elseif ( $cat1 != null && $cat2 != null && $cat3 == null ) {
						$c_no = $cat2;
						$ref = $cat1;
						$ref_n = $cat1;
						$lev = 1;
						$step = 0.01;
					}
					elseif ( $cat1 != null && $cat2 == null && $cat3 == null ){
						$c_no = $cat1;
						$ref = $cat1;
						$ref_n = 0;
						$lev = 0;
						$step = 0;
					}
					else {
						throw new Exception("카테고리 설정이 잘못되었습니다.", 1);
					}

					$r = $wpdb->insert(
						$tb, 
						array(
								'c_no' => $c_no,
								'ref'  => $ref,
								'ref_n' => $ref_n,
								'lev' => $lev,
								'step' => $step,
								'name' => $name,
								'email' => $email,
								'homepage' => $url,
								'title' => $title,
								'content' => $content,
								'admin_ok' => 1,
								'Re_quesion' => 0,
								'indate' => date('Y-m-d')
							),
						array(
								'%d',
								'%d',
								'%d',
								'%d',
								'%f',
								'%s',
								'%s',
								'%s',
								'%s',
								'%s',
								'%d',
								'%d',
								'%s'
							)
						);
					if ( false === $r ) throw new Exception("DB 입력 오류", 1);

					$insert_num = $wpdb->insert_id;
					$row = $wpdb->get_row("SELECT * FROM $tb WHERE num = $insert_num");

					if ( false === $row ) throw new Exception("추가한 항목을 불러오는데 오류가 발생했습니다.", 1);
					
					$out = array(
								'stat'=>'success', 
								'data'=>$row
								);
					
				break;

				case 'edit' :
				break;

			}
			
			
		} catch (Exception $e) {
			$out = array( 
					'stat'=>'fail', 
					'data'=> $e->getMessage()
				);
		}
		finally {
			echo json_encode($out);
			$wpdb->flush();
			wp_die();
		}
		
	}

	add_action('wp_ajax_nopriv_dw_apply_directory_item', 'dw_apply_directory_item');
	add_action('wp_ajax_dw_apply_directory_item', 'dw_apply_directory_item');
	function dw_apply_directory_item() {
		global $wpdb;
		$tb = $wpdb->prefix.'dw_directory';

		try {
			$title = (null !== ($_POST['data']['title'])) ? sanitize_text_field($_POST['data']['title']) : null;
			$url = (null !== ($_POST['data']['url'])) ? sanitize_text_field($_POST['data']['url']) : null;
			$content = (null !== ($_POST['data']['content'])) ? sanitize_text_field($_POST['data']['content']) : null;

			$cat1 = (null !== ($_POST['data']['cat1'])) ? sanitize_text_field($_POST['data']['cat1']) : null;
			$cat2 = (null !== ($_POST['data']['cat2'])) ? sanitize_text_field($_POST['data']['cat2']) : null;
			$cat3 = (null !== ($_POST['data']['cat3'])) ? sanitize_text_field($_POST['data']['cat3']) : null;
			$cat_new = (null !== ($_POST['data']['cat_new'])) ? sanitize_text_field($_POST['data']['cat_new']) : null;

			$name = (null !== ($_POST['data']['name'])) ? sanitize_text_field($_POST['data']['name']) : null;
			$email = (null !== ($_POST['data']['email'])) ? sanitize_text_field($_POST['data']['email']) : null;

			if ( $cat1 != null && $cat2 != null && $cat3 != null ) {
				$c_no = $cat3;
				$ref = $cat1;
				$ref_n = $cat2;
				$lev = 2;
				$step = 0.0101;
			}
			elseif ( $cat1 != null && $cat2 != null && $cat3 == null ) {
				$c_no = $cat2;
				$ref = $cat1;
				$ref_n = $cat1;
				$lev = 1;
				$step = 0.01;
			}
			elseif ( $cat1 != null && $cat2 == null && $cat3 == null ){
				$c_no = $cat1;
				$ref = $cat1;
				$ref_n = 0;
				$lev = 0;
				$step = 0;
			}
			elseif ( ! is_null($cat_new) && ( is_null($cat1) && is_null($cat2) && is_null($cat3) ) ){
				$c_no = 0;
				$ref = 0;
				$ref_n = 0;
				$lev = 0;
				$step = 0;
			}
			else {
				throw new Exception("카테고리 설정이 잘못되었습니다.");
			}

			$r = $wpdb->insert(
				$tb, 
				array(
						'c_no' => $c_no,
						'ref'  => $ref,
						'ref_n' => $ref_n,
						'lev' => $lev,
						'step' => $step,
						'name' => $name,
						'email' => $email,
						'homepage' => $url,
						'title' => $title,
						'content' => $content,
						'admin_ok' => 0,
						'Re_quesion' => 0,
						'request_new_cat' => $cat_new,
						'indate' => date('Y-m-d')
					),
				array(
						'%d',
						'%d',
						'%d',
						'%d',
						'%f',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%d',
						'%d',
						'%s',
						'%s'
					)
				);
			if ( false === $r ) throw new Exception("DB 입력 오류");

			$insert_num = $wpdb->insert_id;
			$row = $wpdb->get_row("SELECT * FROM $tb WHERE num = $insert_num");

			if ( false === $row ) throw new Exception("추가한 항목을 불러오는데 오류가 발생했습니다.");
			
			$out = array( 'stat'=>'success' );
			
		} 
		catch (Exception $e) {
			$out = array( 
					'stat'=>'fail', 
					'data'=> $e->getMessage()
				);
		}
		
		echo json_encode($out);
		$wpdb->flush();
		wp_die();
		
	}

 ?>
