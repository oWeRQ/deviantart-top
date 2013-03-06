;(function($, undefined){
	$.plugin('modalWindow', {
		defaults: {
			shimClass: 'modal-shim',
			template: '<div class="modal-wrap"><div class="modal-window"><a class="modal-close" href="#"></a><div class="modal-content"></div></div></div>',
			elements: {
				window: '.modal-window',
				closeButton: '.modal-close',
				content: '.modal-content'
			},
			width: '',
			overlay: {},
			onBeforeOpen: $.noop,
			onOpen: $.noop,
			onBeforeClose: $.noop,
			onClose: $.noop
		},

		isOpen: false,

		init: function(){
			this.bindAll();

			this.container = $(document.documentElement);

			if (this.options.shim) {
				this.shim = $(this.options.shim);
			} else {
				this.shim = $('<div>', {
					'class': this.options.shimClass
				}).css({
					display: 'none'
				});
			}

			this.wrap = $(this.options.template).css({
				display: 'none',
				position: 'absolute'
			});

			this.shim.append(this.wrap);

			this.findAll(this.options.elements, this.wrap);

			this.window.css({
				width: this.options.width
			});
			this.content.append(this.el);

			this.options.window = this.shim;
			this.shim.appendTo(document.body);

			this.closeButton.add(this.shim).click(this.close);

			this.window.click(function(e){
				e.stopPropagation();
			});

			this.windowCenter = $.windowCenter(this.options, this.wrap);
			if (this.options.overlay)
				this.overlay = $.overlay(this.options.overlay);
		},

		open: function(e){
			if (e && e.preventDefault)
				e.preventDefault();

			if (this.isOpen)
				return;

			this.options.onBeforeOpen.call(this);

			this.container.css({
				overflow: 'hidden',
				paddingRight: window.outerWidth-document.documentElement.clientWidth
			});

			this.shim.show();
			this.wrap.show();
			this.windowCenter.updatePosition();

			if (this.overlay)
				this.overlay.show();

			this.isOpen = true;
			this.options.onOpen.call(this);
		},

		close: function(e){
			if (e && e.preventDefault)
				e.preventDefault();

			if (!this.isOpen)
				return;

			this.options.onBeforeClose.call(this);

			this.container.css({
				overflow: '',
				paddingRight: ''
			});

			this.shim.hide();
			this.wrap.hide();

			if (this.overlay)
				this.overlay.hide();

			this.isOpen = false;
			this.options.onClose.call(this);
		}
	});
})(jQuery);