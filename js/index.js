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
					id: 'image_'+image.id,
					'class': 'showInGallery',
					href: 'images/'+image.filename,
					target: '_blank',
					title: image.title,
					'data-id': image.id,
					'data-galleries': image.galleries.join(', '),
					append: $('<img>', {
						src: 'images/mythumbs/'+image.filename
					})
				});
				var li = $('<li>', {
					append: [
						link,
						$('<a>', {
							'class': 'update',
							href: '#'
						})
					],
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
		if (galleryModal.isOpen) {
			e.preventDefault();

			if (e.keyCode === 37) // left
				gallery.setActive(gallery.idx-1);
			else if (e.keyCode === 39 || e.which === 32) // right or space
				gallery.setActive(gallery.idx+1);
			else if (e.which === 43) // plus
				imagesBlock.find('.showInGallery').eq(gallery.idx).addClass('selected');
			else if (e.which === 45) // minus
				imagesBlock.find('.showInGallery').eq(gallery.idx).removeClass('selected');
		}
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

	$('.authors-list').on('click', '.showInGallery', function(e){
		e.preventDefault();

		var link = $(this),
			idx = link.closest('li').index();

		imagesBlock = link.closest('.b-images');

		if (e.ctrlKey) {
			link.toggleClass('selected');
			imagesBlock.data('last-selected', idx);

			return;
		} else if (e.shiftKey) {
			var i,
				lastSelected = parseInt(imagesBlock.data('last-selected')),
				galleryLinks = imagesBlock.find('.showInGallery'),
				checked = galleryLinks.eq(lastSelected).hasClass('selected');

			if (idx > lastSelected) {
				for (i = idx; i >= lastSelected; i--)
					galleryLinks.eq(i).toggleClass('selected', checked);
			} else {
				for (i = idx; i <= lastSelected; i++)
					galleryLinks.eq(i).toggleClass('selected', checked);
			}

			return;
		}

		galleryModal.open();

		updateGalleryPos();
		$(window).on('resize', updateGalleryPos);

		var images = imagesBlock.children().map(function(){
			var link = this.firstElementChild;
			return {
				thumb: link.firstElementChild.src,
				middle: link.href,
				big: link.href
			};
		});

		gallery.thumbsLinks.remove();
		gallery.thumbsSlider.listEls.remove();
		gallery.thumbsLinks = $();
		gallery.thumbsSlider.listEls = $();

		images.each(function(){
			gallery.appendImage(this);
		});

		gallery.setActive(idx);
	});

	var updateControl = {
		el: $('.updateControl'),
		init: function(){
			var that = this;

			this.form = this.el.find('form:first');
			this.galleries = this.el.find('input[name="galleries[]"]');

			this.el.find('.closeControl').click(function(e){
				e.preventDefault();
				that.el.hide();
			});
			
			this.form.submit(function(e){
				e.preventDefault();

				$.post(that.form.attr('action'), that.form.serialize(), function(data){
					var galleries = data.image.galleries.join(', ');
					$('#image_'+data.image.id).data('galleries', galleries).attr('data-galleries', galleries);
				}, 'json');

				that.el.hide();
			});

			this.el.find('.actionDeleteAll').click(function(e){
				e.preventDefault();

				var link = $(this);

				if (confirm('Delete all selected?')) {
					var params = {
						action: 'deleteFavorites',
						username: that.form[0].username.value,
						images: that.getSelected()
					};

					$.post(that.form.attr('action'), params, function(data){
						$.each(data.images, function(i, image){
							$('#image_'+image.id).closest('li').remove();
						});
					}, 'json');

					that.el.hide();
				}
			});

			this.el.find('.actionAddAll, .actionRemoveAll').click(function(e){
				e.preventDefault();

				var link = $(this);

				var params = {
					action: link.data('action'),
					username: that.form[0].username.value,
					gallery: link.data('gallery-id'),
					images: that.getSelected()
				};

				$.post(that.form.attr('action'), params, function(data){
					$.each(data.images, function(i, image){
						var galleries = image.galleries.join(', ');
						$('#image_'+image.id).data('galleries', galleries).attr('data-galleries', galleries);
					});
				}, 'json');
			});
		},
		show: function(rel){
			var linkPos = rel.offset();
			this.el.css({
				left: linkPos.left,
				top: linkPos.top+rel.height()
			}).show();
		},
		getSelected: function(){
			return this.imagesBlock.find('.selected').map(function(){
				return $(this).data('id');
			}).toArray();
		}
	};
	updateControl.init();

	$('.authors-list').on('click', '.update', function(e){
		e.preventDefault();

		updateControl.link = $(this);
		updateControl.imagesBlock = updateControl.link.closest('.b-images');

		var imageLink = updateControl.link.prev('.showInGallery');
		var imageGalleries = imageLink.data('galleries').split(', ');

		//imageLink.addClass('selected');

		updateControl.form[0].username.value = updateControl.imagesBlock.data('username');
		updateControl.form[0].image_id.value = imageLink.data('id');

		updateControl.galleries.each(function(){
			this.checked = (imageGalleries.indexOf(this.value) !== -1);
		});

		updateControl.show(imageLink);
	});
});