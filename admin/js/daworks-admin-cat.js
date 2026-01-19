(function( $ ) {
	'use strict';

	function showMsg(stat, content){
		$('#message-box').attr('class','').addClass(stat);
		$('#message-box p').empty().html(content);
		$('#message-box').fadeIn();

		if ( stat == 'success' || stat == 'basic' ) {
			setTimeout(function(){ 
				$('#message-box').addClass('hide');
			}, 3000);
		}
	}

	$(function(){
		$('.hide').hide();
	});

	// 닫기 버튼
	$('button.close').click(function(){
		$(this).closest('section').fadeOut();
	});

	$("#search-box input[name='cat_search']").keyup(function(e){
		if ( e.which==13 ) {
			// ascii 엔터코드는 13
			var keyword = $(this).val();
			show_search_result(keyword);
		}
	});

	function show_search_result(inputVar){
		var data = {
			'action' : 'dw_search_category',
			'keyword' : inputVar
		}

		$.post(ajaxurl, data, function(result){
			var res = $.parseJSON(result);
			var opt = "<h3>\""+inputVar+"\"로 검색한 결과입니다.</h3>"
			opt += '<ul>';
			if ( res.length > 0 ) {
				for( var i=0; i < res.length; ++i ){
					opt += '<li><a href="#!" data-cno="'+res[i].c_no+'" data-ref="'+res[i].ref+'" data-refn="'+res[i].ref_n+'" data-lev="'+res[i].lev+'">'+
							res[i].c_title +
							'</a></li>';
				}
			}
			else {
				opt += '<li>검색 결과가 없습니다.</li>';
			}
			opt += '</ul>';
			opt += '<p>'+res.length+'개의 결과가 검색되었습니다.</p>';
			opt += '<p><button class="btn reset">다시 검색하기</button></p>';
			$('#search-result').empty().append(opt);
		});
	}

	//리셋 버튼
	$('.reset').live('click', function(){
		$('#search-result').empty();
		$('.search-input input[name="cat_search"]').val('').focus();
	});


	$('#search-result a').live('click', function(){
		var d = {
			'c_no' : $(this).data('cno'),
			'ref' : $(this).data('ref'),
			'ref_n' : $(this).data('refn'),
			'lev' : $(this).data('lev')
		};

		var target_root = $('#select-cat-lev-1');
		var target_lev1 = $('#select-cat-lev-2');
		var target_lev2 = $('#select-cat-lev-3');

		if (d.c_no === d.ref && d.lev === 0){
			// root level category
			target_root.val(d.c_no).trigger('change');
		}
		else if ( d.lev > 0 ){
			target_root.val(d.ref).trigger('change');
			if ( d.lev === 1 ){
				// second level
				setTimeout(
					function(){
						target_lev1.val(d.c_no).trigger('change');
					}, 100);
			}
			else if ( d.lev > 1 ) {
				// third level
				setTimeout(
					function(){
						target_lev1.val(d.ref_n).trigger('change');
						target_lev2.val(d.c_no);
					}, 100);
			}

		}
	});

	// 카테고리 로딩
	function get_cat(lev, c_no, obj){
		var d = {
			'action' : 'dw_get_cat',
			'lev' : lev+1,
			'c_no' : c_no
		}

		$.post(ajaxurl, d, function(r){
			var d = $.parseJSON(r);
			if ( d.status === 'success' ) {
				var opt = new Array();
				opt.push('<option value="">카테고리 선택</option>');
				for ( var i = 0; i < d.data.length; ++i ) {
					var dd = d.data[i];
					var tt = dd.c_title.split('>')[lev+1];
					opt.push('<option value="'+dd.c_no+'" data-ref="'+dd.ref+'" data-ref-n="'+dd.ref_n+'" data-lev="'+dd.lev+'">'+tt+'</option>');
				}
				opt.push('<option value="add">카테고리 추가</option>');
				var tg = $('#cat-select-box').find('select').eq(lev+1);
				tg.empty().append(opt.join('')).focus();
			}
		});
	}

	// 카테고리 셀렉터 버튼 설정
	function showAddBtn(lev){
		var src = $('#select-cat-lev-'+lev).val();
		var prevsrc = $('#select-cat-lev-'+(lev-1)).val();

		if (prevsrc === 'add' || prevsrc === ''){
			showMsg('error', '이전 단계 카테고리를 먼저 설정하세요.');
		}
		else {
			if (src === 'add') {
				$('.cat'+lev+'-new-item').show();
			}
		}
	}

	// 카테고리 추가 버튼 동작
	$('button.add-new').click(function(){
		var lev = $(this).data('lev');
		var lev1Val = 0;
		var lev2Val = 0;
		var lev3Val = 0;

		if ( $('select[name="cat_lev1"]').val() ) lev1Val = $('select[name="cat_lev1"]').val();
		if ( $('select[name="cat_lev2"]').val() && $('select[name="cat_lev2"]').val() != 'add' ) lev2Val = $('select[name="cat_lev2"]').val();
		if ( $('select[name="cat_lev3"]').val() || $('select[name="cat_lev3"]').val() != 'add' ) lev3Val = $('select[name="cat_lev3"]').val();

		var c_title = $(this).prev('input[name="new-cat"]').val();

		if ( lev === 0 && c_title )	{
			// 1단계 카테고리 추가
			add_new_cat(0, c_title);
		}
		else if ( lev === 1 && c_title ) {
			// 2단계 카테고리 추가
			add_new_cat(1, c_title, lev1Val, lev1Val);
		}
		else if ( lev > 1 && c_title ) {
			// 3단계 카테고리 추가
			add_new_cat(lev, c_title, lev1Val, lev2Val );
		}
		else {
			// 예외 처리
			showMsg('error', '카테고리를 바르게 추가하세요.');
		}
	});

	// 카테고리 삭제 버튼 클릭 시 확인 메시지 출력
	$('button.del').click(function(){
		var tgId = $(this).data('cat-tg');
		var tgVal = $('#'+tgId).val(); // 삭제할 카테고리의 c_no

		var msg = "해당 분류를 삭제하면 분류에 속한 아이템은 등록 대기상태가 됩니다. 정말로 삭제하시겠습니까?" +
					" <div class='btn-set'><button class='btn warning-bg' id='del-confirm' data-cno='"+tgVal+"'>예, 삭제합니다.</button>" +
					" <button class='btn calcel' id='del-cancel'>취소</button></div>";
		showMsg('error',msg);
	});

	// 삭제 확인 버튼
	$('#del-confirm').live('click', function(){
		var c_no = $(this).data('cno');
		var d = {
			'action':'dw_del_category',
			'c_no' : c_no
		}

		$.post(ajaxurl, d, function(r){
			var res = $.parseJSON(r);

			if ( res.status === 'success' ) {
				// 박스에 메시지 출력 : 몇 개의 아이템이 등록대기 상태로 되었는지 표시
				if ( res.data === null ) {
					res.data = 0;
				}
				showMsg('success-bg', res.data+'개의 아이템의 등록대기 상태로 전환되었습니다.');
				// setTimeout(function(){location.reload();}, 3000);
			}
			else if ( res.status === 'fail' ){
				showMsg('error', res.data );
			}
		});

	});

	// 새 카테고리 추가
	function add_new_cat(lev, c_title, ref, ref_n){

		var d = {
				'action':'dw_add_category', 
				'c_title': c_title, 
				'ref' : ref,
				'ref_n': ref_n,
				'lev' : lev
			 }

		$.post(ajaxurl,d, function(r){

			var res = $.parseJSON(r);
			if (res.status === 'success'){

				var data = res.data[0];

				var opt = new Array();
				opt.push('<option value="'+parseInt(data.c_no)+'" ');
				opt.push('data-ref="'+parseInt(data.ref)+'" ');
				opt.push('data-ref-n="'+parseInt(data.ref_n)+'" ');
				if ( data.c_title.split('>').length > 0 ) {
					opt.push('selected>'+data.c_title.split('>')[data.lev]+'</option>');
				}
				else {
					opt.push('selected>'+data.c_title+'</option>');
				}

				var target = $('select[name="cat_lev'+(parseInt(data.lev)+1)+'"]');
				target.append(opt.join(''));
				$('.cat'+(data.lev+1)+'-new-item').hide();

			}
			else if(res.status === 'fail'){
				showMsg('error', '카테고리 추가 오류 발생 : '+res.data);
			}
		});
	}

	// 카테고리 수정 버튼
	$('#cat-select-box .edit').click(function(){
		var tgId = $(this).data('cat-tg');
		var tg_label = $('#'+tgId+' option:selected').text();
		var tg_cno = $('#'+tgId).val();
		var cat_id = tgId.split('-')[3];
		var editform = $('.cat'+cat_id+'-new-item input');
		
		$('.cat'+cat_id+'-new-item').show();
		$('.cat'+cat_id+'-new-item .edit-save').show();
		$('.cat'+cat_id+'-new-item .add-new').hide();

		editform.attr({
			'placeholder':'카테고리명 수정',
			'value':tg_label,
			'data-cno':$('#'+tgId).val()
		}).show();
		$('.cat'+cat_id+'-new-item .edit-save').attr({
			'data-cno':$('#'+tgId).val(),
			'data-src':'.cat'+cat_id+'-new-item',
			'data-sel-id' : tgId
		});
	});

	$('.edit-save').click(function(){

		var c_title = $($(this).data('src')+' input').val();
		var c_no = $(this).data('cno');
		var d = {
			'action':'dw_update_cat_title',
			'c_no' : c_no,
			'c_title' : c_title
		}
		$.post(ajaxurl,d, function(r){

			var res = $.parseJSON(r);

			if (res.status === 'success' ){
				console.log(res.data);
				var lev = res.data.lev;
				lev *= 1;
				if ( lev === 0 ) {
					$('#select-cat-lev-1 option:selected').text(res.data.c_title);
					$('.cat1-new-item').hide();
				}
				else if (lev === 1){
					$('#select-cat-lev-2 option:selected').text(res.data.c_title);
					$('.cat2-new-item').hide();
				}
				else if (lev > 1 ){
					$('#select-cat-lev-3 option:selected').text(res.data.c_title);
					$('.cat3-new-item').hide();
				}
			}
			else if (res.status === 'fail'){
				showMsg('error', res.data);
			}
		});
	});
	

	// 카테고리 셀렉트바 로딩
	$('#cat-select-box select').change(function(){
		var lev = $(this).attr('name').split('_')[1];

		if ( lev === 'lev1' ) {
			if ($(this).val() === 'add') {
				showAddBtn(1);
			}
			get_cat(0, $(this).val(), $(this));
		}
		else if ( lev === 'lev2' ) {
			// 2단계 카테고리 로딩
			if ( $(this).val() === 'add' ) {
				showAddBtn(2);
			}
			get_cat(1, $(this).val(),$(this));
		}
		else if ( lev === 'lev3' ) {
			// 3단계 카테고리 로딩
			if ( $(this).val() === 'add' ) {
				showAddBtn(3);
			}
			get_cat(2, $(this).val(),$(this));
		}
	});	


})( jQuery );
