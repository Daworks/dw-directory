<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       http://daworks.org
 * @since      1.0.0
 *
 * @package    Daworks
 * @subpackage Daworks/public/partials
 */

?>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="dw-container">
    <div id="search-item">
        <form name="search_item_form" id="search_item_form">
            <label for="keyword">디렉토리 항목 찾기 : 검색어를 입력하세요.</label>
            <input type="text" name="keyword" id="keyword">
            <button class="btn" type="submit">검색</button>
        </form>
    </div>

    <div id="search-result" class="hide"></div>

    <?php if (!$lev && !$c_no && !$ref) : ?>
        <div class="first-node"><?php echo $this->get_first_cat() ?></div>
    <?php else : ?>
        <?php if ( $lev == 0 && !isset($ref_n) ) : ?>
            <?php echo $this->get_sub_category($c_no, $ref, $lev) ?>
        <?php elseif ( isset($ref_n) && $ref_n != '' ) : ?>
            <?php echo $this->get_sub_category($ref_n, $ref, $lev) ?>
        <?php elseif ($lev > 0) : ?>
            <?php echo $this->get_data($c_no, $ref, $lev) ?>
        <?php endif ?>
    <?php endif ?>
	
	<?php if (is_user_logged_in()) : ?>
        <div class="dw-button-set" id="dw-control">
            <button id="apply-dir-item" type="button" class="new" onclick="toggleNewCategoryForm()">홈페이지 등록 신청</button>
        </div>
	<?php else : ?>
        <p><a href="<?php echo wp_login_url()?>">로그인</a> 후 등록 신청 하실 수 있습니다.</p>
	<?php endif ?>
    
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
                <input type="text" name="title" placeholder="홈페이지 이름을 입력하세요.">
            </fieldset>
            <fieldset>
                <label class="ness">홈페이지 주소</label>
                <input type="text" name="homepage" value="" placeholder="https://abc.com">
            </fieldset>
            <fieldset>
                <label>간단한 설명</label>
                <textarea name="content" id="dw-content"></textarea>
            </fieldset>
            <fieldset>
                <label class="ness">신청자 이름</label>
                <input type="text" name="name" id="dw-name" value="<?php echo $user_name ?>">
            </fieldset>
            <fieldset>
                <label class="ness">신청자 이메일</label>
                <input type="email" name="email" id="dw-email" placeholder="your@email.com" value="<?php echo $user_email ?>">
            </fieldset>
            <fieldset>
                <p class="agree">
                    <span class="ness"></span>표시된 항목은 반드시 입력해야 합니다. 입력하신 정보는 관리자의 검토 후 승인됩니다. 또한 원활한 정보공유를 위하여 본 사이트의 회원들에게 제공될 수 있습니다.
                </p>
                <label class="ness"><input type="checkbox" name="agreement"> 동의합니다.</label>
            </fieldset>
            <fieldset class="text-center">
                <button type="submit">등록 신청</button>
                <button type="reset">취소</button>
            </fieldset>
        </form>
    </div>
</div>

<template id="search-item-tpl">
    <p class="searchinfo"></p>
    <div class="article">
        <div class="no"></div>
        <dl>
            <dt>
                <span class="title"></span>
                <a href="" target="_blank" class="homepage"></a>
            </dt>
            <dd>
                <p></p>
            </dd>
        </dl>
    </div>
    <p class="reload"><a href="javascript:;" onclick="resetSearchForm()">다시 검색하기</a></p>
    <p id="no-result" class="text-center">검색 결과가 없습니다. 다른 검색어를 입력해보세요.</p>
</template>
