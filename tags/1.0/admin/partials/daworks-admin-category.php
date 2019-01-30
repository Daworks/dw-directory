<?php

/**
 * 카테고리 관리용 페이지
 *
 * @link       http://daworks.org
 * @since      1.0.0
 *
 * @package    Daworks
 * @subpackage Daworks/admin/partials
 */
require plugin_dir_path(__FILE__).'library/class-daworks-library.php';
$lib = new Daworks_Library();
define("__IMG", plugin_dir_url(__FILE__).'../img');

wp_enqueue_style( 'dw-admin-css', plugin_dir_url(__FILE__).'../css/daworks-admin.css');
wp_enqueue_style( 'fontawesome', plugin_dir_url(__FILE__).'../../bower_components/font-awesome/css/font-awesome.min.css');
wp_enqueue_script('jquery');
wp_enqueue_script( 'dw-admin-common-js', plugin_dir_url(__FILE__).'../js/daworks-admin-common.js');
wp_enqueue_script( 'dw-admin-cat-js', plugin_dir_url(__FILE__).'../js/daworks-admin-cat.js');
?>

<h1>디렉토리 서비스 :: 카테고리 관리</h1>
<div class="dw-admin-container">
	<?php 
		require plugin_dir_path(__FILE__) . 'daworks-admin-nav.php';
	?>
	<section class="infobox">
		<ul>
				<li><b>사용방법</b></li>
				<li>
					카테고리를 검색하거나 단계별로 카테고리를 선택하여 카테고리를 관리하세요.
				</li>
		</ul>
	</section>
	<section id="search-box" class="infobox">
		<div class="label-bg">검색</div>
		<div class="search-input">
			<input type="text" name="cat_search" placeholder="검색어 입력 후 엔터...">
			<button class="btn reset" >다시 검색</button>
		</div>
		<div class="exp">
		<p>
			<span><img src="<?php echo __IMG ?>/icon-warn.png" srcset="<?php echo __IMG ?>/icon-warn@2x.png 2x" alt=""> </span>
			검색된 카테고리명을 클릭하면 하단에 카테고리 분류에 반영됩니다.
		</p>
			
		</div>
		<div class="clear"></div>
	</section>

	<section id="search-result">
		<!-- 검색결과 출력 -->
		<p>키워드를 검색 또는 아래 셀렉트바를 이용하여 카테고리 선택하세요.</p>
	</section>

	<!-- 셀렉트 박스 처리 -->
	<section id="cat-select-box">
		<fieldset class="cat_lev">
			<label for="select-cat-lev-1">1차 카테고리</label>
			<select name="cat_lev1" id="select-cat-lev-1" class="lev-selector">
			<?php 
				$lists = $lib->get_cat_lev1();
				if ( $lists ) {
					$tags = "<option value=''>1차 카테고리 선택</option>";
					foreach ( $lists as $list ) {
						
						$tags .= '<option value="'.$list->c_no.'" data-ref="'.$list->c_no.'" data-ref-n="0" data-lev="0">'.$list->c_title.'</option>';
					}
				}
				else {
					$tags = "<option value=''>카테고리 정보 없음</option>";
				}
				$tags.= '<option value="add">카테고리 추가</option>';
				echo $tags;
			 ?>
			</select>
			<button class="btn edit" data-cat-tg="select-cat-lev-1">수정</button>
			<button class="btn del" data-cat-tg="select-cat-lev-1">삭제</button>
			<div class="cat1-new-item hide">
				<input type="text" name="new-cat" placeholder="새 항목 추가">
				<button class="btn add-new" data-lev="0">저장</button>
				<button class="btn edit-save hide">수정 확인</button>
			</div>
		</fieldset>

		<fieldset class="cat_lev">
			<label for="select-cat-lev-2">2차 카테고리</label>
			<select name="cat_lev2" id="select-cat-lev-2" class="lev-selector">
				<option value="">카테고리 선택</option>
			</select>
			<button class="btn edit" data-cat-tg="select-cat-lev-2">수정</button>
			<button class="btn del" data-cat-tg="select-cat-lev-2">삭제</button>
			<div class="cat2-new-item hide">
				<input type="text" name="new-cat" placeholder="새 항목 추가">
				<button class="btn add-new" data-lev="1">저장</button>
				<button class="btn edit-save hide">수정 확인</button>
			</div>
		</fieldset>

		<fieldset class="cat_lev">
			<label for="select-cat-lev-3">3차 카테고리</label>
			<select name="cat_lev3" id="select-cat-lev-3" class="lev-selector">
				<option value="">카테고리 선택</option>
			</select>
			<button class="btn edit" data-cat-tg="select-cat-lev-3">수정</button>
			<button class="btn del" data-cat-tg="select-cat-lev-3">삭제</button>
			<div class="cat3-new-item hide">
				<input type="text" name="new-cat" placeholder="새 항목 추가">
				<button class="btn add-new" data-lev="2">저장</button>
				<button class="btn edit-save hide">수정 확인</button>
			</div>
		</fieldset>
		<div class="clear"></div>
	</section>
	
	<!-- 메시지 박스 -->
	<section id="message-box" class="hide" style="margin-bottom:0">
		<button class="close"><i class="fa fa-times" aria-hidden="true"></i></button>
		<p></p>
	</section>


	<!-- Footer -->
	<?php 
		require plugin_dir_path(__FILE__) . 'daworks-admin-footer.php';
	?>
	<!-- Footer end -->
</div>