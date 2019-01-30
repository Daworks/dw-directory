(function( $ ) {
	'use strict';

	function get_item_list(c_no, ref,ref_n,page) {

		if ( page === undefined ) {
			page = 1;
		}

		var d = {
			'action':'dw_get_item_list', 
			'c_no':c_no, 
			'ref':ref, 
			'ref_n':ref_n,
			'page':page
		}

		var total_items = 0;

		$.post(ajaxurl,d, function(r){

			var dd = $.parseJSON(r);
			if ( dd.status === 'success' && dd.data.length > 0 ){
				total_items = dd.total;

				// 테이블 렌더링
				render_table(dd.data, total_items);
				
				// 페이지 렌더링
				render_pagination(total_items, c_no, ref, ref_n, page);
			}
			else if ( dd.status === 'success' && dd.data.length === 0){
					show_modal('안내', '출력할 결과가 없습니다.');
				}
			else if ( dd.status ==='fail' ){
				// 에러시 처리
				if (dd.data == '' ) {
					dd.data = "데이터가 정상적으로 로딩되지 못했습니다.";
					dd.data+= dd.q;
				}

				show_modal('오류', '콘솔을 확인하세요.');
				console.warn(dd.data);
			}
			else {
				// 에러시 처리
				show_modal('오류', '콘솔을 확인하세요');
				console.warn(r);
			}
		});
	}

	// 페이지 렌더링
	function render_pagination(total, c_no, ref, ref_n, page){

		var num = total; // 총 아이템 수
		var list = 20; // 페이지 당 아이템 수
		var block = 10;  // 페이지 블럭 당 페이지 수
		var pageNum = Math.ceil(num/list); // 총 페이지 수
		var blockNum = Math.ceil(pageNum/block); // 총 페이지 블럭 수
		var nowBlock = Math.ceil(page/block);
		
		var s_page = (nowBlock * block) - (block-1);
		if (s_page <= 1) {
			s_page = 1;
		}
		var e_page = nowBlock * block;
		if (pageNum <= e_page) {
			e_page = pageNum;
		}

		var out = new Array();
		out.push(
			'<ul data-cno="'+c_no+'" data-ref="'+ref+'" data-refn="'+ref_n+'">'+
				'<li class="start" data-page="1">'+
					'<a href="#!">처음</a>'+
				'</li>'
				);

		if (page > 1){
			out.push('<li class="prev" data-page="'+(page-1)+'">이전</li>');
		}

		for (var p = s_page; p <= e_page; ++p ){
			var active = (page === p)?"active":"";
			out.push('<li data-page="'+p+'" class="'+active+'"><a href="#!">'+p+'</a></li>');
		}

		if ( pageNum > page ) {
			out.push('<li class="next" data-page="'+(e_page+1)+'">다음</li>');
		}
		var final = (page === pageNum)?"active":"";
		out.push('<li class="end" data-page="'+pageNum+'" class="'+active+'">마지막</li></ul>');

		$('#pagination').empty().append(out.join(''));
	}

	// 페이지 이동
	$('#pagination li').live('click', function(){

		var d = $(this).parent().data();

		var c_no = ($(this).parent().data('cno') === "undefined") ? null : $(this).parent().data('cno');
		var ref = ($(this).parent().data('ref') === "undefined") ? null : $(this).parent().data('ref');
		var ref_n = ($(this).parent().data('refn') === "undefined") ? null : $(this).parent().data('refn');
		var page = ($(this).data('page') === "undefined") ? null : $(this).data('page');

		get_item_list(c_no, ref, ref_n, page);

		false === $(this).hasClass('active') ? $(this).addClass('active') : '';
	});

	// 키워드 검색 결과
	$('#search-submit').click(function(){
		var obj = $('#search-wrap .s-type:nth-child(1) input[name="search-keyword"]');
		get_search_result(obj);
	});

	function get_search_result(obj){
		var keyword = obj.val();
		var d = {
			'action':'dw_search_item',
			'keyword' : keyword
		}
		$.post(ajaxurl, d, function(r){

			var res = $.parseJSON(r);
			if (res.status === 'success')
			{
				render_table(res.data, res.total);
				$('#pagination').hide();
			}
			else if ( res.status === 'fail' )
			{
				show_modal('오류', res.data)
			}
			else 
			{
				show_modal('오류', r);
			}
		});
	}

	function show_modal(title, content){
		$('.modal-title').empty().text(title);
		$('.modal-content').empty().text(content);
		$('.modal').show();
	}

	function render_table(obj, total){
		
		var out = new Array();
		
		out.push(
			'<tr><td colspan="6" id="search-total-count" align="center"></td></tr>'
			);

		if (total > 0) {
			for ( var i=0; i < obj.length; ++i ){
				if ( obj[i].content.length > 50 ){
					var content = obj[i].content.substr(50)+'...';
				}
				else {
					var content = obj[i].content;
				}
				out.push(
					'<tr>'+
					'<td><input type="checkbox" name="checknum[]" data-num="'+obj[i].num+'" value="'+obj[i].num+'"></td>'+
					'<td>'+obj[i].num+'</td>'+
					'<td>'+obj[i].title+'</td>'+
					'<td>'+obj[i].homepage+'</td>'+
					'<td>'+obj[i].c_title+'</td>'+
					'<td><div class="user"><a href="#!"><i class="fa fa-user" aria-hidden="true"></i></a>'+
					'<span class="user-info">신청 : '+obj[i].name+'<br>'+'이메일 : '+obj[i].email+'</span>'+
					content+'</td>'+
					'</tr>'
					);
			}
			$('#item-list .list tbody').empty().html(out.join(''));
			$('#search-total-count').empty().text(total+'개의 항목이 검색되었습니다.');
		}
		else {
			$('#search-total-count').empty().text('검색 결과가 없습니다.');
		}

	}

	// 카테고리 셀렉터 검색
	// 상단 카테고리 셀렉터 로딩
	$('.s-type #cat-lev-0').change(function(){
		var target = $('select#cat-lev-1');
		get_cat_tree($(this).val(), target);
	});

	$('.s-type select[name="cat-lev-1"]').change(function(){
		var target = $('.s-type #cat-lev-2');
		get_cat_tree($(this).val(), target);
	});

	$('#submit-cat-search').click(function(){
		var v1 = $('#cat-lev-0').val();
		var v2 = $('#cat-lev-1').val();
		var v3 = $('#cat-lev-2').val();
		search_item_by_cat(v1, v2, v3);
	});

	function search_item_by_cat(v1, v2, v3){
		//카테고리 셀렉터로 아이템 검색
		if ( !v1 && !v2 && !v3 ) {
			alert('카테고리를 선택하세요.');
		}
		else {
			var d = {
				'action' : 'dw_search_item_by_cat',
				'data' : {'c1': v1, 'c2':v2, 'c3':v3 }
			}
			v1 = (v1 === 'undefined' || v1 === '') ? null : v1;
			v2 = (v2 === 'undefined' || v2 === '') ? null : v2;
			v3 = (v3 === 'undefined' || v3 === '') ? null : v3;

			// console.log({'c_no': v1, 'ref':v2, 'ref_n':v3 });
			get_item_list(v1, v2, v3);
		}
	}

	function get_cat_tree(c_no, target){
		var opt_default = '<option value="">카테고리 선택</option>';
		var opt_no = '<option value="">카테고리 없음</option>';
		var opt = opt_default;

		var d = {
			'action':'dw_get_single_category', 
			'c_no':c_no 
		}

		$.post(ajaxurl, d, function(r){
			var rr = $.parseJSON(r);
			
			if (rr.status === 'success' ){
				cb_attach_value(target, rr.data);
			}
			else if (rr.status === 'fail'){
				alert(rr.data);
			}
		});
	}

	function cb_attach_value(obj, val){
		var opt = new Array();
		if ( val.length > 1 ) {
			$.each(val, function(i,v){
				opt.push('<option value="'+v.c_no+'">'+v.c_title+'</option>');
			});
			obj.empty().append(opt.join(''));
		}
	}


	// 아이템 추가, 수정, 삭제 버튼 동작 설정
	$('.new-item').click(function(){
		$('#action-area').removeClass('hide');
		
		$('#edit input[name="mode"]').val('new');

		$.post(ajaxurl, {'action':'get_cat_root'}, function(r){
			var res = $.parseJSON(r);
			if (res.status === 'success') {
				$('#edit select:nth-child(1)').empty().append(res.data);
			}
			else {
				show_modal('오류', '1단계 카테고리 로딩 오류 밟생');
			}
		});
		
	});

	$('#edit select:nth-child(1)').change(function(){
		$.post(ajaxurl,{'action':'dw_get_single_category', 'c_no':$(this).val()}, function(r){
			if ($.parseJSON(r).status === 'success') {
				cb_attach_value($('#edit select:nth-child(2)'), $.parseJSON(r).data);
			}
		})
	});

	$('#edit select:nth-child(2)').change(function(){
		$.post(ajaxurl,{'action':'dw_get_single_category', 'c_no':$(this).val()}, function(r){
			if ($.parseJSON(r).status === 'success') {
				cb_attach_value($('#edit select:nth-child(3)'), $.parseJSON(r).data);
			}
		})
	});

	$('#edit-form').submit(function(e){
		e.preventDefault();

		var d = {
			'action' : 'dw_add_directory_item',
			'data' : {
				'mode':$(this).find('input[name="mode"]').val(),
				'num':$(this).find('input[name="num"]').val(),
				'c_no':$(this).find('input[name="c_no"]').val(),
				'ref':$(this).find('input[name="ref"]').val(),
				'ref_n':$(this).find('input[name="ref_n"]').val(),
				'lev':$(this).find('input[name="lev"]').val(),
				'title':$(this).find('input[name="title"]').val(),
				'url':$(this).find('input[name="url"]').val(),
				'content':$(this).find('textarea[name="content"]').val(),
				'cat1':$(this).find('select[name="cat-lev-1"]').val(),
				'cat2':$(this).find('select[name="cat-lev-2"]').val(),
				'cat3':$(this).find('select[name="cat-lev-3"]').val(),
				'name':$(this).find('input[name="name"]').val(),
				'email':$(this).find('input[name="email"]').val()
			}
		}

		//폼 검증
		if ( $(this).find('input[name="title"]').val() === null ) {
			alert('제목을 입력하세요.');
			$(this).find('input[name="title"]').focus();
		}
		if ( $(this).find('input[name="url"]').val() === null ) {
			alert('홈페이지 주소를 입력하세요.');
			$(this).find('input[name="url"]').focus();
		}
		if ( $(this).find('select[name="cat-lev-1"]').val() === null ) {
			alert('1단계 카테고리는 입력해야 합니다.');
			$(this).find('select[name="cat-lev-1"]').focus();
		}

		$.post(ajaxurl, d, function(res){

			var r = $.parseJSON(res);
			if ( r.stat === 'success' ) {
				var msg = (d.data.mode === 'new') ? "새 아이템이 추가되었습니다." : "수정되었습니다.";
				if ( confirm(msg) ) {
					$('#search-keyword').val(d.data.url);
					$('#search-submit').trigger('click');
					$('#action-area input, #action-area textarea, #action-area select').val('');
					$('#action-area').hide();
				}
			}
			else if (r.stat === 'fail' ) {
				show_modal('오류', r.data);
				console.warn(r.cat);
			}
			else {
				show_modal('오류', res);
			}
		});
		
	});

	$('#item-list #check-all').click(function(){
		
		if ( $(this).prop('checked') ){
			$('input[type="checkbox"]').prop('checked', true);
		}
		else {
			$('input[type="checkbox"]').prop('checked', false);
		}
	});

	$('.fn-wrap .edit-item').click(function(){
		try {
			if ( $('#item-list input[type="checkbox"]:checked').length > 1 ) {
				$('#item-list input[type="checkbox"]').prop('checked', false);
				throw ('수정할 아이템을 한 개만 체크하세요.');
			}

			if ( $('#item-list input[type="checkbox"]:checked').length === 0 ) {
				throw ('수정할 아이템을 하나 체크하세요.');
			}

			var checkedItem = $('#item-list input[type="checkbox"]:checked').data('num');
			$.post(ajaxurl, {'action':'dw_get_single_item', 'num':checkedItem}).done(function(r){
				var res = $.parseJSON(r);

				if (res.status === 'success'){

					var output = {
						'num':res.data.num,
						'c_no':res.data.c_no,
						'ref':res.data.ref,
						'ref_n':res.data.ref_n,
						'lev':res.data.lev,
						'url':res.data.homepage,
						'name':res.data.name,
						'email':res.data.email,
						'title':res.data.title,
						'content':res.data.content
					}

					$.post(ajaxurl, {'action':'get_cat_root'}, function(r){
						var res = $.parseJSON(r);
						if (res.status === 'success') {
							$('#edit select:nth-child(1)').empty().append(res.data);
						}
						else {
							show_modal('오류', '1단계 카테고리 로딩 오류 밟생');
						}
					});

					$('#edit-form input[name="mode"]').val('edit');
					$('#edit-form input[name="num"]').val(output.num);
					$('#edit-form input[name="c_no"]').val(output.c_no);
					$('#edit-form input[name="ref"]').val(output.ref);
					$('#edit-form input[name="ref_n"]').val(output.ref_n);
					$('#edit-form input[name="lev"]').val(output.lev);
					$('#edit-form input[name="title"]').val(output.title);
					$('#edit-form input[name="url"]').val(output.url);
					$('#edit-form textarea[name="content"]').val(output.content);
					
					// 셀렉트 박스 적용
					setTimeout(function(){
						$('#edit-form select[name="cat-lev-1"]').val(output.ref).trigger('change');
					},100);

					if ( parseInt(output.lev) === 1 ) {
						setTimeout(function(){
							$('#edit-form select[name="cat-lev-2"]').val(output.c_no).trigger('change');
						},200);
					}
					else if (parseInt(output.lev) > 1) {
						setTimeout(function(){
							$('#edit-form select[name="cat-lev-2"]').val(output.ref_n).trigger('change');
						},200);
						setTimeout(function(){
							$('#edit-form select[name="cat-lev-3"]').val(output.c_no);
						},300);
					}

					$('#action-area').removeClass('hide');
				}
				else{
					throw (res.data);
				}
			});
		}
		catch(err){
			show_modal('오류', err);
		}
	});

	$('#item-list .del-item').click(function(){
		var num = $('#item-list input[name="checknum[]"]:checked').length;
		var nums = new Array();
		$('#item-list input[name="checknum[]"]:checked').each(function(i,v){
			nums.push(v.value);
		});
		
		var d = {
			'action':'dw_del_item',
			'num':nums
		}
		$.post(ajaxurl, d, function(res){
			if (res === 'success'){
				show_modal('','정상적으로 삭제되었습니다.');
				location.reload();
			}
			else if(res === 'fail'){
				show_modal('오류', '삭제 실패');
			}
			else {
				show_modal('오류', '콘솔을 확인하세요.');
				console.warn(res);
			}
		});
	});

	$('.user').live('hover',function(){
		$(this).children('.user-info').toggle();
	});

	$('#action-area .close, #action-area #cancel').click(function(){
		$('#action-area').addClass('hide');
	});

	$('#modal-close').click(function(){
		$('.modal').hide();
	});



	$(document).ready(function(){
		get_item_list();

		var cat_opt_root = $.post(ajaxurl,{'action':'get_cat_root'}, function(r){
								var res = $.parseJSON(r);
								if ( res.status === 'success' ) {
									$('.s-type select[name="cat-lev-0"]').empty().append(res.data);
								}
								else {
									alert(res.data);
								}
							});
	});

})( jQuery );