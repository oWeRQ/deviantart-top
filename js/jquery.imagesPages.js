$.plugin('imagesPages', {
	defaults: {
		elements: {
			pagesList: null,
			pages: null,
			pageInput: null,
			showPrevButton: null,
			showMoreButton: null
		}
	},

	currentPage: null,
	imagesBlock: null,
	modalGallery: null,

	init: function(){
		var that = this;

		this.findAll(this.options.elements);
		this.bindAll(
			'scrollPageHandler',
			'scrollShowMoreHandler',
			'showInGalleryHandler',
			'showPrevHandler',
			'showMoreHandler',
			'addImage',
			'keypress'
		);

		this.showPrevButton.click(this.showPrevHandler);
		this.showMoreButton.click(this.showMoreHandler);

		this.$el.on('click', '.b-images', function(){
			that.setImagesBlock(this);
		});

		this.$el.on('click', '.m-showInGallery', this.showInGalleryHandler);

		$(document).on('keypress', this.keypress);

		this.$el.find('.b-images').each(function(){
			$(this).imagesBlock();
		});

		this.modalGallery = $.modalGallery({
			gallery: {
				onActivate: function(){
					if (that.imagesBlock)
						that.imagesBlock.find('.m-showInGallery').removeClass('m-cursor').eq(this.idx).addClass('m-cursor');
				},
				thumbsSlider: {
					paddingRight: 10,
					onNextDisabled: function(){
						that.imagesBlock.find('.m-moreImages').click();
					}
				}
			}
		});
	},

	setImagesBlock: function(el) {
		if (this.imagesBlock) {
			this.imagesBlock.removeClass('m-active');
			this.imagesBlock.off('addImage', this.addImage);
		}

		this.imagesBlock = $(el).addClass('m-active');
		this.imagesBlock.on('addImage', this.addImage)

		//window.scrollTo(window.scrollX, this.imagesBlock.prop('offsetTop'));
	},

	scrollPageHandler: function(e) {
		var that = this;

		that.pages.each(function(){
			if (window.scrollY < this.offsetTop + this.clientHeight) {
				var page = $(this).data('num');
				if (that.currentPage !== page) {
					var url = window.location.href.replace(/page=\d+/, 'page='+page);
					history.replaceState({}, 'page '+page, url);
					that.pageInput.val(page);
					that.currentPage = page;
				}
				return false;
			}
		});
	},

	scrollShowMoreHandler: function(e) {
		if (window.scrollY >= window.scrollMaxY-300 && this.showMoreButton.is(':visible')) {
			$(window).off('scroll', this.scrollShowMoreHandler);
			this.showMoreButton.click();
		}
	},

	showInGalleryHandler: function(e) {
		e.preventDefault();

		var link = $(e.target).closest('a'),
			idx = link.closest('li').index(),
			modalGallery = this.modalGallery;

		this.setImagesBlock(link.closest('.b-images'));

		if (e.ctrlKey || e.metaKey) {
			link.toggleClass('selected');
			this.imagesBlock.data('last-selected', idx);
		} else if (e.shiftKey) {
			var i,
				lastSelected = parseInt(this.imagesBlock.data('last-selected'), 10),
				galleryLinks = this.imagesBlock.find('.m-showInGallery'),
				checked = galleryLinks.eq(lastSelected).hasClass('selected');

			if (idx > lastSelected) {
				for (i = idx; i >= lastSelected; i--)
					galleryLinks.eq(i).toggleClass('selected', checked);
			} else {
				for (i = idx; i <= lastSelected; i++)
					galleryLinks.eq(i).toggleClass('selected', checked);
			}
		} else {
			modalGallery.modal.open();

			modalGallery.updateGalleryPos();
			$(window).on('resize', modalGallery.updateGalleryPos);

			var images = this.imagesBlock.find('.b-images-list > li').map(function(){
				var link = this.firstElementChild;
				return {
					thumb: link.firstElementChild.src,
					middle: link.href,
					big: link.dataset.big
				};
			});

			modalGallery.gallery.thumbsLinks.remove();
			modalGallery.gallery.thumbsSlider.listEls.remove();
			modalGallery.gallery.thumbsLinks = $();
			modalGallery.gallery.thumbsSlider.listEls = $();

			images.each(function(){
				modalGallery.gallery.appendImage(this);
			});

			modalGallery.gallery.setActive(idx);
		}
	},

	showPrevHandler: function(e) {
		e.preventDefault();

		var that = this;
		var link = $(e.target);

		if (link.hasClass('disabled'))
			return;

		link.addClass('disabled');

		$.getJSON(link.prop('href'), function(data){
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
			that.pagesList.prepend(page);
			page.find('.b-images').each(function(){
				$(this).imagesBlock();
			});
			Array.prototype.unshift.call(that.pages, page[0]);

			window.scrollTo(winScrollX, winScrollY);
			$(window).on('scroll', that.scrollPageHandler).scroll();
		});
	},

	showMoreHandler: function(e) {
		e.preventDefault();

		var that = this;
		var link = $(e.target);

		if (link.hasClass('disabled'))
			return;

		link.addClass('disabled');

		$.getJSON(link.prop('href'), function(data){
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
			that.pagesList.append(page);
			page.find('.b-images').each(function(){
				$(this).imagesBlock();
			});
			that.pages.push(page[0]);

			window.scrollTo(winScrollX, winScrollY);
			$(window).on('scroll', that.scrollShowMoreHandler);
			$(window).on('scroll', that.scrollPageHandler).scroll();
		});
	},

	addImage: function(e, image) {
		if (this.modalGallery.modal.isOpen) {
			this.modalGallery.gallery.appendImage(image);
			this.modalGallery.gallery.thumbsSlider.setActive(this.modalGallery.gallery.thumbsSlider.idx);
		}
	},

	keypress: function(e) {
		if (this.modalGallery.modal.isOpen) {
			e.preventDefault();

			if (e.isKey('esc', 'q'))
				this.modalGallery.modal.close();
			else if (e.isKey('left', 'backspace', 'a'))
				this.modalGallery.gallery.setActive(this.modalGallery.gallery.idx-1);
			else if (e.isKey('right', 'space', 'd'))
				this.modalGallery.gallery.setActive(this.modalGallery.gallery.idx+1);
			else if (e.isKey('+', 'w')) {
				this.imagesBlock.find('.m-showInGallery').eq(this.modalGallery.gallery.idx).addClass('selected');
				this.modalGallery.gallery.setActive(this.modalGallery.gallery.idx+1);
			}
			else if (e.isKey('-', 's')) {
				this.imagesBlock.find('.m-showInGallery').eq(this.modalGallery.gallery.idx).removeClass('selected');
				this.modalGallery.gallery.setActive(this.modalGallery.gallery.idx+1);
			}
			else if (e.isKey('e')) {
				this.modalGallery.modal.close();
				this.imagesBlock.find('.i-addGallery').click();
			}
			else if (e.isKey('r')) {
				this.modalGallery.modal.close();
				this.imagesBlock.find('.i-removeGallery').click();
			}
		} else if ($(e.target).closest('form').length === 0) {
			if (!this.imagesBlock || this.imagesBlock.length === 0)
				this.imagesBlock = this.$el.find('.b-images:first');

			var imagesLinks = this.imagesBlock.find('.m-showInGallery');
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
				this.setImagesBlock(this.imagesBlock.prev());
				imagesLinks = this.imagesBlock.find('.m-showInGallery');
			} else if (e.isKey('down', 's')) {
				e.preventDefault();
				this.setImagesBlock(this.imagesBlock.next());
				imagesLinks = this.imagesBlock.find('.m-showInGallery');
			} else if (e.isKey('space', 'enter')) {
				e.preventDefault();
				cursor.click();
			}

			imagesLinks.eq(cursorIdx).addClass('m-cursor');
		}
	}
});