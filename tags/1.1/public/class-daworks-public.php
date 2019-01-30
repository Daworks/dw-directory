<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://daworks.org
 * @since      1.0.0
 *
 * @package    Daworks
 * @subpackage Daworks/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/daworks-public.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'bower_components/font-awesome/css/font-awesome.min.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/daworks-public.js', array( 'jquery' ), $this->version, false );
	}

	public function get_first_cat() {
		global $wpdb;
		global $post;
		$directory = $wpdb->prefix.'dw_directory';
		$category = $wpdb->prefix.'dw_directory_category';

		$directory = $wpdb->prefix.'dw_directory';
		$category = $wpdb->prefix.'dw_directory_category';

		$query = "SELECT * FROM {$category} WHERE lev = %d order by c_no asc";
		$depth_root = $wpdb->get_results(
				$wpdb->prepare($query, 0)
			);
		
		$tags = '<ul class="depth-root">';
		foreach ($depth_root as $single)
		{
			$tags.= "<li>
						<a href='".get_permalink($post->ID)."?&c_no=".$single->c_no."&ref=".$single->ref."&lev=".$single->lev."'>
							$single->c_title
						</a>
						<ul class='sub-category'>";

			$child_cats = $wpdb->get_results(
				$wpdb->prepare(
					"
						SELECT * FROM $category WHERE ref = %d and lev > 0
					", 
					$single->c_no
					)
			);
			foreach ($child_cats as $child_cat){
				if ($child_cat->lev == 1){
					$title = trim(explode(">", $child_cat->c_title)[1]);
					$link = get_permalink($post->ID) . '?&c_no='.$child_cat->c_no.'&ref='.$child_cat->ref.'&lev='.$child_cat->lev;
					$tags .= "<li><a href='".$link."'>$title</a></li>";
				}
			}
			$tags .= "
						</ul>
					</li>";
		}
		$tags .= '</ul>';
		return $tags;
	}

	public function get_sub_category($c_no, $ref, $lev){
		global $wpdb;
		global $post;
		$t_category = $wpdb->prefix.'dw_directory_category';
		$t_directory = $wpdb->prefix.'dw_directory';
		(int) $ref_n = (null !== (intval($_GET['ref_n'])))?get_query_var('ref_n', intval($_GET['ref_n'])):"";

		// 서브 카테고리 출력
		$query = "SELECT * from $t_category where ref = %d and lev = %d order by c_title asc";
		$sub_cats = $wpdb->get_results(
				$wpdb->prepare($query, $ref, $lev+1)
			);

		$upper_category_name = $wpdb->get_var($wpdb->prepare("select c_title from $t_category where c_no = %d", $ref));
		
		$tags = "<h3><a href='#' onclick='history.back();'>".$upper_category_name."</a></h3>";
		$tags .= "<div class='clear'>".$this->get_breadcumb($c_no)."</div>";
		$tags .= "<ul class='sub-category'>";
		foreach ( $sub_cats as $sub ){

			$cat_nums = count(explode(">", $sub->c_title));

			if ( $ref_n ) {
				$title = trim(explode(">", $sub->c_title)[2]);
			}
			else {
				$title = trim(explode(">", $sub->c_title)[1]);
			}
			
			$has_child = $wpdb->get_var(
					$wpdb->prepare("select count(*) from $t_category where ref_n = %d", $sub->c_no)
				);

			if ( $has_child == 0 ){
				$link = get_permalink($post->ID) . "?&c_no=" . $sub->c_no . "&ref=" . $sub->ref . "&lev=". $sub->lev;
			}
			elseif ( $has_child > 0 ){
				$link = get_permalink($post->ID) . "?&c_no=" . $sub->c_no . "&ref=" . $sub->ref . "&lev=". $sub->lev . "&ref_n=".$sub->ref_n;
			}
			$tags .= "<li><a href='".$link."'>".$title."</a></li>";
		}
		$tags .= "</ul>";

		// 서브 카테고리 자체의 내용이 있는 경우 출력
		$q = "select count(*) from $t_directory where c_no = %d and ref = %d and lev >=0 and admin_ok=1";
		$has_child_contents = $wpdb->get_results(
				$wpdb->prepare( $q, $c_no, $ref )
			);

		if ( $has_child_contents > 0 ) {
			$q = "select * from $t_directory where c_no = %d and ref = %d and lev >=0 and admin_ok=1";
			$childrens = $wpdb->get_results($wpdb->prepare( $q, $c_no, $ref ));

			$tags .= "<table class='sub-child-contents'>";
			foreach ( $childrens as $single ){
				$tags .= '
				<tr>
					<td>'.$single->title.'</td>
					<td><a href="'.$single->homepage.'" target="_blank">'.$single->homepage.'</a></td>
					<td>'.$single->content.'</td>
				</tr>
				';
			}
			$tags .= "</table>";
		}

		return $tags;
	}

	private function exp_title($data, $lev){
		return explode(">", $data)[$lev];
	}

	public function get_breadcumb($c_no) {
		global $wpdb, $post;
		$root = get_permalink($post->ID);

		$table = $wpdb->prefix . "dw_directory_category";
		$row = $wpdb->get_row(
				$wpdb->prepare(
						"select c_title, ref, ref_n, lev, step from $table where c_no = %d", $c_no
					)
			);

		$out  = '<ul class="breadcumb">';
		$out .= '<li><a href="'.$root.'">HOME</a></li>';
		if ( $row->lev == 0 ){
			$link = $root;
			$out .= '<li><a href="'.$link.'">'.$this->exp_title($row->c_title, $row->lev).'</a></li>';
		}
		elseif ( $row->lev == 1 ) {
			$upper = $wpdb->get_row(
                            $wpdb->prepare(
                                    "select c_no, ref, ref_n, lev, c_title from $table where c_no = %d",
                                    $row->ref
                            )
                        );
			$link = $root . '?&c_no='.$upper->c_no.'&ref='.$upper->ref.'&lev='.$upper->lev;
			$out .= '<li><a href="'.$link.'">'.$upper->c_title.'</a></li>';
			$out .= '<li>'.$this->exp_title($row->c_title, $row->lev).'</li>';
		}
		elseif ( $row->lev == 2 ) {
			$upper = $wpdb->get_row("select c_no, ref, ref_n, lev, c_title from $table where c_no = $row->ref_n");
			$link = $root . '?&c_no='.$upper->c_no.'&ref='.$upper->ref.'&lev='.$upper->lev;
			$out .= '<li><a href="'.$link.'">'.$upper->c_title.'</a></li>';
			$out .= "<li>".$this->exp_title($row->c_title, $row->lev)."</li>";
		}

		$out .= '</ul>';
		
		return $out;
	}

	public function get_data($c_no, $ref, $lev){
		
		global $wpdb, $post;
		$table = $wpdb->prefix . "dw_directory";

		if ( null !== intval($_GET['page']) ){
			$page = get_query_var('page', intval($_GET['page']));
		}
		else {
			$page = 1;
		}

		$per_page = 20;
		$start = ($page-1) * $per_page;
		$end = $per_page;
		$total = $wpdb->get_var(
			$wpdb->prepare(
					"SELECT count(*) FROM $table WHERE c_no = %d AND ref = %d AND lev >= %d",
					$c_no, $ref, $lev
				)
			);

		$result = $wpdb->get_results(
						$wpdb->prepare(
							"SELECT * FROM $table WHERE c_no = %d AND ref = %d AND lev >= %d ORDER BY num ASC LIMIT %d,%d",
							$c_no, $ref, $lev, $start, $end
						));
		$tags = "";
		$tags .= "<div class='page-info'>Page: ".$page." / Total : $total</div>";
		$tags .= $this->get_breadcumb($c_no);
		$tags .= '<table class="dw-content">';

		if ( !empty($result) ) {
			$page_no = $start+1;
			
			foreach ($result as $single)
			{
				$tags .= "<tr>
							<td>{$page_no}</td>
							<td>$single->title</td>
							<td><a href=\"{$single->homepage}\" target='_blank'>{$single->homepage}</a></td>
							<td>$single->content</td>
						</tr>";
				++$page_no;
			}
		}
		else {
			$tags .= '<tr><td colspan="3">데이터가 없습니다.</td></tr>';
		}
		$tags .= '</table>';

		// Pagination
		$page_tag = '<ul class="dw-pagination">';
		$_GET['c_no'] = isset($_GET['c_no'])?intval($_GET['c_no']):"";
		$_GET['step'] = isset($_GET['step'])?intval($_GET['step']):"";
		$_GET['ref_n'] = isset($_GET['ref_n'])?intval($_GET['ref_n']):"";
		if ($page > 1) {
			$page_tag .= '<li class="prev"><a href="'.get_permalink($post->ID).'?&c_no='.get_query_var('c_no', intval($_GET['c_no'])).'&ref='.get_query_var('ref', intval($_GET['ref'])).'&ref_n='.get_query_var('ref_n', intval($_GET['ref_n'])).'&lev='.get_query_var('lev', intval($_GET['lev'])).'&step='.get_query_var('step', intval($_GET['step'])).'&page='.($page-1).'">이전</a></li>';
		}

		for($i = 1 ; $i<=ceil($total/$per_page); $i++){

			if ( $i == $page ) {
				$page_tag .= '<li class="active">'.$i.'</li>';
			}
			else {
				$page_tag .= '<li><a href="'.get_permalink($post->ID).'?&c_no='.get_query_var('c_no', intval($_GET['c_no'])).'&ref='.get_query_var('ref', intval($_GET['ref'])).'&ref_n='.get_query_var('ref_n', intval($_GET['ref_n'])).'&lev='.get_query_var('lev', intval($_GET['lev'])).'&step='.get_query_var('step', intval($_GET['step'])).'&page='.$i.'">'.$i.'</a></li>';
			}
		}

		if ($page < ceil($total/$per_page)) {
			$page_tag .= '<li class="next"><a href="'.get_permalink($post->ID).'?&c_no='.get_query_var('c_no', intval($_GET['c_no'])).'&ref='.get_query_var('ref', intval($_GET['ref'])).'&ref_n='.get_query_var('ref_n', intval($_GET['ref_n'])).'&lev='.get_query_var('lev', intval($_GET['lev'])).'&step='.get_query_var('step', intval($_GET['step'])).'&page='.($page+1).'">다음</a></li>';
		}
		
		$page_tag .='</ul>';

		return $tags.$page_tag;
	}

	public function get_search_form() {
		$out = '
		<div id="search-item">
			<form name="search_item_form" id="search_item_form">
				<label for="keyword">디렉토리 항목 찾기 : 검색어를 입력하세요.</label>
				<input type="text" name="keyword" id="keyword">
				<button class="btn" type="submit">검색</button>
			</form>
		</div>
		<div id="search-result" class="hide"></div>
		';
		return $out;
	}

	public function show_function_button() {

		$out = '';
		if ( is_user_logged_in() ) {
			$out .= '
				<div class="dw-button-set" id="dw-control">
					<button id="apply-dir-item" type="button" class="new">홈페이지 등록 신청</button> 
				</div>
				';
		}
		else {
			$out .= '<p><a href="'.wp_login_url().'">로그인</a> 후 등록 신청 하실 수 있습니다.</p>';
		}

		$out .= $this->add_new_item();
		return $out;
	}

	public function add_new_item() {
		global $current_user;
		get_currentuserinfo();

		$out ='
		<div id="new-category-form" class="new-category-form hide">
			<h3>디렉토리 서비스 항목 등록 신청서</h3>
			<hr>
			<form name="add_directory_item" id="add_directory_item">
				<fieldset>
					<label class="ness">카테고리를 선택하세요.</label>
					<select name="cat1" id="cat1">
						<option value="">1단계 카테고리</option>
					</select>
					<select name="cat2" id="cat2">
						<option value="">2단계 카테고리</option>	
					</select>
					<select name="cat3" id="cat3">
						<option value="">3단계 카테고리</option>
					</select>
					<button id="rqst-new-cat" type="button">새 카테고리 신청</button>
					<p class="note">적절한 카테고리가 없을 경우 새 카테고리 신청 버튼을 클릭하세요.</p>
				</fieldset>
				<fieldset id="new-cat-field" class="hide">
					<label>신청하실 카테고리명을 입력해주세요.</label>
					<p><i>항목과 항목 사이는 > 표시로 구분해주세요. 예) 대분류 > 중분류 > 소분류</i></p>
					<input type="text" name="c_title" placeholder="대분류 > 중분류 > 소분류 ...">
				</fieldset>
				<fieldset>	
					<label class="ness">홈페이지 제목</label>
					<input type="text" name="title">
				</fieldset>
				<fieldset>	
					<label class="ness">홈페이지 주소</label>
					<input type="text" name="homepage" value="http://">
				</fieldset>
				<fieldset>	
					<label>간단한 설명</label>
					<textarea name="content" id="dw-content"></textarea>
				</fieldset>
				<fieldset>	
					<label class="ness">신청자 이름</label>
					<input type="text" name="name" id="dw-name" value="'.$current_user->display_name.'">
				</fieldset>
				<fieldset>	
					<label class="ness">신청자 이메일</label>
					<input type="email" name="email" id="dw-email" placeholder="your@email.com" value="'.$current_user->user_email.'">
				</fieldset>
				<fieldset>
					<p class="agree">
						<span class="ness"></span>표시된 항목은 반드시 입력해야 합니다. 입력하신 정보는 관리자의 검토 후 승인됩니다. 또한 원활한 정보공유를 위하여 본 사이트의 회원들에게 제공될 수 있습니다.
					</p>
					<label class="ness"><input type="checkbox" name="agreement"> 동의합니다.</label>
				</fieldset>
				<fieldset>
					<input type="submit" id="submit" value="등록 신청">
					<input type="button" id="cancel" value="취소">
				</fieldset>
			</form>
		</div>			
		';
		return $out;
	}

	public function show_directory() {
		$this->enqueue_scripts();
		global $wpdb;

		(int) $c_no = (null !== intval($_GET['c_no'])) ? get_query_var('c_no',intval($_GET['c_no'])) : null;
		(int) $ref = (null !== intval($_GET['ref'])) ? get_query_var('ref', intval($_GET['ref'])) : null;
		(int) $ref_n = (null !== intval($_GET['ref_n'])) ? get_query_var('ref_n', intval($_GET['ref_n'])) : null;
		(int) $lev = (null !== intval($_GET['lev'])) ? get_query_var('lev', intval($_GET['lev'])) : null;
		(int) $step = (null !== intval($_GET['step'])) ? get_query_var('step', intval($_GET['step'])) : null;

		$out = '<div class="dw-container">';
		$out .= $this->get_search_form();

		if (!$lev && !$c_no && !$ref ) {
			$out .= '<div class="first-node">'.$this->get_first_cat().'</div>';
		}
		else {
			if ( $lev == 0 && !isset($ref_n) ){
				$out .= $this->get_sub_category($c_no, $ref, $lev);
			}
			elseif ( isset($ref_n) && $ref_n != '' ){
				$out .= $this->get_sub_category($ref_n, $ref, $lev);
			}
			elseif ($lev > 0) {
				$out .= $this->get_data($c_no, $ref, $lev);
			}
		}

		$out .= $this->show_function_button();

		$out .= '</div>';
		return $out;
	}

	public function regist_shortcode() {
		add_shortcode('dw-directory', array($this, 'show_directory'));
		$this->enqueue_styles();
		$this->enqueue_scripts();
	}

}
