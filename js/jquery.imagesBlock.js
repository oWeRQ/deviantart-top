$.plugin('imagesBlock', {
	defaults: {
		elements: {
			imagesList: '.b-images-list',
			checkAllButton: '.i-checkAll',
			checkAllCheckbox: '.i-checkAll-checkbox',
			galleryButton: '.i-addGallery, .i-removeGallery',
			deleteButton: '.i-deleteFavourite'
		}
	},

	imagesJustify: null,
	
	init: function(){
		var imagesBlock = this;

		this.findAll(this.options.elements);
		//this.bindAll();

		this.imagesJustify = $.imagesJustify({}, this.imagesList);

		this.galleryButton.click(function(e){
			e.preventDefault();

			var action = $(this).data('action');

			$('#moveMenu').dropmenu('open', this, function(e){
				e.preventDefault();

				$.post('.', {
					action: action,
					gallery: $(this).data('id'),
					images: imagesBlock.getSelected()
				}, function(data){
					$.each(data.images, function(i, image){
						var galleries = image.galleries.join(', ');
						$('#image_'+image.id).data('galleries', galleries).attr('data-galleries', galleries);
					});
				}, 'json');
			});
		});

		this.deleteButton.click(function(e){
			e.preventDefault();

			if (!confirm('Delete all selected favourites?'))
				return;

			$.post('.', {
				action: 'deleteFavorites',
				images: imagesBlock.getSelected()
			}, function(data){
				$.each(data.images, function(i, image){
					$('#image_'+image.id).closest('li').remove();
				});
				imagesBlock.imagesJustify.updateElements();
				imagesBlock.imagesJustify.process();
			}, 'json');
		});

		this.checkAllCheckbox.prop('checked', false);

		this.checkAllCheckbox.click(function(e){
			//e.preventDefault();
			//e.stopPropagation();

			var items = imagesBlock.$el.find('.m-showInGallery');

			if (items.is('.selected'))
				this.checked = false;

			items.toggleClass('selected', this.checked);
		});	

		this.checkAllButton.click(function(e){
			//e.preventDefault();

			if (this === e.target) {
				$('#checkMenu').dropmenu('open', this, function(e){
					e.preventDefault();

					var check = $(this).data('check');
					var items = imagesBlock.$el.find('.m-showInGallery');
					var selected = items.filter('.selected');
					var notSelected = items.not('.selected');

					switch (check) {
						case 'all':
							items.addClass('selected');
							imagesBlock.checkAllCheckbox.prop('checked', true);
							break;
						case 'none':
							items.removeClass('selected');
							imagesBlock.checkAllCheckbox.prop('checked', false);
							break;
						case 'invert':
							selected.removeClass('selected');
							notSelected.addClass('selected');
							imagesBlock.checkAllCheckbox.prop('checked', false);
							break;
					}
				});
			}
		});

		this.$el.on('contextmenu', '.m-showInGallery', function(e){
			e.preventDefault();

			$('#updateControl').updateControl('show', this);
		});

		this.$el.on('click', '.m-update', function(e){
			e.preventDefault();

			$('#updateControl').updateControl('show', $(this).siblings('.m-showInGallery'));
		});

		this.$el.on('click', '.m-moreImages', function(e){
			e.preventDefault();

			var link = $(this),
				username = imagesBlock.$el.data('username'),
				imagesLoaded = imagesBlock.$el.data('images-loaded');
				imagesTotal = imagesBlock.$el.data('images-total');
				isLoading = imagesBlock.$el.data('is-loading');

			if (isLoading)
				return;

			link.text('Loading...');

			imagesBlock.$el.data('is-loading', true);

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

				imagesBlock.$el.data('images-loaded', imagesLoaded+count);

				$.each(author.images, function(i, image){
					var img = $('<img>', {
						src: 'images/mythumbs/'+image.filename
					});
					var link = $('<a>', {
						id: 'image_'+image.id,
						'class': 'm-showInGallery',
						href: 'images/original/'+image.filename,
						target: '_blank',
						title: image.title,
						'data-big': image.page,
						'data-id': image.id,
						'data-galleries': image.galleries.join(', '),
						append: img
					});
					var li = $('<li>', {
						append: [
							link,
							$('<a>', {
								'class': 'm-similar',
								href: 'similar.php?id='+image.id,
								target: '_blank'
							}),
							$('<a>', {
								'class': 'm-update',
								href: '#'
							})
						],
						appendTo: imagesBlock.imagesList
					});

					imagesBlock.imagesJustify.addImage(img);

					imagesBlock.$el.trigger('addImage', {
						thumb: link[0].firstElementChild.src,
						middle: link[0].href,
						big: link[0].dataset.big
					});
				});

				imagesBlock.imagesJustify.process();

				imagesBlock.$el.data('is-loading', null);
			}, 'json');
		});
	},

	getSelected: function(){
		return this.$el.find('.selected').map(function(){
			return $(this).data('id');
		}).toArray();
	}
});