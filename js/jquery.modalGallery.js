$.plugin('modalGallery', {
	defaults: {
		//elements: {}
		gallery: {}
	},
	
	init: function(){
		var that = this;

		//this.findAll(this.options.elements);
		this.bindAll('updateGalleryPos');

		this.modal = $.modalWindow({
			onClose: function(){
				$(window).off('resize', that.updateGalleryPos);
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
		).appendTo(this.modal.content);

		this.gallery = $.gallery(this.options.gallery, galleryEl);
	},

	updateGalleryPos: function(){
		var ratio = 1.5,
			imageHeight = window.innerHeight - 170,
			imageWidth = imageHeight * ratio,
			maxWidth = window.innerWidth - 60;

		if (imageWidth > maxWidth) {
			imageWidth = maxWidth;
			imageHeight = imageWidth / ratio;
		}

		this.gallery.$el.css({width: imageWidth});
		this.gallery.imageWrap.css({height: imageHeight});
		this.gallery.thumbsSlider.setActive(this.gallery.thumbsSlider.idx);

		this.modal.windowCenter.updatePosition();
	}
});