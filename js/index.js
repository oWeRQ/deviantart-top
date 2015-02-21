$(function(){
	$('.checkAll').click(function(e){
		e.preventDefault();

		$(this).closest('.checkboxes').find('input[type=checkbox]:not(.exclude)').prop('checked', true);
	});

	$('.uncheckAll').click(function(e){
		e.preventDefault();

		$(this).closest('.checkboxes').find('input[type=checkbox]:not(.exclude)').prop('checked', false);
	});

	$('.checkboxes label > input').change(function(e){
		$(this).parent('label').prev('input.exclude').prop('checked', false);
	});

	$('.checkboxes input.exclude').change(function(e){
		$(this).next('label').find('input').prop('checked', false);
	});

	$('.clearInput').click(function(e){
		e.preventDefault();

		$(this).prev('input').val('');
	});

	$('.b-form-legend').click(function(){
		$(this).toggleClass('m-open');
	});
});

$(function(){
	var pages = $('.b-pages');

	pages.imagesPages({
		elements: {
			pages: '.b-pages-item',
			pageInput: $('.l-sidebar input[name=page]'),
			showPrevButton: $('.i-showPrev'),
			showMoreButton: $('.i-showMore')
		}
	});

	/*$('#filterForm').submit(function(e){
		e.preventDefault();

		$.getJSON('', $(this).serialize(), function(response){
			var page = $('<div>', {
				'id': 'page_' + response.page,
				'class': 'b-pages-item',
				'data-num': response.page
			});

			page.html(response.authorsHtml.join(''));

			pages.empty().append(page).imagesPages('init');
		});
	});*/
});
