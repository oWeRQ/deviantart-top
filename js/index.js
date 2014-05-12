$(function(){
	$('.checkAll').click(function(e){
		e.preventDefault();

		$(this).closest('.checkboxes').find('input[type=checkbox]:not(.exclude)').prop('checked', true);
	});

	$('.uncheckAll').click(function(e){
		e.preventDefault();

		$(this).closest('.checkboxes').find('input[type=checkbox]:not(.exclude)').prop('checked', false);
	});

	$('.clearInput').click(function(e){
		e.preventDefault();

		$(this).prev('input').val('');
	});

	$('.b-pages').imagesPages({
		elements: {
			pages: '.b-pages-item',
			pageInput: $('.l-sidebar input[name=page]'),
			showPrevButton: $('.i-showPrev'),
			showMoreButton: $('.i-showMore')
		}
	});
});
