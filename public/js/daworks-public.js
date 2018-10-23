(function($) {
	'use strict';
	var ajaxurl = '/wp-admin/admin-ajax.php';

	function add_root_option(r, target){
		if ($.parseJSON(r).status==='success'){
			target.empty().append($.parseJSON(r).data);
		}
		else{
			alert('오류 발생 : 콘솔을 참고하세요.');
			console.warn(r);
		}
	}

	function add_option(res, target){
		var r = $.parseJSON(res);
		var opts = new Array();
		if (r.status ==='success'){
			$.each(r.data, function(i,v){
				opts.push(
					'<option value="'+v.c_no+'">'+v.c_title+'</option>'
					);
			});
			if (opts.length > 0){
				target.empty().append(opts.join(''));
			}
		}
		else {
			console.warn(res);
		}
	}

	function searchByKeyword(keyword, target){
		$.post(ajaxurl, {'action':'dw_search_item', 'keyword':keyword}).done(function(res){
			var r = $.parseJSON(res);
			var out = new Array();
			if ( r.status === 'success') {
				if ( r.data.length > 0 ) {
					out.push('<p class="searchinfo">총 '+r.total+'개의 항목이 검색되었습니다.</p>');
					$.each(r.data, function(i,v){
						out.push('<article>');
						out.push('<div class="no">'+(i+1)+'</div>');
						out.push(
							'<dl><dt>'+v.title+'<a href="'+v.homepage+'" target="_blank">'+v.homepage+'</a></dt><dd><p>'+v.c_title+'</p><p>'+v.content+'</p></dd></dl>');
						out.push('</article>');
					});
					out.push('<p class="reload"><a href="#!">다시 검색하기</a></p>');
				}
				else {
					out.push('<p align="center">검색 결과가 없습니다. 다른 검색어를 입력해보세요.</p>');
				}
				target.empty().append(out.join('')).fadeIn();
				target.css('border','2px solid silver');
			}

		});
	}

	$(function(){
		// 키워드 검색
		$('#search_item_form').submit(function(e){
			e.preventDefault();
			var keyword = $(this).find('input[name="keyword"]').val();
			if ( keyword === '' || keyword === undefined || keyword === null ) {
				alert('검색어를 입력하세요.');
				$(this).find('input[name="keyword"]').focus();
			}
			else {
				searchByKeyword(keyword, $('#search-result'));
			}
		});

		$('#search-result p.reload').live('click', function(){
			$('#search-result').empty().fadeOut();
			$(this).find('input[name="keyword"]').val('').focus();
		});

		// 1단계 카테고리 로딩
		$.post(ajaxurl, { 'action':'get_cat_root' }).done(function(r){
			add_root_option(r, $('#add_directory_item select#cat1'));
		});

		// 2단계 카테고리 로딩
		$('#add_directory_item #cat1').on('change', function(){
			$.post(
				ajaxurl, 
				{
					'action':'dw_get_single_category', 
					'c_no':$('select#cat1').val()
				}
				).done(function(res){
					add_option(res, $('#add_directory_item select#cat2'));
				});

			});

		// 3단계 카테고리 로딩
		$('#add_directory_item #cat2').on('change', function(){
			$.post(
				ajaxurl, 
				{
					'action':'dw_get_single_category', 
					'c_no':$('select#cat2').val()
				}
				).done(function(res){
					add_option(res, $('#add_directory_item select#cat3'));
				});
			});

		$('#apply-dir-item').click(function(){
			$('#new-category-form').removeClass('hide');
		});

		$('#new-category-form #cancel').click(function(){
			$('#new-category-form').hide();
		});

		// 새 카테고리명 신청란 보여주기
		$('#add_directory_item #rqst-new-cat').click(function(){
			$('#new-cat-field').toggleClass('hide');
			$('input[name="c_title"]').focus();
		});

		// 신청서 폼 전송
		$('#add_directory_item').submit(function(e){
			e.preventDefault();

			var d = {
				'action':'dw_apply_directory_item',
				'data' : {
					'cat1':$(this).find('#cat1').val(),
					'cat2':$(this).find('#cat2').val(),
					'cat3':$(this).find('#cat3').val(),
					'cat_new':$(this).find('input[name="c_title"]').val(),
					'title':$(this).find('input[name="title"]').val(),
					'url':$(this).find('input[name="homepage"]').val(),
					'content':$(this).find('textarea[name="content"]').val(),
					'name':$(this).find('input[name="name"]').val(),
					'email':$(this).find('input[name="email"]').val(),
				}
			}

			//폼 체크
			if (d.data.cat1 ==='' ) {
				if (d.data.cat_new === '' ) {
					alert('카테고리 정보를 선택하거나 새 카테고리를 입력하세요.');
					$(this).find('#cat1').focus();
				}
			}
			else if ( d.data.title ==='' ) {
				alert('제목을 입력하세요.');
				$(this).find('input[name="title"]').focus();
			}
			else if ( d.data.url ==='' || d.data.url ==='http://' ) {
				alert('홈페이지 주소를 입력하세요.');
				$(this).find('input[name="homepage"]').focus();
			}
			else if (d.data.name === '') {
				alert('신청자 이름을 입력하세요.');
				$(this).find('input[name="name"]').focus();
			}
			else if (d.data.email === ''){
				alert('신청자 이메일을 입력하세요.');
				$(this).find('input[name="email"]').focus();
			}
			else if (false === $(this).find('input[name="agreement"]:checked' )){
				alert('약관에 동의하셔야 등록 신청 가능합니다.');
				$(this).find('input[name="agreement"]').focus();
			}
			else {
				$.post(ajaxurl, d).done(function(res){

					if (res === 0) {
						alert('ajax 오류 발생');
					}
					else {
						var r = $.parseJSON(res);
						if (r.stat ==='success'){
							alert('신청이 완료되었습니다.');
							$('#new-category-form').fadeOut();
						}
						else {
							alert('오류 발생 : '+r.data);
						}
					}

				});
			}
		});

	});

	
	

})( jQuery );