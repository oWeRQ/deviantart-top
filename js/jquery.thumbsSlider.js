$.plugin('thumbsSlider', {
	defaults: {
		paddingRight: 0,
		slideDuration: 400,
		elements: {
			wrapEl: '.thumbs-wrap',
			prevEl: '.thumbs-prev',
			nextEl: '.thumbs-next',
			listEl: '.thumbs-list',
			listEls: '.thumbs-list > li',
			images: '.thumbs-list img'
		},
		onPrevDisabled: $.noop,
		onNextDisabled: $.noop
	},
	idx: 0,
	imagesRemained: 0,
	init: function(){
		this.findAll(this.options.elements);
		this.bindAll('imageLoad', 'prev', 'next');

		this.imagesRemained = this.images.length;

		this.prevEl.click(this.prev);
		this.nextEl.click(this.next);
		this.images.load(this.imageLoad);

		this.lastEl = this.listEls.last();

		this.setActive(this.idx);
	},
	imageLoad: function(){
		if (--this.imagesRemained === 0) {
			this.setActive(this.idx);
		}
	},
	prev: function(e){
		e.preventDefault();

		this.setActive(this.getPrevIdx());
	},
	next: function(e){
		e.preventDefault();

		this.setActive(this.getNextIdx());
	},
	getPrevIdx: function(){
		var idx = this.idx-1,
			prevEls = this.listEls.eq(this.idx).prevAll(),
			minLeft = -this.listEl.prop('offsetLeft');

		prevEls.each(function(i){
			if (this.offsetLeft < minLeft) {
				idx -= i;
				return false;
			}
		});

		return idx;
	},
	getNextIdx: function(){
		var idx = this.idx+1,
			nextEls = this.listEls.eq(this.idx).nextAll(),
			maxLeft = -this.listEl.prop('offsetLeft') + this.wrapEl.width();

		nextEls.each(function(i){
			if (this.offsetLeft + this.clientWidth > maxLeft) {
				idx += i;
				return false;
			}
		});

		return idx;
	},
	getThumbsWidth: function(){
		return this.lastEl.prop('offsetLeft') + this.lastEl.width() + this.options.paddingRight;
	},
	getThumbCenterOffset: function(idx){
		var thumb = this.listEls.eq(idx);
		return thumb.prop('offsetLeft') - this.wrapEl.width()/2 + thumb.width()/2;
	},
	setActive: function(idx){
		this.idx = Math.max(0, Math.min(idx, this.listEls.length-1));

		var maxLeft = this.getThumbsWidth() - this.wrapEl.width();
			left = Math.max(0, Math.min(this.getThumbCenterOffset(this.idx), maxLeft));

		this.prevEl.toggleClass('disabled', left === 0);
		this.nextEl.toggleClass('disabled', left === maxLeft || maxLeft < 0);

		if (left === 0)
			this.options.onPrevDisabled.call(this);

		if (left === maxLeft || maxLeft < 0)
			this.options.onNextDisabled.call(this);

		this.listEl.stop().animate({
			left: -left
		}, this.options.slideDuration);
	}
});