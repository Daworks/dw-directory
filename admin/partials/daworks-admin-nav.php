<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://daworks.io
 * @since      1.0.0
 *
 * @package    Daworks
 * @subpackage Daworks/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<nav>
	<ul class="btn-set">
		<li><a href="<?php menu_page_url( 'dw-directory-standby-slug', 1 ); ?>" class="btn"><i class="fa fa-barcode"></i> 등록 대기 관리</a></li>
		<li><a href="<?php menu_page_url( 'dw-directory-manage-cat-slug', 1 ); ?>" class="btn"><i class="fa fa-archive"></i> 카테고리 관리</a></li>
		<li><a href="<?php menu_page_url( 'dw-directory-manage-item-slug', 1 ); ?>" class="btn"><i class="fa fa-file-text"></i> 아이템 관리</a></li>
	</ul>
</nav>