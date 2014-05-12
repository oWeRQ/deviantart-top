$.plugin('updateControl', {
	defaults: {
		//elements: {}
	},

	activeLink: null,

	init: function(){
		//this.findAll(this.options.elements);
		//this.bindAll();

		var that = this;

		this.form = this.$el.find('form:first');
		this.galleries = this.$el.find('input[name="galleries[]"]');

		this.$el.find('.closeControl').click(function(e){
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

		if (this.activeLink && this.activeLink[0] === $link[0] && this.$el.hasClass('show')) {
			this.$el.removeClass('show');
			this.activeLink = null;
			return;
		}

		this.activeLink = $link;

		var imageGalleries = this.activeLink.data('galleries').split(', ');

		this.form[0].image_id.value = this.activeLink.data('id');

		this.galleries.each(function(){
			this.checked = (imageGalleries.indexOf(this.value) !== -1);
		});

		var linkPos = this.activeLink.offset();
		this.$el.css({
			opacity: 1,
			left: linkPos.left,
			top: linkPos.top+this.activeLink.height()
		}).addClass('show');
	},
	
	hide: function(){
		var that = this;
		this.$el.animate({
			opacity: 0,
			top: '-=12px'
		}, 500, function(){
			that.$el.removeClass('show');
		});
	}
});