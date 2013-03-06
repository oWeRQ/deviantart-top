$.plugin('gallery', {
	defaults: {
		elements: {
			image: '.gallery-image',
			imagePrevEl: '.image-prev',
			imageNextEl: '.image-next',
			imageWrap: '.image-wrap',
			imageLoader: '.image-loader',
			imageCurrent: '.image-current',
			thumbs: '.thumbs-block',
			thumbsLinks: '.thumbs-list a'
		},
		thumbsSlider: {},
		showDuration: 400,
		onActivate: $.noop
	},
	idx: 0,
	init: function(){
		this.findAll(this.options.elements);
		this.bindAll('thumbClick', 'imagePrev', 'imageNext', 'imageLoaded');

		this.thumbsLinks.click(this.thumbClick);
		this.imagePrevEl.click(this.imagePrev);
		this.imageNextEl.click(this.imageNext);
		this.imageCurrent.load(this.imageLoaded);

		this.thumbsSlider = $.thumbsSlider(this.options.thumbsSlider, this.thumbs);

		this.setActive(this.idx);
	},
	imagePrev: function(e){
		e.preventDefault();

		this.setActive(this.idx-1);
	},
	imageNext: function(e){
		e.preventDefault();

		this.setActive(this.idx+1);
	},
	thumbClick: function(e){
		e.preventDefault();

		this.setActive(this.thumbsLinks.index(e.currentTarget));
	},
	imageLoaded: function(){
		this.imageLoader.hide();
		this.imageCurrent.fadeIn(this.showDuration);
	},
	appendImage: function(image){
		var link = $('<a>', {
			href: image.middle,
			'data-big': image.big,
			append: $('<img>', {
				src: image.thumb
			})
		});

		var li = $('<li>', {
			append: link,
			appendTo: this.thumbsSlider.listEl
		});

		this.thumbsLinks.push(link[0]);
		this.thumbsSlider.listEls.push(li[0]);

		this.thumbsLinks.click(this.thumbClick);
		this.thumbsSlider.lastEl = li;
	},
	setActive: function(idx){
		if (idx < 0)
			idx = this.thumbsLinks.length-1;
		else if (idx >= this.thumbsLinks.length)
			idx = 0;

		var link = this.thumbsLinks.eq(idx);
		
		link.addClass('active');
		this.thumbsLinks.not(link).removeClass('active');

		this.thumbsSlider.setActive(idx);

		this.imageLoader.show();
		this.imageWrap.prop('href', link.data('big'));
		this.imageCurrent.hide().prop('src', link.prop('href'));

		this.idx = idx;

		this.options.onActivate.call(this);
	}
});