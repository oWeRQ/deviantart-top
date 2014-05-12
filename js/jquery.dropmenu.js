$.plugin('dropmenu', {
	defaults: {
		//elements: {}
	},

	links: null,
	action: null,
	activeLink: null,
	selected: -1,
	onClick: null,

	init: function(){
		var that = this;

		//this.findAll(this.options.elements);
		this.bindAll('keypress');

		this.links = this.$el.find('a').click(function(e){
			if (that.onClick)
				that.onClick.call(this, e);

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

	open: function(link, onClick){
		var $link = $(link);

		if (this.activeLink && this.activeLink[0] === $link[0] && this.$el.is(':visible')) {
			this.close();
			return;
		}

		$(document).on('keypress', this.keypress);

		this.activeLink = $link;
		this.onClick = onClick;

		var linkPos = this.activeLink.offset();
		this.$el.css({
			opacity: 1,
			left: linkPos.left,
			top: linkPos.top+this.activeLink.height()
		}).outerWidth(this.activeLink.outerWidth()).show();
	},
	
	close: function(){
		$(document).off('keypress', this.keypress);

		//this.$el.hide();
		this.activeLink = null;
		this.onClick = null;
		this.selected = -1;

		var that = this;
		this.$el.animate({
			opacity: 0,
			top: '-=8px'
		}, 500, function(){
			that.$el.hide();
		});
	}
});