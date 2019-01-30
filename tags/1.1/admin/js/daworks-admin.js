(function( $ ) {
	'use strict';

		function showMsg(stat, content){
			$('#message-box').attr('class','').addClass(stat);
			$('#message-box p').empty().text(content);
			$('#message-box').fadeIn();

			if ( stat == 'success' || stat == 'basic' ) {
				setTimeout(function(){ 
					$('#message-box').addClass('hide');
				}, 3000);
			}
		}
		// 닫기 버튼
		$('button.close').click(function(){
			$(this).closest('section').fadeOut();
		});

		// 버튼 동작 처리
		// 삭제 버튼
		$('button.reject').click(function(){

			var num = $(this).data('item-id');
			var data = {
				'action' : 'dw_del_item',
				'num' : $(this).data('item-id')
			};

			$.post(ajaxurl, data, function(r){
				if (r === 'success') {
					showMsg('success', '삭제되었습니다.');
					$('tr.num-'+num ).fadeOut();
				}
				else {
					showMsg('error', '오류 발생 : 코드 '+r);
				}
			});
		});

		// 허용 버튼
		$('button.apply').click(function(){
			
			var num = $(this).data('item-id');
			var lev1 = $('td#num-'+num+' select[name="cat_lev1"]').val();
			var lev2 = $('td#num-'+num+' select[name="cat_lev2"]').val();
			var lev3 = $('td#num-'+num+' select[name="cat_lev3"]').val();

			if ( lev1 == '' ) {
				alert('1단계 카테고리를 선택하세요.');
			}
			else {

				if ( lev1 && !lev2 && !lev3 ) {
					// 1단계 카테고리만 있을 경우
					var c_no = lev1;
					var ref = lev1;
					var ref_n = 0;
					var lev = 0;
					var step = 0;
				}
				else if ( lev1 && lev2 && !lev3 ) {
					// 2단계 카테고리까지 있을 경우
					var c_no = lev2;
					var ref = lev1;
					var ref_n = lev1;
					var lev = 1;
					var step = 0.01;
				}
				else if ( lev1 && lev2 && lev3 ) {
					// 3단계 카테고리까지 있을 경우
					var c_no = lev3;
					var ref = lev1;
					var ref_n = lev2;
					var lev = 2;
					var step = 0.0101;
				}

				var data = {
					'action':'dw_grant_item',
					'num' : num,
					'c_no' : c_no,
					'ref' : ref,
					'ref_n' : ref_n,
					'lev' : lev,
					'step' : step
				};

				$.post(ajaxurl, data, function(r){

					if ( r > 0 )  {
						showMsg('success', '정상 등록되었습니다.');
						$( 'tr.num-'+num ).fadeOut();
					}
					else {
						showMsg('error', '오류 발생 : '+r);
					}
				} );
			}
		});

		// 보류 버튼 - 보류는 지정한 카테고리는 적용하되 화면에는 출력되지 않도록 함.
		$('button.standby').click(function(){
			
			var num = $(this).data('item-id');
			var lev1 = $('td#num-'+num+' select[name="cat_lev1"]').val();
			var lev2 = $('td#num-'+num+' select[name="cat_lev2"]').val();
			var lev3 = $('td#num-'+num+' select[name="cat_lev3"]').val();

			if ( lev1 == '' ) {
				alert('1단계 카테고리를 선택하세요.');
			}
			else {

				if ( lev1 && !lev2 && !lev3 ) {
					// 1단계 카테고리만 있을 경우
					var c_no = lev1;
					var ref = lev1;
					var ref_n = 0;
					var lev = 0;
					var step = 0;
				}
				else if ( lev1 && lev2 && !lev3 ) {
					// 2단계 카테고리까지 있을 경우
					var c_no = lev2;
					var ref = lev1;
					var ref_n = lev1;
					var lev = 1;
					var step = 0.01;
				}
				else if ( lev1 && lev2 && lev3 ) {
					// 3단계 카테고리까지 있을 경우
					var c_no = lev3;
					var ref = lev1;
					var ref_n = lev2;
					var lev = 2;
					var step = 0.0101;
				}

				var data = {
					'action':'dw_standby_item',
					'num' : num,
					'c_no' : c_no,
					'ref' : ref,
					'ref_n' : ref_n,
					'lev' : lev,
					'step' : step
				};

				$.post(ajaxurl, data, function(r){

					if ( r != 0 && r != false )  {
						showMsg('success', '정상 처리되었습니다.');
					}
					else if ( r == 0 ) {
						showMsg('basic', '변동사항이 없습니다.');
					}
					else {
						showMsg('error', '오류 발생 : 코드 ' + r);
					}
				} );
			}
		});

		/**
		* 카테고리 로딩
		**/
		// 1단계 카테고리 로딩
		$(document).ready(function(){
			$('select[name="cat_lev1"]').trigger('change');
		});

		// 2단계 카테고리 로딩
		$('select[name="cat_lev1"]').change(function(){
			var c_no = $(this).val();
			var num = $(this).data('num-id');
			var data = {
				'action' : 'dw_load_cat_lev1',
				'c_no' : c_no
			};

			$.post(ajaxurl, data, function(r){
				var res = $.parseJSON(r);
				var tag = '<option value="">2단계 카테고리</option>';
				for (var i=0; i < res.length; ++i ){

					if ( $('td#num-'+num+' select[name="cat_lev2"]').data("cno") == res[i].c_no ){
						var selected = "selected";
					}
					else {
						var selected = "";
					}
					tag += '<option value="'+res[i].c_no+'" '+selected+'>'+res[i].c_title.split('>')[1]+'</option>';
				}
				$('td#num-'+num+' select[name="cat_lev2"]').empty().append(tag);
			});
		});

		// 3단계 카테고리 로딩
		$('select[name="cat_lev2"]').change(function(){
			var c_no = $(this).val();
			var num = $(this).data('num-id');
			var data = {
				'action' : 'dw_load_cat_lev2',
				'c_no' : c_no
			};

			$.post(ajaxurl, data, function(r){
				var res = $.parseJSON(r);
				if (res.length > 0) {
					var tag = '<option>3단계 카테고리</option>';
					for (var i=0; i < res.length; ++i ){
						if ( $('td#num-'+num+' select[name="cat_lev3"]').data("cno") == res[i].c_no ){
							var selected = "selected";
						}
						else {
							var selected = "";
						}
						tag += '<option value="'+res[i].c_no+'" '+selected+'>'+res[i].c_title.split('>')[2]+'</option>';
					}
				}
				else {
					tag = '<option value="">카테고리 없음</option>';
				}
				$('td#num-'+num+' select[name="cat_lev3"]').empty().append(tag);

			});
		});

})( jQuery );
