<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://daworks.org
 * @since      1.0.0
 *
 * @package    Daworks
 * @subpackage Daworks/admin/partials
 */
require plugin_dir_path(__FILE__).'library/class-daworks-library.php';
$lib = new Daworks_Library();

wp_enqueue_style( 'dw-admin-css', plugin_dir_url(__FILE__).'../css/daworks-admin.css');
wp_enqueue_style( 'fontawesome', plugin_dir_url(__FILE__).'../../bower_components/font-awesome/css/font-awesome.min.css');
wp_enqueue_script('jquery');
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<h1>디렉토리 서비스 :: 등록대기 관리</h1>
<div class="dw-admin-container">
	<?php 
		require plugin_dir_path(__FILE__) . 'daworks-admin-nav.php';
	?>
	<section class="infobox">
		<ul>
				<li><b>사용방법</b></li>
				<li>
					아이템 등록 허용 : 아이템의 카테고리를 지정한 후 허용 버튼 클릭
				</li>
				<li>
					아이템 등록 보류 : 아이템의 카테고리를 지정한 후 허용 버튼 클릭. 보류는 카테고리는 지정하되 화면에 출력하지 않도록 합니다.
				</li>
				<li>
					아이템 삭제 : 해당 아이템의 삭제 버튼을 클릭.
				</li>
		</ul>
	</section>
	<section id="message-box" class="hide">
		<button class="close"><i class="fa fa-times" aria-hidden="true"></i></button>
		<p></p>
	</section>
	<section id="standby-list">
		<table>
			<colgroup>
				<col width="50">
				<col width="200">
				<col width="200">
				<col width="180">
				<col width="120">
				<col width="*">
				<col width="160">
			</colgroup>
			<thead>
				<tr>
					<td>No</td>
					<td>제목</td>
					<td>URL</td>
					<td>신청인</td>
					<td>날짜</td>
					<td colspan="2">처리</td>
				</tr>
			</thead>
			<tbody>
				<?php
					$list = $lib->get_standby_list();
					if ( !empty($list) ) :
						$no = 1;
					foreach ($list as $row){
				?>
					<tr class="num-<?php echo $row->num ?>">
						<?php 
							if ($row->request_new_cat && $row->content) {
								$span = "3";
							} 
							elseif ( (!$row->request_new_cat && $row->content) || ($row->request_new_cat && !$row->content) ){
								$span = "2";
							}
							else {
								$span = "1";
							}
						?>
						<td rowspan="<?php echo $span; ?>" align="center"><?php echo $no; ++$no; ?></td>
						<td><strong><?php echo $row->title; ?></strong></td>
						<td><a href="<?php echo $row->homepage; ?>" target="_blank"><?php echo $row->homepage; ?></a></td>
						<td><?php echo $row->name . "<br>".$row->email; ?></td>
						<td><?php echo $row->indate; ?></td>
						<td id="num-<?php echo $row->num ?>">
							<select name="cat_lev1" data-num-id="<?php echo $row->num ?>">
								<option value="">1단계 카테고리</option>
							<?php 
								$lev1_options = $lib->get_cat_lev1();
								if ( $lev1_options ){
									$tags = '';
									foreach ($lev1_options as $option){
										if ( $row->ref == $option->c_no ) {
											$selected = "selected";
										}
										else {
											$selected = "";
										}
										$tags .= '<option value="'.$option->c_no.'" '.$selected.'>'.$option->c_title.'</option>';
									}
								}
								else {
									$tags .= "<option selected>데이터 없음</option>";
								}
								echo $tags;
							 ?>
							</select>
							<select name="cat_lev2" data-num-id="<?php echo $row->num ?>" data-cno="<?php echo $row->c_no; ?>">
								<option value="">2단계 카테고리</option>
							</select>
							<select name="cat_lev3" data-num-id="<?php echo $row->num ?>" data-cno="<?php echo $row->c_no; ?>">
								<option value="">3단계 카테고리</option>
							</select>
						</td>
						<td>
							<button class="btn apply" data-item-id="<?php echo $row->num; ?>">허용</button>
							<button class="btn standby" data-item-id="<?php echo $row->num; ?>">보류</button>
							<button class="btn reject" data-item-id="<?php echo $row->num; ?>">삭제</button>
						</td>
					</tr>
					<?php if ( $row->request_new_cat ) : ?>
						<td colspan="6">
							<p class="rq-new-cat"><i class="fa fa-spinner fa-pulse fa-fw"></i> 새로운 카테고리 등록 요청 : <?php echo $row->request_new_cat; ?></p>
						</td>
					<?php endif; ?>
					<?php if ($row->content) : ?>
					<tr class="num-<?php echo $row->num ?>">
						<td colspan="6">
							<p><i class="fa fa-file-text" aria-hidden="true"></i>&nbsp;&nbsp; <?php echo $row->content; ?></p>
						</td>
					</tr>
				<?php endif; ?>
				<?php
					}
					
					else : 
				?>
					<tr>
						<td colspan="6" align="center">등록 대기 중인 아이템이 없습니다.</td>
					</tr>
				<?php
					endif;
				?>
			</tbody>
		</table>
	</section>
	<?php 
		require plugin_dir_path(__FILE__) . 'daworks-admin-footer.php';
	?>
</div>

<?php 
wp_enqueue_script( 'dw-admin-js', plugin_dir_url(__FILE__).'../js/daworks-admin.js');
 ?>
