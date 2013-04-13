/*!
 * jQuery Lightbox Evolution - for jQuery 1.3+
 * http://codecanyon.net/item/jquery-lightbox-evolution/115655?ref=aeroalquimia
 *
 * Copyright (c) 2010, Eduardo Daniel Sada
 * Released under CodeCanyon Regular License.
 * http://codecanyon.net/wiki/buying/howto-buying/licensing/
 *
 * Version: 1.5.3 (Oct 01 2011)
 *
 * Includes jQuery Easing v1.3
 * http://gsgd.co.uk/sandbox/jquery/easing/
 * Copyright (c) 2008, George McGinley Smith
 * Released under BSD License.
 */

(function ($) {
    var q = ($.browser.msie && parseInt($.browser.version, 10) < 7 && parseInt($.browser.version, 10) > 4);
    if ($.proxy === undefined) {
        $.extend({
            proxy: function (a, b) {
                if (a) {
                    proxy = function () {
                        return a.apply(b || this, arguments)
                    }
                };
                return proxy
            }
        })
    };
    $.extend($.fx.prototype, {
        update: function () {
            if (this.options.step) {
                this.options.step.call(this.elem, this.now, this)
            }(jQuery.fx.step[this.prop] || jQuery.fx.step._default)(this)
        }
    });
    $.extend($.easing, {
        easeOutBack: function (x, t, b, c, d, s) {
            if (s == undefined) s = 1.70158;
            return c * ((t = t / d - 1) * t * ((s + 1) * t + s) + 1) + b
        }
    });
    $.extend({
        LightBoxObject: {
            defaults: {
                name: 'jquery-lightbox',
                zIndex: 7000,
                width: 470,
                height: 280,
                background: '#FFFFFF',
                modal: false,
                overlay: {
                    'opacity': 0.6
                },
                showDuration: 400,
                closeDuration: 200,
                moveDuration: 1000,
                resizeDuration: 1000,
                showTransition: 'easeOutBack',
                closeTransition: 'easeOutBack',
                moveTransition: 'easeOutBack',
                resizeTransition: 'easeOutBack',
                shake: {
                    'distance': 10,
                    'duration': 100,
                    'transition': 'easeOutBack',
                    'loops': 2
                },
                flash: {
                    'width': 640,
                    'height': 360
                },
                maps: {
                    'width': 640,
                    'height': 360
                },
                emergefrom: 'top'
            },
            options: {},
            animations: {},
            gallery: {},
            image: {},
            esqueleto: {
                lightbox: [],
                buttons: {
                    close: [],
                    prev: [],
                    max: [],
                    next: []
                },
                background: [],
                html: []
            },
            visible: false,
            maximized: false,
            mode: 'image',
            videoregs: {
                swf: {
                    reg: /[^\.]\.(swf)\s*$/i
                },
                youtube: {
                    reg: /youtube\.com\/watch/i,
                    split: '=',
                    index: 1,
                    iframe: 1,
                    url: "http://www.youtube.com/embed/%id%?autoplay=1&amp;fs=1&amp;rel=0"
                },
                metacafe: {
                    reg: /metacafe\.com\/watch/i,
                    split: '/',
                    index: 4,
                    url: "http://www.metacafe.com/fplayer/%id%/.swf?playerVars=autoPlay=yes"
                },
                dailymotion: {
                    reg: /dailymotion\.com\/video/i,
                    split: '/',
                    index: 4,
                    url: "http://www.dailymotion.com/swf/video/%id%?additionalInfos=0&amp;autoStart=1"
                },
                google: {
                    reg: /google\.com\/videoplay/i,
                    split: '=',
                    index: 1,
                    url: "http://video.google.com/googleplayer.swf?autoplay=1&amp;hl=en&amp;docId=%id%"
                },
                vimeo: {
                    reg: /vimeo\.com/i,
                    split: '/',
                    index: 3,
                    iframe: 1,
                    url: "http://player.vimeo.com/video/%id%?hd=1&amp;autoplay=1&amp;show_title=1&amp;show_byline=1&amp;show_portrait=0&amp;color=&amp;fullscreen=1"
                },
                megavideo: {
                    reg: /megavideo.com/i,
                    split: '=',
                    index: 1,
                    url: "http://www.megavideo.com/v/%id%"
                },
                gametrailers: {
                    reg: /gametrailers.com/i,
                    split: '/',
                    index: 5,
                    url: "http://www.gametrailers.com/remote_wrap.php?mid=%id%"
                },
                collegehumornew: {
                    reg: /collegehumor.com\/video\//i,
                    split: 'video/',
                    index: 1,
                    url: "http://www.collegehumor.com/moogaloop/moogaloop.jukebox.swf?autostart=true&amp;fullscreen=1&amp;use_node_id=true&amp;clip_id=%id%"
                },
                collegehumor: {
                    reg: /collegehumor.com\/video:/i,
                    split: 'video:',
                    index: 1,
                    url: "http://www.collegehumor.com/moogaloop/moogaloop.swf?autoplay=true&amp;fullscreen=1&amp;clip_id=%id%"
                },
                ustream: {
                    reg: /ustream.tv/i,
                    split: '/',
                    index: 4,
                    url: "http://www.ustream.tv/flash/video/%id%?loc=%2F&amp;autoplay=true&amp;vid=%id%&amp;disabledComment=true&amp;beginPercent=0.5331&amp;endPercent=0.6292&amp;locale=en_US"
                },
                twitvid: {
                    reg: /twitvid.com/i,
                    split: '/',
                    index: 3,
                    url: "http://www.twitvid.com/player/%id%"
                },
                wordpress: {
                    reg: /v.wordpress.com/i,
                    split: '/',
                    index: 3,
                    url: "http://s0.videopress.com/player.swf?guid=%id%&amp;v=1.01"
                },
                vzaar: {
                    reg: /vzaar.com\/videos/i,
                    split: '/',
                    index: 4,
                    url: "http://view.vzaar.com/%id%.flashplayer?autoplay=true&amp;border=none"
                }
            },
            mapsreg: {
                bing: {
                    reg: /bing.com\/maps/i,
                    split: '?',
                    index: 1,
                    url: "http://www.bing.com/maps/embed/?emid=3ede2bc8-227d-8fec-d84a-00b6ff19b1cb&amp;w=%width%&amp;h=%height%&amp;%id%"
                },
                streetview: {
                    reg: /maps.google.com(.*)layer=c/i,
                    split: '?',
                    index: 1,
                    url: "http://maps.google.com/?output=svembed&amp;%id%"
                },
                googlev2: {
                    reg: /maps.google.com\/maps\ms/i,
                    split: '?',
                    index: 1,
                    url: "http://maps.google.com/maps/ms?output=embed&amp;%id%"
                },
                google: {
                    reg: /maps.google.com/i,
                    split: '?',
                    index: 1,
                    url: "http://maps.google.com/maps?%id%&amp;output=embed"
                }
            },
            imgsreg: /\.(jpg|jpeg|gif|png|bmp|tiff)(.*)?$/i,
            overlay: {
                create: function (b) {
                    this.options = b;
                    this.element = $('<div id="' + new Date().getTime() + '" class="' + this.options.name + '-overlay"></div>');
                    this.element.css($.extend({}, {
                        'position': 'fixed',
                        'top': 0,
                        'left': 0,
                        'opacity': 0,
                        'display': 'none',
                        'z-index': this.options.zIndex
                    }, this.options.style));
                    this.element.click($.proxy(function (a) {
                        if (this.options.hideOnClick) {
                            if ($.isFunction(this.options.callback)) {
                                this.options.callback()
                            } else {
                                this.hide()
                            }
                        }
                        a.preventDefault()
                    }, this));
                    this.hidden = true;
                    this.inject();
                    return this
                },
                inject: function () {
                    this.target = $(document.body);
                    this.target.append(this.element);
                    if (q) {
                        this.element.css({
                            'position': 'absolute'
                        });
                        var a = parseInt(this.element.css('zIndex'));
                        if (!a) {
                            a = 1;
                            var b = this.element.css('position');
                            if (b == 'static' || !b) {
                                this.element.css({
                                    'position': 'relative'
                                })
                            }
                            this.element.css({
                                'zIndex': a
                            })
                        }
                        a = ( !! (this.options.zIndex || this.options.zIndex === 0) && a > this.options.zIndex) ? this.options.zIndex : a - 1;
                        if (a < 0) {
                            a = 1
                        }
                        this.shim = $('<iframe id="IF_' + new Date().getTime() + '" scrolling="no" frameborder=0 src=""></iframe>');
                        this.shim.css({
                            zIndex: a,
                            position: 'absolute',
                            top: 0,
                            left: 0,
                            border: 'none',
                            width: 0,
                            height: 0,
                            opacity: 0
                        });
                        this.shim.insertAfter(this.element);
                        $('html, body').css({
                            'height': '100%',
                            'width': '100%',
                            'margin-left': 0,
                            'margin-right': 0
                        })
                    }
                },
                resize: function (x, y) {
                    this.element.css({
                        'height': 0,
                        'width': 0
                    });
                    if (this.shim) {
                        this.shim.css({
                            'height': 0,
                            'width': 0
                        })
                    };
                    var a = {
                        x: $(document).width(),
                        y: $(document).height()
                    };
                    this.element.css({
                        'width': '100%',
                        'height': y ? y : a.y
                    });
                    if (this.shim) {
                        this.shim.css({
                            'height': 0,
                            'width': 0
                        });
                        this.shim.css({
                            'position': 'absolute',
                            'left': 0,
                            'top': 0,
                            'width': this.element.width(),
                            'height': y ? y : a.y
                        })
                    }
                    return this
                },
                show: function (a) {
                    if (!this.hidden) {
                        return this
                    };
                    if (this.transition) {
                        this.transition.stop()
                    };
                    if (this.shim) {
                        this.shim.css({
                            'display': 'block'
                        })
                    };
                    this.element.css({
                        'display': 'block',
                        'opacity': 0
                    });
                    this.target.bind('resize', $.proxy(this.resize, this));
                    this.resize();
                    this.hidden = false;
                    this.transition = this.element.fadeTo(this.options.showDuration, this.options.style.opacity, $.proxy(function () {
                        if (this.options.style.opacity) {
                            this.element.css(this.options.style)
                        };
                        this.element.trigger('show');
                        if ($.isFunction(a)) {
                            a()
                        }
                    }, this));
                    return this
                },
                hide: function (a) {
                    if (this.hidden) {
                        return this
                    };
                    if (this.transition) {
                        this.transition.stop()
                    };
                    if (this.shim) {
                        this.shim.css({
                            'display': 'none'
                        })
                    };
                    this.target.unbind('resize');
                    this.hidden = true;
                    this.transition = this.element.fadeTo(this.options.closeDuration, 0, $.proxy(function () {
                        this.element.trigger('hide');
                        if ($.isFunction(a)) {
                            a()
                        };
                        this.element.css({
                            'height': 0,
                            'width': 0,
                            'display': 'none'
                        })
                    }, this));
                    return this
                }
            },
            create: function (a) {
                this.options = $.extend(true, this.defaults, a);
                this.overlay.create({
                    name: this.options.name,
                    style: this.options.overlay,
                    hideOnClick: !this.options.modal,
                    zIndex: this.options.zIndex - 1,
                    callback: $.proxy(this.close, this),
                    showDuration: this.options.showDuration,
                    closeDuration: this.options.closeDuration
                });
                this.esqueleto.lightbox = $('<div class="' + this.options.name + ' ' + this.options.name + '-mode-image"><div class="' + this.options.name + '-border-top-left"></div><div class="' + this.options.name + '-border-top-middle"></div><div class="' + this.options.name + '-border-top-right"></div><a class="' + this.options.name + '-button-close" href="#close"><span>Close</span></a><div class="' + this.options.name + '-navigator"><a class="' + this.options.name + '-button-left" href="#"><span>Previous</span></a><a class="' + this.options.name + '-button-right" href="#"><span>Next</span></a></div><div class="' + this.options.name + '-buttons"><div class="' + this.options.name + '-buttons-init"></div><a class="' + this.options.name + '-button-left" href="#"><span>Previous</span></a><a class="' + this.options.name + '-button-max" href="#"><span>Maximize</span></a><div class="' + this.options.name + '-buttons-custom"></div><a class="' + this.options.name + '-button-right" href="#"><span>Next</span></a><div class="' + this.options.name + '-buttons-end"></div></div><div class="' + this.options.name + '-background"></div><div class="' + this.options.name + '-html"></div><div class="' + this.options.name + '-border-bottom-left"></div><div class="' + this.options.name + '-border-bottom-middle"></div><div class="' + this.options.name + '-border-bottom-right"></div></div>');
                this.esqueleto.navigator = $('.' + this.options.name + '-navigator', this.esqueleto.lightbox);
                this.esqueleto.buttons.div = $('.' + this.options.name + '-buttons', this.esqueleto.lightbox);
                this.esqueleto.buttons.close = $('.' + this.options.name + '-button-close', this.esqueleto.lightbox);
                this.esqueleto.buttons.prev = $('.' + this.options.name + '-button-left', this.esqueleto.lightbox);
                this.esqueleto.buttons.max = $('.' + this.options.name + '-button-max', this.esqueleto.lightbox);
                this.esqueleto.buttons.next = $('.' + this.options.name + '-button-right', this.esqueleto.lightbox);
                this.esqueleto.buttons.custom = $('.' + this.options.name + '-buttons-custom', this.esqueleto.lightbox);
                this.esqueleto.background = $('.' + this.options.name + '-background', this.esqueleto.lightbox);
                this.esqueleto.html = $('.' + this.options.name + '-html', this.esqueleto.lightbox);
                this.esqueleto.move = $('<div class="' + this.options.name + '-move"></div>').append(this.esqueleto.lightbox);
                this.esqueleto.move.css({
                    'position': 'absolute',
                    'z-index': this.options.zIndex,
                    'top': -999,
                    'left': -999
                });
                $('body').append(this.esqueleto.move);
                this.addevents();
                return this.esqueleto.lightbox
            },
            addevents: function () {
                this.esqueleto.buttons.close.bind('click', $.proxy(function (a) {
                    this.close();
                    a.preventDefault()
                }, this));
                $(window).bind('resize', $.proxy(function () {
                    if (this.visible) {
                        this.overlay.resize();
                        if (!this.maximized) {
                            this.movebox()
                        }
                    }
                }, this));
                $(window).bind('scroll', $.proxy(function () {
                    if (this.visible && !this.maximized) {
                        this.movebox()
                    }
                }, this));
                $(document).bind('keydown', $.proxy(function (a) {
                    if (this.visible) {
                        if (a.keyCode == 27 && this.overlay.options.hideOnClick) {
                            this.close()
                        }
                        if (this.gallery.total > 1) {
                            if (a.keyCode == 37) {
                                this.esqueleto.buttons.prev.triggerHandler('click', a)
                            }
                            if (a.keyCode == 39) {
                                this.esqueleto.buttons.next.triggerHandler('click', a)
                            }
                        }
                    }
                }, this));
                this.esqueleto.buttons.max.bind('click', $.proxy(function (a) {
                    this.maximinimize();
                    a.preventDefault()
                }, this));
                this.overlay.element.bind('show', $.proxy(function () {
                    $(this).triggerHandler('show')
                }, this));
                this.overlay.element.bind('hide', $.proxy(function () {
                    $(this).triggerHandler('close')
                }, this))
            },
            create_gallery: function (b) {
                if ($.isArray(b) && b.length > 1 && b[0].match(this.imgsreg)) {
                    this.gallery.images = b;
                    this.gallery.current = 0;
                    this.gallery.total = b.length;
                    b = b[0];
                    this.esqueleto.buttons.prev.unbind('click');
                    this.esqueleto.buttons.next.unbind('click');
                    this.esqueleto.buttons.prev.bind('click', $.proxy(function (a) {
                        if (this.gallery.current - 1 < 0) {
                            this.gallery.current = this.gallery.total - 1
                        } else {
                            this.gallery.current = this.gallery.current - 1
                        }
                        this.show(this.gallery.images[this.gallery.current]);
                        a.preventDefault()
                    }, this));
                    this.esqueleto.buttons.next.bind('click', $.proxy(function (a) {
                        if (this.gallery.current + 1 >= this.gallery.total) {
                            this.gallery.current = 0
                        } else {
                            this.gallery.current = this.gallery.current + 1
                        }
                        this.show(this.gallery.images[this.gallery.current]);
                        a.preventDefault()
                    }, this))
                }
                if (this.gallery.total > 1) {
                    if (this.esqueleto.navigator.css("display") == "none") {
                        this.esqueleto.buttons.div.show()
                    }
                    this.esqueleto.buttons.prev.show();
                    this.esqueleto.buttons.next.show()
                } else {
                    this.esqueleto.buttons.prev.hide();
                    this.esqueleto.buttons.next.hide()
                }
            },
            custombuttons: function (b, c) {
                $.each(b, $.proxy(function (i, a) {
                    this.esqueleto.buttons.custom.append($('<a href="#" class="' + a['class'] + '">' + a.html + '</a>').bind('click', $.proxy(function (e) {
                        if ($.isFunction(a.callback)) {
                            c = typeof c === "undefined" ? false : c[this.gallery.current || 0];
                            a.callback(this.image.src, this, c)
                        }
                        e.preventDefault()
                    }, this)))
                }, this));
                this.esqueleto.buttons.div.show()
            },
            show: function (b, c, d, f) {
                var g = '';
                var h = false;
                if (typeof b === "object" && b[0].nodeType) {
                    var j = b;
                    b = "#";
                    g = 'element'
                }
                if (($.isArray(b) && b.length <= 1) || b == '') {
                    return false
                };
                this.loading();
                h = this.visible;
                this.open();
                if (!h) {
                    this.movebox()
                };
                this.create_gallery(b, c);
                if ($.isArray(b) && b.length > 1) {
                    b = b[0]
                }
                var k = b.split("%LIGHTBOX%");
                var b = k[0];
                var l = k[1] || '';
                c = $.extend(true, {
                    'width': 0,
                    'height': 0,
                    'modal': 0,
                    'force': '',
                    'title': l,
                    'autoresize': true,
                    'move': -1,
                    'iframe': false,
                    'flashvars': '',
                    'cufon': true,
                    'onOpen': function () {},
                    'onClose': function () {}
                }, c || {});
                this.options.onOpen = c.onOpen;
                this.options.onClose = c.onClose;
                this.options.cufon = c.cufon;
                urloptions = this.unserialize(b);
                c = $.extend({}, c, urloptions);
                var m = {
                    x: $(window).width(),
                    y: $(window).height()
                };
                if (c.width && (c.width + '').indexOf("p") > 0) {
                    c.width = (m.x - 20) * c.width.substring(0, c.width.indexOf("p")) / 100
                }
                if (c.height && (c.height + '').indexOf("p") > 0) {
                    c.height = (m.y - 20) * c.height.substring(0, c.height.indexOf("p")) / 100
                }
                this.esqueleto.background.unbind('complete');
                this.overlay.options.hideOnClick = !c.modal;
                this.esqueleto.buttons.max.removeClass(this.options.name + '-button-min');
                this.esqueleto.buttons.max.addClass(this.options.name + '-button-max');
                this.maximized = !(c.move > 0 || (c.move == -1 && c.autoresize));
                if ($.isArray(c.buttons)) {
                    this.custombuttons(c.buttons, f)
                }
                if (!this.esqueleto.buttons.custom.is(":empty")) {
                    this.esqueleto.buttons.div.show()
                }
                if (c.force != '') {
                    g = c.force
                } else if (c.iframe) {
                    g = 'iframe'
                } else if (b.match(this.imgsreg)) {
                    g = 'image'
                } else {
                    $.each(this.videoregs, $.proxy(function (i, e) {
                        if (b.split('?')[0].match(e.reg)) {
                            if (e.split) {
                                videoid = b.split(e.split)[e.index].split('?')[0].split('&')[0];
                                b = e.url.replace("%id%", videoid)
                            }
                            g = e.iframe ? 'iframe' : 'flash';
                            c.width = c.width ? c.width : this.options.flash.width;
                            c.height = c.height ? c.height : this.options.flash.height;
                            return false
                        }
                    }, this));
                    $.each(this.mapsreg, function (i, e) {
                        if (b.match(e.reg)) {
                            g = 'iframe';
                            if (e.split) {
                                id = b.split(e.split)[e.index];
                                b = e.url.replace("%id%", id).replace("%width%", c.width).replace("%height%", c.height)
                            }
                            c.width = c.width ? c.width : this.options.maps.width;
                            c.height = c.height ? c.height : this.options.maps.height;
                            return false
                        }
                    });
                    if (g == '') {
                        if (b.match(/#/)) {
                            obj = b.substr(b.indexOf("#"));
                            if ($(obj).length > 0) {
                                g = 'inline';
                                b = obj
                            } else {
                                g = 'ajax'
                            }
                        } else {
                            g = 'ajax'
                        }
                    }
                }
                if (g == 'image') {
                    this.esqueleto.buttons.max.hide();
                    var n = new Image();
                    n.onload = $.proxy(function () {
                        n.onload = function () {};
                        if (!this.visible) {
                            return false
                        };
                        this.image = {
                            width: n.width,
                            height: n.height,
                            src: n.src
                        };
                        if (c.width) {
                            width = parseInt(c.width);
                            height = parseInt(c.height)
                        } else {
                            if (c.autoresize) {
                                var a = this.calculate(n.width, n.height);
                                width = a.width;
                                height = a.height;
                                if (n.width != width || n.height != height) {
                                    this.esqueleto.buttons.div.show();
                                    this.esqueleto.buttons.max.show()
                                }
                            } else {
                                width = n.width;
                                height = n.height
                            }
                        }
                        this.resize(width, height);
                        this.esqueleto.background.bind('complete', $.proxy(function () {
                            if (!this.visible) {
                                return false
                            };
                            this.changemode('image');
                            this.esqueleto.background.empty();
                            this.esqueleto.html.empty();
                            if (c.title != '') {
                                this.esqueleto.background.append($('<div class="' + this.options.name + '-title"></div>').html(c.title))
                            }
                            $(n).hide();
                            this.esqueleto.background.append(n);
                            $(n).stop().fadeIn(400, $.proxy(function () {
                                this.esqueleto.background.removeClass(this.options.name + '-loading')
                            }, this));
                            this.options.onOpen()
                        }, this))
                    }, this);
                    n.onerror = $.proxy(function () {
                        this.error("The requested image cannot be loaded. Please try again later.")
                    }, this);
                    n.src = b
                } else if (g == 'flash' || g == 'inline' || g == 'ajax' || g == 'element') {
                    if (g == 'inline') {
                        this.appendhtml($(b).clone(true).show(), c.width > 0 ? c.width : $(b).outerWidth(true), c.height > 0 ? c.height : $(b).outerHeight(true), 'html')
                    } else if (g == 'ajax') {
                        if (c.width) {
                            width = c.width;
                            height = c.height
                        } else {
                            this.error("You need to specify the size of the lightbox.");
                            return false
                        }
                        if (this.animations.ajax) {
                            this.animations.ajax.abort()
                        };
                        this.animations.ajax = $.ajax({
                            url: b,
                            type: "GET",
                            cache: false,
                            dataType: "html",
                            error: $.proxy(function () {
                                this.error("The requested content cannot be loaded. Please try again later.")
                            }, this),
                            success: $.proxy(function (a) {
                                this.appendhtml($(a), width, height, 'html')
                            }, this)
                        })
                    } else if (g == 'flash') {
                        var o = this.swf2html(b, c.width, c.height, c.flashvars);
                        this.appendhtml($(o), c.width, c.height, 'html')
                    } else if (g == 'element') {
                        this.appendhtml(j, c.width > 0 ? c.width : j.outerWidth(true), c.height > 0 ? c.height : j.outerHeight(true), 'html')
                    }
                } else if (g == 'iframe') {
                    if (c.width) {
                        width = c.width;
                        height = c.height
                    } else {
                        this.error("You need to specify the size of the lightbox.");
                        return false
                    }
                    this.appendhtml($('<iframe id="IF_' + (new Date().getTime()) + '" frameborder="0" src="' + b + '" style="margin:0; padding:0;" allowTransparency="true"></iframe>').css(c), c.width, c.height, 'html')
                }
                this.callback = $.isFunction(d) ? d : function (e) {}
            },
            swf2html: function (a, b, c, d) {
                if (typeof d == 'undefined' || d == '') d = 'autostart=1&autoplay=1&fullscreenbutton=1';
                var e = '<object width="' + b + '" height="' + c + '" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000"><param name="movie" value="' + a + '" style="margin:0; padding:0;"></param>';
                e += '<param name="allowFullScreen" value="true"></param><param name="allowscriptaccess" value="always"></param><param name="wmode" value="transparent"></param>';
                e += '<param name="autostart" value="true"></param><param name="autoplay" value="true"></param><param name="flashvars" value="' + d + '"></param>';
                e += '<param name="width" value="' + b + '"></param><param name="height" value="' + c + '"></param>';
                e += '<embed src="' + a + '" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true" autostart="true" autoplay="true" flashvars="' + d + '" wmode="transparent" width="' + b + '" height="' + c + '" style="margin:0; padding:0;"></embed></object>';
                return e
            },
            appendhtml: function (a, b, c, d) {
                if (typeof d !== 'undefined') {
                    this.changemode(d)
                }
                this.resize(b + 30, c + 20);
                this.esqueleto.background.bind('complete', $.proxy(function () {
                    this.esqueleto.background.removeClass(this.options.name + '-loading');
                    this.esqueleto.html.html(a);
                    this.esqueleto.html.html(a);
                    this.esqueleto.background.unbind('complete');
                    if (this.options.cufon && typeof Cufon !== 'undefined') {
                        Cufon.refresh()
                    }
                    this.options.onOpen()
                }, this))
            },
            movebox: function (w, h) {
                var a = {
                    x: $(window).width(),
                    y: $(window).height()
                };
                var b = {
                    x: $(window).scrollLeft(),
                    y: $(window).scrollTop()
                };
                var c = h != null ? h : this.esqueleto.lightbox.outerHeight();
                var d = w != null ? w : this.esqueleto.lightbox.outerWidth();
                var y = 0;
                var x = 0;
                x = b.x + ((a.x - d) / 2);
                if (this.visible) {
                    y = b.y + (a.y - c) / 2
                } else if (this.options.emergefrom == "bottom") {
                    y = (b.y + a.y + 14)
                } else {
                    y = (b.y - c) - 14
                }
                if (this.visible) {
                    if (!this.animations.move) {
                        this.morph(this.esqueleto.move, {
                            'left': x
                        }, 'move')
                    }
                    this.morph(this.esqueleto.move, {
                        'top': y
                    }, 'move')
                } else {
                    this.esqueleto.move.css({
                        'left': x,
                        'top': y
                    })
                }
            },
            morph: function (d, f, g, h, i) {
                var j = $.speed({
                    queue: i || false,
                    duration: this.options[g + 'Duration'],
                    easing: this.options[g + 'Transition'],
                    complete: ($.isFunction(h) ? $.proxy(h, this) : null)
                });
                return d[j.queue === false ? "each" : "queue"](function () {
                    if (parseFloat($.fn.jquery) > 1.5) {
                        if (j.queue === false) {
                            jQuery._mark(this)
                        }
                    }
                    var c = $.extend({}, j),
                        self = this;
                    c.curAnim = $.extend({}, f);
                    c.animatedProperties = {};
                    for (p in f) {
                        name = p;
                        val = f[name];
                        c.animatedProperties[name] = c.specialEasing && c.specialEasing[name] || c.easing || 'swing';
                        if (val === "hide" && hidden || val === "show" && !hidden) {
                            return c.complete.call(this)
                        }
                    }
                    $.each(f, function (a, b) {
                        var e = new $.fx(self, c, a);
                        e.custom(e.cur(true) || 0, b, "px")
                    });
                    return true
                })
            },
            resize: function (x, y) {
                if (this.visible) {
                    var a = {
                        x: $(window).width(),
                        y: $(window).height()
                    };
                    var b = {
                        x: $(window).scrollLeft(),
                        y: $(window).scrollTop()
                    };
                    var c = (b.x + (a.x - (x + 14)) / 2);
                    var d = (b.y + (a.y - (y + 14)) / 2);
                    if ($.browser.msie || ($.browser.mozilla && (parseFloat($.browser.version) < 1.9))) {
                        y += 4
                    }
                    this.animations.move = true;
                    this.morph(this.esqueleto.move.stop(), {
                        'left': (this.maximized && c < 0) ? 0 : c,
                        'top': (this.maximized && (y + 14) > a.y) ? b.y : d
                    }, 'move', $.proxy(function () {
                        this.move = false
                    }, this.animations));
                    this.morph(this.esqueleto.html, {
                        'height': y - 20
                    }, 'resize');
                    this.morph(this.esqueleto.lightbox.stop(), {
                        'width': (x + 14),
                        'height': y - 20
                    }, 'resize', {}, true);
                    this.morph(this.esqueleto.buttons.div, {
                        'width': x,
                        'height': y
                    }, 'resize');
                    this.morph(this.esqueleto.navigator, {
                        'width': x
                    }, 'resize');
                    this.morph(this.esqueleto.navigator, {
                        'top': (y - 90) / 2
                    }, 'move');
                    this.morph(this.esqueleto.background.stop(), {
                        'width': x,
                        'height': y
                    }, 'resize', function () {
                        $(this.esqueleto.background).trigger('complete')
                    })
                } else {
                    this.esqueleto.html.css({
                        'height': y - 20
                    });
                    this.esqueleto.lightbox.css({
                        'width': x + 14,
                        'height': y - 20
                    });
                    this.esqueleto.background.css({
                        'width': x,
                        'height': y
                    });
                    this.esqueleto.buttons.div.css({
                        'width': x,
                        'height': y
                    });
                    this.esqueleto.navigator.css({
                        'width': x,
                        'height': 90
                    })
                }
            },
            close: function (a) {
                this.visible = false;
                this.gallery = {};
                this.options.onClose();
                if ($.browser.msie) {
                    this.esqueleto.background.empty();
                    this.esqueleto.html.hide().empty().show();
                    this.esqueleto.buttons.custom.empty();
                    this.esqueleto.move.css({
                        'display': 'none'
                    });
                    this.movebox()
                } else {
                    this.esqueleto.move.animate({
                        'opacity': 0,
                        'top': '-=40'
                    }, {
                        queue: false,
                        complete: ($.proxy(function () {
                            this.esqueleto.background.empty();
                            this.esqueleto.html.empty();
                            this.esqueleto.buttons.custom.empty();
                            this.movebox();
                            this.esqueleto.move.css({
                                'display': 'none',
                                'opacity': 1,
                                'overflow': 'visible'
                            })
                        }, this))
                    })
                }
                this.overlay.hide($.proxy(function () {
                    if ($.isFunction(this.callback)) {
                        this.callback.apply(this, $.makeArray(a))
                    }
                }, this));
                this.esqueleto.background.stop(true, false);
                this.esqueleto.background.unbind('complete')
            },
            open: function () {
                this.visible = true;
                if ($.browser.msie) {
                    this.esqueleto.move.get(0).style.removeAttribute('filter');
                    this.esqueleto.buttons.div.css({
                        'position': 'static'
                    }).css({
                        'position': 'absolute'
                    })
                }
                this.esqueleto.move.css({
                    'display': 'block',
                    'overflow': 'visible'
                }).show();
                this.overlay.show()
            },
            shake: function () {
                var x = this.options.shake.distance;
                var d = this.options.shake.duration;
                var t = this.options.shake.transition;
                var o = this.options.shake.loops;
                var l = this.esqueleto.move.position().left;
                var e = this.esqueleto.move;
                for (i = 0; i < o; i++) {
                    e.animate({
                        left: l + x
                    }, d, t);
                    e.animate({
                        left: l - x
                    }, d, t)
                };
                e.animate({
                    left: l + x
                }, d, t);
                e.animate({
                    left: l
                }, d, t)
            },
            changemode: function (a) {
                if (a != this.mode) {
                    this.esqueleto.lightbox.removeClass(this.options.name + '-mode-' + this.mode);
                    this.mode = a;
                    this.esqueleto.lightbox.addClass(this.options.name + '-mode-' + this.mode)
                }
                this.esqueleto.move.css({
                    'overflow': 'visible'
                })
            },
            error: function (a) {
                alert(a);
                this.close()
            },
            unserialize: function (d) {
                var e = /lightbox\[(.*)?\]$/i;
                var f = {};
                if (d.match(/#/)) {
                    d = d.slice(0, d.indexOf("#"))
                }
                d = d.slice(d.indexOf('?') + 1).split("&");
                $.each(d, function () {
                    var a = this.split("=");
                    var b = a[0];
                    var c = a[1];
                    if (b.match(e)) {
                        if (isFinite(c)) {
                            c = parseInt(c)
                        } else if (c.toLowerCase() == "true") {
                            c = true
                        } else if (c.toLowerCase() == "false") {
                            c = false
                        }
                        f[b.match(e)[1]] = c
                    }
                });
                return f
            },
            calculate: function (x, y) {
                var a = $(window).width() - 50;
                var b = $(window).height() - 50;
                if (x > a) {
                    y = y * (a / x);
                    x = a;
                    if (y > b) {
                        x = x * (b / y);
                        y = b
                    }
                } else if (y > b) {
                    x = x * (b / y);
                    y = b;
                    if (x > a) {
                        y = y * (a / x);
                        x = a
                    }
                }
                return {
                    width: parseInt(x),
                    height: parseInt(y)
                }
            },
            loading: function () {
                this.changemode('image');
                this.esqueleto.background.empty();
                this.esqueleto.html.empty();
                this.esqueleto.background.addClass(this.options.name + '-loading');
                this.esqueleto.buttons.div.hide();
                if (this.visible == false) {
                    this.movebox(this.options.width, this.options.height);
                    this.resize(this.options.width, this.options.height)
                }
            },
            maximinimize: function () {
                if (this.maximized) {
                    this.maximized = false;
                    this.esqueleto.buttons.max.removeClass(this.options.name + '-button-min');
                    this.esqueleto.buttons.max.addClass(this.options.name + '-button-max');
                    var a = this.calculate(this.image.width, this.image.height);
                    this.loading();
                    this.esqueleto.buttons.div.show();
                    this.resize(a.width, a.height)
                } else {
                    this.maximized = true;
                    this.esqueleto.buttons.max.removeClass(this.options.name + '-button-max');
                    this.esqueleto.buttons.max.addClass(this.options.name + '-button-min');
                    this.loading();
                    this.esqueleto.buttons.div.show();
                    this.resize(this.image.width, this.image.height)
                }
            }
        },
        lightbox: function (a, b, c) {
            if (typeof a !== 'undefined') {
                return $.LightBoxObject.show(a, b, c)
            } else {
                return $.LightBoxObject
            }
        }
    });
    $.fn.lightbox = function (l, m) {
        return $(this).live('click', function (e) {
            $(this).blur();
            var b = [];
            var c = $.trim($(this).attr('rel')) || '';
            var d = $.trim($(this).attr('title')) || '';
            var f = $(this);
            c = c.replace('[', '\\\\[');
            c = c.replace(']', '\\\\]');
            if (!c || c == '' || c === 'nofollow') {
                b = $(this).attr('href');
                copy_options = (d || d != '') ? $.extend({}, l, {
                    'title': d
                }) : l
            } else {
                var g = [];
                var h = [];
                var j = [];
                var k = false;
                $("a[rel=" + c + "], area[rel=" + c + "]", this.ownerDocument).each($.proxy(function (i, a) {
                    if (this == a) {
                        h.unshift(a);
                        k = true
                    } else if (k == false) {
                        j.push(a)
                    } else {
                        h.push(a)
                    }
                }, this));
                g = f = h.concat(j);
                $.each(g, function () {
                    var a = $.trim($(this).attr('title')) || '';
                    a = a ? "%LIGHTBOX%" + a : '';
                    b.push($(this).attr('href') + a)
                });
                if (b.length == 1) {
                    b = b[0]
                }
                copy_options = l
            }
            $.LightBoxObject.show(b, copy_options, m, f);
            e.preventDefault();
            e.stopPropagation()
        })
    };
    $(function () {
        if (parseFloat($.fn.jquery) > 1.2) {
            $.LightBoxObject.create()
        } else {
            throw "The jQuery version that was loaded is too old. Lightbox Evolution requires jQuery 1.3+";
        }
    })
})(jQuery);