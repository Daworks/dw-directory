<?php 

require plugin_dir_path(__FILE__).'library/class-daworks-library.php';
$lib = new Daworks_Library();
define("__IMG", plugin_dir_url(__FILE__).'../img');
wp_enqueue_style( 'dw-admin-css', plugin_dir_url(__FILE__).'../css/daworks-admin.css'); 
wp_enqueue_style( 'dw-admin-css', plugin_dir_url(__FILE__).'../css/daworks-admin.css');
wp_enqueue_style( 'fontawesome', plugin_dir_url(__FILE__).'../../bower_components/font-awesome/css/font-awesome.min.css');
wp_enqueue_script('jquery');
wp_enqueue_script( 'dw-admin-common-js', plugin_dir_url(__FILE__).'../js/daworks-admin-common.js');
wp_enqueue_script( 'dw-admin-item-js', plugin_dir_url(__FILE__).'../js/daworks-admin-item.js');


?>

<h1>아이템 관리</h1>
<div class="dw-admin-container">
	<?php 
		require plugin_dir_path(__FILE__) . 'daworks-admin-nav.php';
	?>
<!-- 	<div class="infobox">

	</div> -->
	<section id="search-wrap" class="infobox">
		<div class="s-type">
			<label for="search-keyword">사이트명 또는 URL로 검색</label>
			<input type="text" name="search-keyword" id="search-keyword" placeholder="사이트명 또는 URL로 검색 예) www.abc.com">
			<button class="btn" id="search-submit">검색</button>
		</div>
		<div class="s-type">
			<label for="search-cat">카테고리로 검색</label>
			<select name="cat-lev-0" id="cat-lev-0">
				<option value="">1단계 카테고리</option>
			</select>
			<select name="cat-lev-1" id="cat-lev-1">
				<option value="">2단계 카테고리</option>
			</select>
			<select name="cat-lev-2" id="cat-lev-2">
				<option value="">3단계 카테고리</option>
			</select>
			<button class="btn" id="submit-cat-search">검색</button>
		</div>
	</section>
	<section id="item-list">
		<div class="fn-wrap">
			<button class="btn success-bg new-item">새 항목 추가</button>
			<button class="btn warning-bg edit-item">수정</button>
			<button class="btn danger-bg del-item">삭제</button>
			<span id="total-count"></span>
		</div>
		<table class="list">
			<colgroup>
				<col width="40">
				<col width="5%">
				<col width="20%">
				<col width="20%">
				<col width="20%">
				<col width="*">
			</colgroup>
			<thead>
				<tr>
					<td>
						<input type="checkbox" name="check-all" id="check-all">
					</td>
					<td>no</td>
					<td>제목</td>
					<td>URL</td>
					<td>분류</td>
					<td>설명</td>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td colspan="6" align="center">
						<i class="fa fa-circle-o-notch fa-spin fa-fw"></i> 데이터를 로딩 중입니다....
					</td>
				</tr>
			</tbody>
		</table>
		<!-- 페이지번호 -->
		<div id="pagination"></div>
		<div class="fn-wrap-bottom">
			<div class="fn-wrap">
				<button class="btn success-bg new-item">새 항목 추가</button>
				<button class="btn warning-bg edit-item">수정</button>
				<button class="btn danger-bg del-item">삭제</button>
			</div>
		</div>
	</section>
	<section id="action-area" class="hide">
		<div id="edit">
			<form id="edit-form">
			<div class="close"><i class="fa fa-window-close" aria-hidden="true"></i></div>
			<h3>새 디렉토리 항목 추가</h3>
			<input type="hidden" name="mode" value="">
			<input type="hidden" name="num" value="">
			<input type="hidden" name="c_no" value="">
			<input type="hidden" name="ref" value="">
			<input type="hidden" name="ref_n" value="">
			<input type="hidden" name="lev" value="">
			<table>
				<tbody>
					<tr>
						<td>제목</td>
						<td><input type="text" name="title" id="edit-title" placeholder="추가할 아이템 제목..."></td>
					</tr>
					<tr>
						<td>URL</td>
						<td><input type="text" name="url" id="edit-url" value="http://"></td>
					</tr>
					<tr>
						<td>설명</td>
						<td><textarea name="content" id="edit-content" placeholder="간단한 설명을 추가하세요..."></textarea></td>
					</tr>
					<tr>
						<td>분류설정</td>
						<td>
							<select name="cat-lev-1" id="edit-cat1">
								<option value="">1단계 카테고리</option>
							</select>
							<select name="cat-lev-2" id="edit-cat2">
								<option value="">2단계 카테고리</option>
							</select>
							<select name="cat-lev-3" id="edit-cat3">
								<option value="">3단계 카테고리</option>
							</select>
						</td>
					</tr>
					<tr>
						<td>신청자명</td>
						<td>
							<input type="text" name="name" id="edit-name" placeholder="신청자 이름 입력...">
						</td>
					</tr>
					<tr>
						<td>신청자 이메일</td>
						<td>
							<input type="email" name="email" id="edit-email" placeholder="user@email.com">
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<button id="submit" class="btn" type="submit">저장</button>
							<button id="cancel" class="btn" type="button">취소</button>
						</td>
					</tr>
				</tbody>
			</table>
			</form>
		</div>
	</section>

	<div class="modal">
		<div class="modal-container">
			<div class="modal-title"></div>
			<div class="modal-content"></div>
			<span><a href="#!" id="modal-close">닫기</a></span>
		</div>
	</div>
</div>