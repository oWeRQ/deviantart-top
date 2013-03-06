;(function($, undefined){
	$.plugin('overlay', {
		visible: false,
		defaults: {
			elementClass: 'modal-overlay',
			initStyles: {
				opacity: 0
			},
			showAnimate: {
				opacity: 0.6
			},
			showDuration: '',
			hideDuration: ''
		},
		init: function(){
			this.resize = $.proxy(this.resize, this);

			if (this.el === undefined) {
				this.$el = $('<div>', {
					'class': this.options.elementClass
				}).prependTo(document.body);

				this.el = this.$el[0];

				this.$el.data(this.pluginName, this);
			}
		},
		getDocHeight: function(){
			var doc = document.documentElement;
			return Math.max(doc.scrollHeight, doc.clientHeight);
		},
		resize: function(){
			this.$el.css({
				height: this.getDocHeight()
			});
		},
		show: function(){
			if (this.visible === false) {
				this.visible = true;

				this.$el.stop().css($.extend({
					display: 'block',
					height: this.getDocHeight()
				}, this.options.initStyles));

				$(window).on('resize', this.resize);

				this.$el.animate(this.options.showAnimate, this.options.showDuration);
			}
		},
		hide: function(){
			if (this.visible === true) {
				this.visible = false;

				$(window).off('resize', this.resize);

				this.$el.stop().fadeOut(this.options.hideDuration);
			}
		}
	});
})(jQuery);