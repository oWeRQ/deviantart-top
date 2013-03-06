;(function($, undefined){
	if ($.plugin !== undefined)
		return;

	$.plugin = function(pluginName, object){
		function Plugin(options, el) {
			if (!(this instanceof Plugin))
				return new Plugin(options, el);

			this.options = $.extend({}, this.defaults, options);
			this.$el = (el instanceof $) ? el : $(el);
			this.el = this.$el[0];

			return this.init();
		}

		Plugin.prototype = $.extend({
			pluginName: pluginName,
			defaults: {},
			init: $.noop,
			bindAll: function(){
				var funcs = arguments.length ? arguments : $.map(this, function(value, key){
					if ($.isFunction(value))
						return key;
				});

				for (var i = 0; i < funcs.length; i++) {
					this[funcs[i]] = $.proxy(this[funcs[i]], this);
				}
			},
			findAll: function(elements, context) {
				if (!$.isPlainObject(elements))
					throw new TypeError('first argument must be a plain object');

				for (var key in elements) {
					this[key] = (elements[key] instanceof $) ? elements[key] : (context || this.$el).find(elements[key]);
				}
			}
		}, object);

		Plugin.defaults = Plugin.prototype.defaults;

		$[pluginName] = Plugin;

		$.fn[pluginName] = function(method){
			var args = Array.prototype.slice.call(arguments, 1),
				options = $.isPlainObject(method) ? method : {},
				returnValue = this;

			this.each(function(){
				var plugin = $.data(this, pluginName),
					methodValue;

				if (plugin === undefined) {
					plugin = new Plugin(options, this);
					if (!(plugin instanceof Plugin)) {
						returnValue = plugin;
						return false;
					}
					$.data(this, pluginName, plugin);
				}

				if ($.isFunction(plugin[method])) {
					methodValue = plugin[method].apply(plugin, args);
					if (methodValue !== undefined && methodValue !== plugin) {
						returnValue = methodValue;
						return false;
					}
				}
			});

			return returnValue;
		};
	};
})(jQuery);