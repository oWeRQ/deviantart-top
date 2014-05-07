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

	var authorsList = $('.b-pages');

	authorsList.on('click', '.m-moreImages', function(e){
		e.preventDefault();

		var link = $(this),
			imagesBlock = $(this).closest('.b-images'),
			imagesList = imagesBlock.find('.b-images-list'),
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
			imagesOffset: imagesLoaded
		}, function(data){
			var author = data.authors[0],
				count = author.images.length;

			if (imagesLoaded+count >= imagesTotal)
				link.remove();
			else
				link.text('More images ('+(author.favourites-imagesLoaded-count)+')');

			imagesBlock.data('images-loaded', imagesLoaded+count);

			$.each(author.images, function(i, image){
				var link = $('<a>', {
					id: 'image_'+image.id,
					'class': 'm-showInGallery',
					href: 'images/original/'+image.filename,
					target: '_blank',
					title: image.title,
					'data-big': image.page,
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
							'class': 'm-similar',
							href: 'm-similar.php?id='+image.id,
							target: '_blank'
						}),
						$('<a>', {
							'class': 'm-update',
							href: '#'
						})
					],
					appendTo: imagesList
				});

				if (galleryModal.isOpen) {
					gallery.appendImage({
						thumb: link[0].firstElementChild.src,
						middle: link[0].href,
						big: link[0].dataset.big
					});
				}
			});

			gallery.thumbsSlider.setActive(gallery.thumbsSlider.idx);

			imagesBlock.data('is-loading', null);
		}, 'json');
	});

	var pages = $('.b-pages-item');
	var currentPage = null;
	var pageInput = $('.l-sidebar input[name=page]');

	var scrollPage = function(e){
		pages.each(function(){
			if (window.scrollY < this.offsetTop + this.clientHeight) {
				var page = $(this).data('num');
				if (currentPage !== page) {
					var url = window.location.href.replace(/page=\d+/, 'page='+page);
					history.replaceState({}, 'page '+page, url);
					pageInput.val(page);
					currentPage = page;
				}
				return false;
			}
		});
	};

	var scrollShowMoreEnabled = false;
	var scrollShowMore = function(e){
		if (window.scrollY >= window.scrollMaxY-300 && showMore.is(':visible')) {
			$(window).off('scroll', scrollShowMore);
			showMore.click();
		}
	};

	var showPrev = $('.i-showPrev').click(function(e){
		e.preventDefault();

		var link = $(this),
			url = this.href;

		if (link.hasClass('disabled'))
			return;

		link.addClass('disabled');

		$.getJSON(url, function(data){
			//history.replaceState({}, '', url);
			if (data.page === 1)
				link.hide();
			else
				link.prop('href', data.prevUrl).removeClass('disabled');

			var winScrollX = window.scrollX,
				winScrollY = window.scrollY;

			var page = $('<div>', {
				id: 'page_'+data.page,
				'class': 'b-pages-item',
				data: {
					num: data.page
				}
			});
			$.each(data.authorsHtml, function(i, authorHtml){
				page.append(authorHtml);
			});
			authorsList.prepend(page);
			Array.prototype.unshift.call(pages, page[0]);

			window.scrollTo(winScrollX, winScrollY);
			$(window).on('scroll', scrollPage).scroll();
		});
	});

	var showMore = $('.i-showMore').click(function(e){
		e.preventDefault();

		var link = $(this),
			url = this.href;

		if (link.hasClass('disabled'))
			return;

		link.addClass('disabled');

		$.getJSON(url, function(data){
			//history.replaceState({}, 'page '+data.page, url);
			link.prop('href', data.nextUrl).removeClass('disabled');

			var winScrollX = window.scrollX,
				winScrollY = window.scrollY;

			var page = $('<div>', {
				id: 'page_'+data.page,
				'class': 'b-pages-item',
				data: {
					num: data.page
				}
			});
			$.each(data.authorsHtml, function(i, authorHtml){
				page.append(authorHtml);
			});
			authorsList.append(page);
			pages.push(page[0]);

			window.scrollTo(winScrollX, winScrollY);
			$(window).on('scroll', scrollShowMore);
			$(window).on('scroll', scrollPage).scroll();
		});
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
	var setImagesBlock = function(el) {
		if (imagesBlock)
			imagesBlock.removeClass('m-active');

		imagesBlock = $(el).addClass('m-active');

		//window.scrollTo(window.scrollX, imagesBlock.prop('offsetTop'));
	};
	authorsList.on('click', '.b-images', function(){
		setImagesBlock(this);
	});

	var gallery = $.gallery({
		onActivate: function(){
			if (imagesBlock)
				imagesBlock.find('.m-showInGallery').removeClass('m-cursor').eq(this.idx).addClass('m-cursor');
		},
		thumbsSlider: {
			paddingRight: 10,
			onNextDisabled: function(){
				imagesBlock.find('.m-moreImages').click();
			}
		}
	}, galleryEl);

	var sidebarInputs = $('.l-sidebar input');

	$(document).on('keypress', function(e){
		if (galleryModal.isOpen) {
			e.preventDefault();

			if (e.isKey('esc', 'q'))
				galleryModal.close();
			else if (e.isKey('left', 'backspace', 'a'))
				gallery.setActive(gallery.idx-1);
			else if (e.isKey('right', 'space', 'd'))
				gallery.setActive(gallery.idx+1);
			else if (e.isKey('+', 'w')) {
				imagesBlock.find('.m-showInGallery').eq(gallery.idx).addClass('selected');
				gallery.setActive(gallery.idx+1);
			}
			else if (e.isKey('-', 's')) {
				imagesBlock.find('.m-showInGallery').eq(gallery.idx).removeClass('selected');
				gallery.setActive(gallery.idx+1);
			}
			else if (e.isKey('e')) {
				galleryModal.close();
				imagesBlock.find('.i-addGallery').click();
			}
			else if (e.isKey('r')) {
				galleryModal.close();
				imagesBlock.find('.i-removeGallery').click();
			}
		} else if (sidebarInputs.filter(':focus').length === 0) {
			if (!imagesBlock || imagesBlock.length === 0)
				imagesBlock = authorsList.find('.b-images:first');

			var imagesLinks = imagesBlock.find('.m-showInGallery');
			var cursor = imagesLinks.filter('.m-cursor:first');
			var cursorIdx = imagesLinks.index(cursor);

			if (cursorIdx === -1)
				cursorIdx = 0;

			cursor.removeClass('m-cursor');

			if (e.isKey('left', 'a')) {
				e.preventDefault();
				cursorIdx--;
			} else if (e.isKey('right', 'd')) {
				e.preventDefault();
				cursorIdx++;
			} else if (e.isKey('up', 'w')) {
				e.preventDefault();
				setImagesBlock(imagesBlock.prev());
				imagesLinks = imagesBlock.find('.m-showInGallery');
			} else if (e.isKey('down', 's')) {
				e.preventDefault();
				setImagesBlock(imagesBlock.next());
				imagesLinks = imagesBlock.find('.m-showInGallery');
			} else if (e.isKey('space', 'enter')) {
				e.preventDefault();
				cursor.click();
			}

			imagesLinks.eq(cursorIdx).addClass('m-cursor');
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

	authorsList.on('click', '.m-showInGallery', function(e){
		e.preventDefault();

		var link = $(this),
			idx = link.closest('li').index();

		setImagesBlock(link.closest('.b-images'));

		if (e.ctrlKey || e.metaKey) {
			link.toggleClass('selected');
			imagesBlock.data('last-selected', idx);

			return;
		} else if (e.shiftKey) {
			var i,
				lastSelected = parseInt(imagesBlock.data('last-selected'), 10),
				galleryLinks = imagesBlock.find('.m-showInGallery'),
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

		var images = imagesBlock.find('.b-images-list > li').map(function(){
			var link = this.firstElementChild;
			return {
				thumb: link.firstElementChild.src,
				middle: link.href,
				big: link.dataset.big
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
		el: $('.b-updateControl'),
		activeLink: null,
		imagesBlock: null,
		init: function(){
			var that = this;

			this.form = this.el.find('form:first');
			this.galleries = this.el.find('input[name="galleries[]"]');

			this.el.find('.closeControl').click(function(e){
				e.preventDefault();
				that.hide();
			});

			this.form.submit(function(e){
				e.preventDefault();

				$.post(that.form.attr('action'), that.form.serialize(), function(data){
					var galleries = data.image.galleries.join(', ');
					$('#image_'+data.image.id).data('galleries', galleries).attr('data-galleries', galleries);
				}, 'json');

				that.hide();
			});
		},
		show: function(link){
			var $link = $(link);

			if (this.activeLink && this.activeLink[0] === $link[0] && this.el.hasClass('show')) {
				this.el.removeClass('show');
				this.activeLink = null;
				this.imagesBlock = null;
				return;
			}

			this.activeLink = $link;
			this.imagesBlock = this.activeLink.closest('.b-images');

			var imageGalleries = this.activeLink.data('galleries').split(', ');

			this.form[0].username.value = this.imagesBlock.data('username');
			this.form[0].image_id.value = this.activeLink.data('id');

			this.galleries.each(function(){
				this.checked = (imageGalleries.indexOf(this.value) !== -1);
			});

			var linkPos = this.activeLink.offset();
			this.el.css({
				opacity: 1,
				left: linkPos.left,
				top: linkPos.top+this.activeLink.height()
			}).addClass('show');
		},
		hide: function(){
			var that = this;
			this.el.animate({
				opacity: 0,
				top: '-=12px'
			}, 500, function(){
				that.el.removeClass('show');
			});
		},
		getSelected: function(){
			return this.imagesBlock.find('.selected').map(function(){
				return $(this).data('id');
			}).toArray();
		}
	};
	updateControl.init();

	authorsList.on('contextmenu', '.m-showInGallery', function(e){
		e.preventDefault();

		updateControl.show(this);
	});

	authorsList.on('click', '.m-update', function(e){
		e.preventDefault();

		updateControl.show($(this).siblings('.m-showInGallery'));
	});

	var checkMenu = {
		el: $('#checkMenu'),
		links: null,
		activeLink: null,
		init: function(){
			var that = this;

			//this.keypress = $.proxy(this.keypress, this);

			this.links = this.el.find('a').click(function(e){
				e.preventDefault();

				var check = $(this).data('check');
				var items = that.imagesBlock.find('.m-showInGallery');
				var selected = items.filter('.selected');
				var notSelected = items.not('.selected');

				switch (check) {
					case 'all':
						items.addClass('selected');
						that.checkbox.prop('checked', true);
						break;
					case 'none':
						items.removeClass('selected');
						that.checkbox.prop('checked', false);
						break;
					case 'invert':
						selected.removeClass('selected');
						notSelected.addClass('selected');
						that.checkbox.prop('checked', false);
						break;
				}

				that.close();
			});
		},
		open: function(link){
			var $link = $(link);

			if (this.activeLink && this.activeLink[0] === $link[0] && this.el.is(':visible')) {
				this.close();
				return;
			}

			//$(document).on('keypress', this.keypress);

			this.activeLink = $link;
			this.checkbox = $link.find('input[type=checkbox]');
			this.imagesBlock = this.activeLink.closest('.b-images');
			this.username = this.imagesBlock.data('username');

			var linkPos = this.activeLink.offset();
			this.el.css({
				left: linkPos.left,
				top: linkPos.top+this.activeLink.height()
			}).show();//.outerWidth(this.activeLink.outerWidth()).show();
		},
		close: function(){
			//$(document).off('keypress', this.keypress);

			this.el.hide();
			this.activeLink = null;
			this.imagesBlock = null;
			//this.username = null;
			//this.selected = -1;
		},
		getSelected: function(){
			return this.imagesBlock.find('.selected').map(function(){
				return $(this).data('id');
			}).toArray();
		}
	};
	checkMenu.init();

	$('.i-checkAll input').prop('checked', false);

	authorsList.on('click', '.i-checkAll input', function(e){
		//e.preventDefault();
		//e.stopPropagation();

		var checkbox = $(this);
		var items = checkbox.closest('.b-images').find('.m-showInGallery');

		if (items.is('.selected'))
			this.checked = false;

		items.toggleClass('selected', this.checked);
	});	

	authorsList.on('click', '.i-checkAll', function(e){
		//e.preventDefault();

		if (this === e.target)
			checkMenu.open(this);
	});

	var moveMenu = {
		el: $('#moveMenu'),
		links: null,
		action: null,
		activeLink: null,
		imagesBlock: null,
		username: null,
		selected: -1,
		init: function(){
			var that = this;

			this.keypress = $.proxy(this.keypress, this);

			this.links = this.el.find('a').click(function(e){
				e.preventDefault();

				var params = {
					action: that.action,
					username: that.username,
					gallery: $(this).data('id'),
					images: that.getSelected()
				};

				$.post('updateImage.php', params, function(data){
					$.each(data.images, function(i, image){
						var galleries = image.galleries.join(', ');
						$('#image_'+image.id).data('galleries', galleries).attr('data-galleries', galleries);
					});
				}, 'json');

				that.close();
			});
		},
		keypress: function(e) {
			if (e.isKey('esc', 'q')) {
				this.close();
			}
			else if (e.isKey('up')) {
				e.preventDefault();

				if (--this.selected < 0)
					this.selected = 0;

				this.links.eq(this.selected).focus();
			}
			else if (e.isKey('down')) {
				e.preventDefault();

				if (++this.selected > this.links.length-1)
					this.selected = this.links.length-1;

				this.links.eq(this.selected).focus();
			}
		},
		open: function(link){
			var $link = $(link);

			if (this.activeLink && this.activeLink[0] === $link[0] && this.el.is(':visible')) {
				this.close();
				return;
			}

			$(document).on('keypress', this.keypress);

			this.activeLink = $link;
			this.imagesBlock = this.activeLink.closest('.b-images');
			this.username = this.imagesBlock.data('username');

			var linkPos = this.activeLink.offset();
			this.el.css({
				opacity: 1,
				left: linkPos.left,
				top: linkPos.top+this.activeLink.height()
			}).outerWidth(this.activeLink.outerWidth()).show();
		},
		close: function(){
			$(document).off('keypress', this.keypress);

			//this.el.hide();
			this.activeLink = null;
			this.imagesBlock = null;
			this.username = null;
			this.selected = -1;

			var that = this;
			this.el.animate({
				opacity: 0,
				top: '-=8px'
			}, 500, function(){
				that.el.hide();
			});
		},
		getSelected: function(){
			return this.imagesBlock.find('.selected').map(function(){
				return $(this).data('id');
			}).toArray();
		}
	};
	moveMenu.init();

	authorsList.on('click', '.i-addGallery', function(e){
		e.preventDefault();

		moveMenu.action = 'addGallery';
		moveMenu.open(this);
	});

	authorsList.on('click', '.i-removeGallery', function(e){
		e.preventDefault();

		moveMenu.action = 'removeGallery';
		moveMenu.open(this);
	});

	authorsList.on('click', '.i-deleteFavourite', function(e){
		e.preventDefault();

		if (!confirm('Delete all selected favourites?'))
			return;

		var link = $(this);
		setImagesBlock(link.closest('.b-images'));
		var username = imagesBlock.data('username');
		var selectedImages = imagesBlock.find('.selected').map(function(){
			return $(this).data('id');
		}).toArray();

		var params = {
			action: 'deleteFavorites',
			username: username,
			images: selectedImages
		};

		$.post('updateImage.php', params, function(data){
			$.each(data.images, function(i, image){
				$('#image_'+image.id).closest('li').remove();
			});
		}, 'json');
	});
});