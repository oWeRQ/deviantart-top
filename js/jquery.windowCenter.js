;(function($, undefined){
	$.plugin('windowCenter', {
		defaults: {
			position:'absolute',
			minTop: 0,
			minLeft: 0,
			updateOnResize: false,
			resizeSpeed: 500
		},
		position: {
			top: 0,
			left: 0
		},
		init: function(){
			this.bindAll('onWindowResize');

			this.options.window = (this.options.window instanceof jQuery) ? this.options.window : $(this.options.window || window);

			this.$el.css({
				'position': this.options.position
			});

			if (this.options.updateOnResize)
				$(window).resize(this.onWindowResize);
		},
		calcPosition: function(width, height){
			var position = {};

			position.top = (this.options.window.height() - height) / 2;
			position.left = (this.options.window.width() - width) / 2;

			if (this.options.position === 'absolute') {
				position.top += this.options.window.scrollTop();
				position.left += this.options.window.scrollLeft();
			}

			if (position.top < this.options.minTop)
				position.top = this.options.minTop;

			if (position.left < this.options.minLeft)
				position.left = this.options.minLeft;

			return position;
		},
		updatePosition: function(position, speed){
			if (position === undefined) {
				this.position = this.calcPosition(this.$el.outerWidth(), this.$el.outerHeight());
			} else if ($.isPlainObject(position)) {
				if (position.top && position.left)
					this.position = position;
				else if (position.width && position.height)
					this.position = this.calcPosition(position.width, position.height);
			}

			if (!speed)
				this.$el.css(this.position);
			else
				this.$el.stop().animate(this.position, speed);
		},
		onWindowResize: function(){
			this.updatePosition(undefined, this.options.resizeSpeed);
		}
	});
})(jQuery);