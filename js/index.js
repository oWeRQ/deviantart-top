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

	$('.authors-list').on('click', '.moreImages', function(e){
		e.preventDefault();

		var link = $(this),
			imagesBlock = $(this).prev('.b-images'),
			username = imagesBlock.data('username'),
			imagesLoaded = imagesBlock.data('images-loaded');
			imagesTotal = imagesBlock.data('images-total');
			isLoading = imagesBlock.data('is-loading');

		if (isLoading)
			return;

		link.text('Loading...');

		imagesBlock.data('is-loading', true);

		$.post(window.location.href, {
			username: username,
			imagesLoaded: imagesLoaded
		}, function(data){
			var author = data.authors[0],
				count = author.images.length;

			if (imagesLoaded+count >= imagesTotal)
				link.remove();
			else
				link.text('More images ('+(author.total-imagesLoaded-count)+')');

			imagesBlock.data('images-loaded', imagesLoaded+count);

			$.each(author.images, function(i, image){
				var link = $('<a>', {
					href: 'images/'+image.filename,
					target: '_blank',
					title: image.galleries.join(', '),
					append: $('<img>', {
						src: 'images/mythumbs/'+image.filename
					}),
					appendTo: imagesBlock
				});

				if (galleryModal.isOpen) {
					gallery.appendImage({
						thumb: link[0].firstElementChild.src,
						middle: link[0].href,
						big: link[0].href
					});
				}
			});

			gallery.thumbsSlider.setActive(gallery.thumbsSlider.idx);

			imagesBlock.data('is-loading', null);
		}, 'json');
	});

	var scrollShowMoreEnabled = false;
	var scrollShowMore = function(e){
		if (window.scrollY >= window.scrollMaxY-300 && showMore.is(':visible')) {
			$(window).off('scroll', scrollShowMore);
			showMore.click();
		}
	};

	var showPrev = $('.showPrev').click(function(e){
		e.preventDefault();

		var link = $(this),
			url = this.href,
			authorsList = $('.authors-list');

		if (link.hasClass('disabled'))
			return;

		link.addClass('disabled');

		$.getJSON(url, function(data){
			history.replaceState({}, '', url);
			if (data.page === 1)
				link.hide();
			else
				link.prop('href', data.prevUrl).removeClass('disabled');

			var winScrollX = window.scrollX,
				winScrollY = window.scrollY;

			var authorsFragment = $(document.createDocumentFragment());
			$.each(data.authorsHtml, function(i, authorHtml){
				authorsFragment.append(authorHtml);
			});
			authorsList.prepend(authorsFragment);

			window.scrollTo(winScrollX, winScrollY);
		});
	});

	var showMore = $('.showMore').click(function(e){
		e.preventDefault();

		var link = $(this),
			url = this.href,
			authorsList = $('.authors-list');

		if (link.hasClass('disabled'))
			return;

		link.addClass('disabled');

		$.getJSON(url, function(data){
			history.pushState({}, 'page '+data.page, url);
			link.prop('href', data.nextUrl).removeClass('disabled');

			var winScrollX = window.scrollX,
				winScrollY = window.scrollY;

			var authorsFragment = $(document.createDocumentFragment());
			$.each(data.authorsHtml, function(i, authorHtml){
				authorsFragment.append(authorHtml);
			});
			authorsList.append(authorsFragment);

			window.scrollTo(winScrollX, winScrollY);
			$(window).on('scroll', scrollShowMore);
		});

		/*if (!scrollShowMoreEnabled) {
			scrollShowMoreEnabled = true;
			$(window).on('scroll', scrollShowMore);
		}*/
	});

	var galleryModal = $.modalWindow({
		onClose: function(){
			$(window).off('resize', updateGalleryPos);
		}
	});

	var galleryEl = $(
		'<div class="gallery">'+
		'	<div class="gallery-image">'+
		'		<a href="#" class="image-prev"></a>'+
		'		<a href="#" class="image-next"></a>'+
		'		<a class="image-wrap" target="_blank">'+
		'			<img class="image-current" src="" alt="">'+
		'			<span class="image-loader">Loading...</span>'+
		'		</a>'+
		'	</div><!-- .gallery-image -->'+
		'	<div class="thumbs-block">'+
		'		<a href="#" class="thumbs-prev"></a>'+
		'		<a href="#" class="thumbs-next"></a>'+
		'		<div class="thumbs-wrap">'+
		'			<ul class="thumbs-list"></ul>'+
		'		</div><!-- .thumbs-wrap -->'+
		'	</div><!-- .thumbs-block -->'+
		'</div><!-- .gallery -->'
	).appendTo(galleryModal.content);

	var imagesBlock = null;

	var gallery = $.gallery({
		thumbsSlider: {
			paddingRight: 10,
			onNextDisabled: function(){
				imagesBlock.next('.moreImages').click();
			}
		}
	}, galleryEl);

	$(document).on('keypress', function(e){
		if (e.keyCode === 37)
			gallery.setActive(gallery.idx-1);
		else if (e.keyCode === 39 || e.charCode === 32)
			gallery.setActive(gallery.idx+1);
	});

	var updateGalleryPos = function(){
		var ratio = 1.5,
			imageHeight = window.innerHeight - 170,
			imageWidth = imageHeight * ratio,
			maxWidth = window.innerWidth - 60;

		if (imageWidth > maxWidth) {
			imageWidth = maxWidth;
			imageHeight = imageWidth / ratio;
		}

		gallery.$el.css({width: imageWidth});
		gallery.imageWrap.css({height: imageHeight});
		gallery.thumbsSlider.setActive(gallery.thumbsSlider.idx);

		galleryModal.windowCenter.updatePosition();
	};

	$('.authors-list').on('click', '.b-images a', function(e){
		e.preventDefault();

		galleryModal.open();

		updateGalleryPos();
		$(window).on('resize', updateGalleryPos);

		var link = $(this);

		imagesBlock = link.closest('.b-images');

		var images = imagesBlock.children().map(function(){
			return {
				thumb: this.firstElementChild.src,
				middle: this.href,
				big: this.href
			};
		});

		gallery.thumbsLinks.remove();
		gallery.thumbsSlider.listEls.remove();
		gallery.thumbsLinks = $();
		gallery.thumbsSlider.listEls = $();

		images.each(function(){
			gallery.appendImage(this);
		});

		gallery.setActive(link.index());
	});
});