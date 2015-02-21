$.plugin('imagesJustify', {
	defaults: {
		elements: {
			images: 'img'
		},
		spacing: 3
	},

	resizeTimeout: null,

	init: function(){
		var that = this;

		this.bindAll('process');

		this.updateElements();

		$(window).resize(function(){
			clearTimeout(that.resizeTimeout);
			that.resizeTimeout = setTimeout(that.process, 16);
		});

		this.process();
	},

	updateElements: function(){
		var that = this;

		this.findAll(this.options.elements);

		this.images.each(function(){
			if (!this.complete)
				$(this).on('load error', that.process)
		});
	},

	addImage: function(image){
		if (!image[0].complete)
			image.on('load error', this.process);
		this.images.push(image[0]);
	},

	process: function(){
		var spacing = this.options.spacing,
			//maxWidth = this.$el.innerWidth(),
			maxWidth = parseInt(this.$el.parent().css('width'))-1,
			rowWidth = 0,
			rowImages = $();

		this.images.each(function(i, image){
			rowWidth += image.width + spacing;

			rowImages.push(image);

			if (rowWidth > maxWidth) {
				rowImages.each(function(i){
					var margin = -((rowWidth - maxWidth) * (this.width + spacing) / rowWidth / 2);
					this.style.margin = '0 '+margin+'px';
				});

				rowWidth = 0;
				rowImages.length = 0;
			}
		});
	}
});