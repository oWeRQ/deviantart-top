(function($){
	$.keymap = {
		backspace: [8],
		enter: [13],
		esc: [27],
		space: [32],
		left: [37],
		up: [38],
		right: [39],
		down: [40],
		plus: [43],
		'+': [43],
		minus: [45],
		'-': [45],
		a: [97, 1092],
		d: [100, 1074],
		e: [101, 1091],
		q: [113, 1081],
		r: [114, 1082],
		s: [115, 1099],
		w: [119, 1094]
	};

	$.Event.prototype.isKey = function() {
		var keyCode = this.keyCode || this.which;
		for (var i = 0; i < arguments.length; i++) {
			if ($.keymap[arguments[i]].indexOf(keyCode) !== -1)
				return true;
		}
		return false;
	}
})(jQuery);