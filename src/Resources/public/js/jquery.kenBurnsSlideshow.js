(function ( $, window, document, undefined ) {
    var pluginName = 'kenBurnsSlideshow';
    var defaults = {
        images: [],
        opacityTransistionTime: .3,
        timePerSlide: 15000,
        maxZoom: 1.5,
        onStart: false,
        urlPrefix: '',
        shuffle: true
    };
    var _intervalTimer = false;
    var _paused = false;

    function Plugin(element, options) {
        this.element = element;
        this.options = $.extend( {}, defaults, options);
        this._defaults = defaults;
        this._name = pluginName;
        if (!Math.hasOwnProperty('getRandomInt')) {
            Math.getRandomInt = function (min, max) {
                min = Math.ceil(min);
                max = Math.floor(max);
                return Math.floor(Math.random() * (max - min)) + min;
            };
        }

        if (!Math.hasOwnProperty('getRandomBool')) {
            Math.getRandomBool = function () {
                return Math.floor(Math.random() * 2) == 1;
            };
        }

        if (!Array.prototype.hasOwnProperty('removeAt')) {
            Array.prototype.removeAt = function (idx) {
                var el = this[idx];
                this.splice(idx, 1);
                return el;
            };
        }

        this.init();
    }

    Plugin.prototype.init = function() {
        var element = $(this.element);
        if (element.css('position') != 'relative' && element.css('position') != 'absolute') {
            element.css('position', 'relative');
        }

        this._pastedImages = [];
        this._loadedImages = [];
        this._currentSlide = -1;
        this._started = false;
        this._lastSlide = -1;
        this._opacityTransistionTime = this.options.timePerSlide * this.options.opacityTransistionTime;
        $('<div class="pause_ov"><i class="fa fa-pause" aria-hidden="true"></i></div><div class="play_ov"><i class="fa fa-play-circle-o" aria-hidden="true"></i></div>').appendTo(this.element);
        for (i = 0; i < 10 && this.options.images.length > 0; i++) {
            var j = this.options.shuffle ? Math.getRandomInt(0, this.options.images.length) : 0;
            var image = this.options.images.removeAt(j);
            $('<img src="' + this.options.urlPrefix + image + '" class="slide" onload="jQuery(this.parentNode).kenBurnsSlideshow()._imageLoadCallback(this);" />').appendTo(this.element);
        }
        var self = this;
        $(document).keydown(this._keyHandler.bind(this));
    };

    Plugin.prototype._keyHandler = function(event) {
        switch (event.keyCode) {
            case 32: // space
                this._paused = !this._paused;
                if (!this._paused) {
                    this._intervalTimer = setInterval(this._nextSlide.bind(this), this.options.timePerSlide - this._opacityTransistionTime);
                    this._nextSlide();
                    $('.play_ov').css('display', 'block');
                    $('.play_ov').get(0).offsetHeight;
                    $('.play_ov').css('opacity', 0);
                    setTimeout(function() {
                        $('.play_ov').css('display', 'none').css('opacity', 1);
                    }, 1000);
                } else {
                    clearInterval(this._intervalTimer);
                    $('.pause_ov').css('display', 'block');
                    $('.pause_ov').get(0).offsetHeight;
                    $('.pause_ov').css('opacity', 0);
                    setTimeout(function() {
                        $('.pause_ov').css('display', 'none').css('opacity', 1);
                    }, 1000);
                }
                break;
            case 39: // right arrow
                clearInterval(this._intervalTimer);
                this._intervalTimer = setInterval(this._nextSlide.bind(this), this.options.timePerSlide - this._opacityTransistionTime);
                this._nextSlide();
                break;
            case 37: // left arrow
                break;
            case 27: // escape
                location.reload();
                break;
        }
    };

    Plugin.prototype._imageLoadCallback = function(elem) {
        var e = $(elem);
        e.attr('data-width', elem.width).attr('data-height', elem.height).attr('data-num', this._loadedImages.length);
        this._loadedImages.push(e);
        this._checkAndRun();
    };

    Plugin.prototype._checkAndRun = function() {
        if (this._loadedImages.length > 2 && !this._started) {
            this._started = true;
            if (typeof this.options.onStart == 'function') {
                this.options.onStart();
            }

            this._intervalTimer = setInterval(this._nextSlide.bind(this), this.options.timePerSlide - this._opacityTransistionTime);
            this._nextSlide();
        }
    };


    Plugin.prototype._nextSlide = function() {
        this._currentSlide++;
        if (this._currentSlide >= this._loadedImages.length) {
            this._currentSlide--;
            return;
        }
        if (this._lastSlide >= 0) {
            var el = $(this.element).find('.slide[data-num="' + this._lastSlide + '"]').css({ opacity: 0 });
            var me = this;
            setTimeout(function() {
                me._pastedImages.push(el);
                el.remove();
                if (me._pastedImages.length > 10) {
                    me._pastedImages[me._pastedImages.length - 11] = me._pastedImages[me._pastedImages.length - 11].src;
                }
            }, this._opacityTransistionTime * 1.2)
        }
        if (this.options.images.length > 0) {
            var j = this.options.shuffle ? Math.getRandomInt(0, this.options.images.length) : 0;
            var image = this.options.images.removeAt(j);
            $('<img src="' + this.options.urlPrefix + image + '" class="slide" onload="jQuery(this.parentNode).kenBurnsSlideshow()._imageLoadCallback(this);" />').appendTo(this.element);
        }
        var slide = $(this.element).find('.slide[data-num="' + this._currentSlide + '"]');
        var animation = {
            direction: Math.getRandomBool(),
            startPosition: Math.getRandomInt(0, Object.keys(this._startPosition).length),
            position: {
                small: {
                    transform: 'scale(1.000000001) translate(0, 0)'
                },
                large: {
                    transform: 'scale(1.2)'
                },
                start: {
                    opacity: 0,
                    'z-index': 2
                },
                end: {
                    opacity: 1
                }
            }
        };
        var iw = slide.data('width');
        var ih = slide.data('height');
        var cw = $('#canvas').width();
        var ch = $('#canvas').height();
        var min_iw = 0;
        var min_ih = 0;
        if (iw / ih > cw / ch) {
            min_ih = ch;
            min_iw = iw * min_ih / ih;
        } else {
            min_iw = cw;
            min_ih = ih * min_iw / iw;
        }
        var w_overflow = Math.abs(cw - min_iw);
        var h_overflow = Math.abs(ch - min_ih);
        animation.position.start.width = min_iw + 'px';
        animation.position.start.height = min_ih + 'px';
        switch (animation.startPosition) {
            case this._startPosition.BottomLeft:
                animation.position.small.left = 0;
                animation.position.small.bottom = 0;
                animation.position.large.transform += ' translate(' + (-w_overflow) + 'px, ' + (h_overflow) + 'px)';
                animation.position.start['transform-origin'] = 'bottom left';
                break;
            case this._startPosition.BottomRight:
                animation.position.small.right = 0;
                animation.position.small.bottom = 0;
                animation.position.large.transform += ' translate(' + (w_overflow) + 'px, ' + (h_overflow) + 'px)';
                animation.position.start['transform-origin'] = 'bottom right';
                break;
            case this._startPosition.TopLeft:
                animation.position.small.left = 0;
                animation.position.small.top = 0;
                animation.position.large.transform += ' translate(' + (-w_overflow) + 'px, ' + (-h_overflow) + 'px)';
                animation.position.start['transform-origin'] = 'top left';
                break;
            case this._startPosition.TopRight:
                animation.position.small.right = 0;
                animation.position.small.top = 0;
                animation.position.large.transform += ' translate(' + (w_overflow) + 'px, ' + (-h_overflow) + 'px)';
                animation.position.start['transform-origin'] = 'top right';
                break;
        }
        Object.assign(animation.position.start, animation.direction == this._direction.largeToSmall ? animation.position.large : animation.position.small);
        Object.assign(animation.position.end, animation.direction != this._direction.largeToSmall ? animation.position.large : animation.position.small);
        var last = this._currentSlide == this._loadedImages.length - 1;
        var first = this._currentSlide == 0;
        if (first) {
            animation.position.start.opacity = 1;
        }
        slide.css(animation.position.start);
        slide.get(0).offsetHeight;
        slide.css({
            'transition': 'transform ' + (this.options.timePerSlide) + 'ms ease-in-out, opacity ' + this._opacityTransistionTime + 'ms linear'
        });
        slide.get(0).offsetHeight;
        setTimeout(function() {
            slide.css(animation.position.end);
        }, 0);
        this._lastSlide = this._currentSlide;
    };

    Plugin.prototype._startPosition = {
        TopLeft: 0,
        TopRight: 1,
        BottomLeft: 2,
        BottomRight: 3
    };

    Plugin.prototype._direction = {
        largeToSmall: false,
        smallToLarge: true
    };

    $.fn[pluginName] = function( options ) {
        if (this.length == 1 && $.data(this[0], 'plugin_' + pluginName)) {
            return $.data(this[0], 'plugin_' + pluginName);
        }
        return this.each(function () {
            if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' + pluginName, new Plugin(this, options));
            }
        });
    }
})( jQuery, window, document );
