/*
 * jQuery Cycle Plugin (with Transition Definitions)
 * Examples and documentation at: http://jquery.malsup.com/cycle/
 * Copyright (c) 2007-2010 M. Alsup
 * Version: 2.86 (05-APR-2010)
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 * Requires: jQuery v1.2.6 or later
 */
(function($bn2) { 
	var ver = "2.86";
	if ($bn2.support == undefined) {
		$bn2.support = {
			opacity : !($bn2.browser.msie)
		};
	}
	function debug(s) {
		if ($bn2.fn.cycle.debug) {
			log(s);
		}
	}
	function log() {
		if (window.console && window.console.log) {
			//Ashok 
			//window.console.log("[cycle] "
					//+ Array.prototype.join.call(arguments, " "));
		}
	}
	$bn2.fn.cycle = function(options, arg2) {
		var o = {
			s : this.selector,
			c : this.context
		};
		if (this.length === 0 && options != "stop") {
			if (!$bn2.isReady && o.s) {
				log("DOM not ready, queuing slideshow");
				$bn2(function() {
					$bn2(o.s, o.c).cycle(options, arg2);
				});
				return this;
			}
			log("terminating; zero elements found by selector"
					+ ($bn2.isReady ? "" : " (DOM not ready)"));
			return this;
		}
		return this.each(function() {
			var opts = handleArguments(this, options, arg2);
			if (opts === false) {
				return;
			}
			opts.updateActivePagerLink = opts.updateActivePagerLink
					|| $bn2.fn.cycle.updateActivePagerLink;
			if (this.cycleTimeout) {
				clearTimeout(this.cycleTimeout);
			}
			this.cycleTimeout = this.cyclePause = 0;
			var $bn2cont = $bn2(this);
			var $bn2slides = opts.slideExpr ? $bn2(opts.slideExpr, this)
					: $bn2cont.children();
			var els = $bn2slides.get();
			if (els.length < 2) {
				log("terminating; too few slides: " + els.length);
				return;
			}
			var opts2 = buildOptions($bn2cont, $bn2slides, els, opts, o);
			if (opts2 === false) {
				return;
			}
			var startTime = opts2.continuous ? 10 : getTimeout(opts2.currSlide,
					opts2.nextSlide, opts2, !opts2.rev);
			if (startTime) {
				startTime += (opts2.delay || 0);
				if (startTime < 10) {
					startTime = 10;
				}
				debug("first timeout: " + startTime);
				this.cycleTimeout = setTimeout(function() {
					go(els, opts2, 0, !opts2.rev);
				}, startTime);
			}
		});
	};
	function handleArguments(cont, options, arg2) {
		if (cont.cycleStop == undefined) {
			cont.cycleStop = 0;
		}
		if (options === undefined || options === null) {
			options = {};
		}
		if (options.constructor == String) {
			switch (options) {
			case "destroy":
			case "stop":
				var opts = $bn2(cont).data("cycle.opts");
				if (!opts) {
					return false;
				}
				cont.cycleStop++;
				if (cont.cycleTimeout) {
					clearTimeout(cont.cycleTimeout);
				}
				cont.cycleTimeout = 0;
				$bn2(cont).removeData("cycle.opts");
				if (options == "destroy") {
					destroy(opts);
				}
				return false;
			case "toggle":
				cont.cyclePause = (cont.cyclePause === 1) ? 0 : 1;
				checkInstantResume(cont.cyclePause, arg2, cont);
				return false;
			case "pause":
				cont.cyclePause = 1;
				return false;
			case "resume":
				cont.cyclePause = 0;
				checkInstantResume(false, arg2, cont);
				return false;
			case "prev":
			case "next":
				var opts = $bn2(cont).data("cycle.opts");
				if (!opts) {
					log('options not found, "prev/next" ignored');
					return false;
				}
				$bn2.fn.cycle[options](opts);
				return false;
			default:
				options = {
					fx : options
				};
			}
			return options;
		} else {
			if (options.constructor == Number) {
				var num = options;
				options = $bn2(cont).data("cycle.opts");
				if (!options) {
					log("options not found, can not advance slide");
					return false;
				}
				if (num < 0 || num >= options.elements.length) {
					log("invalid slide index: " + num);
					return false;
				}
				options.nextSlide = num;
				if (cont.cycleTimeout) {
					clearTimeout(cont.cycleTimeout);
					cont.cycleTimeout = 0;
				}
				if (typeof arg2 == "string") {
					options.oneTimeFx = arg2;
				}
				go(options.elements, options, 1, num >= options.currSlide);
				return false;
			}
		}
		return options;
		function checkInstantResume(isPaused, arg2, cont) {
			if (!isPaused && arg2 === true) {
				var options = $bn2(cont).data("cycle.opts");
				if (!options) {
					log("options not found, can not resume");
					return false;
				}
				if (cont.cycleTimeout) {
					clearTimeout(cont.cycleTimeout);
					cont.cycleTimeout = 0;
				}
				go(options.elements, options, 1, 1);
			}
		}
	}
	function removeFilter(el, opts) {
		if (!$bn2.support.opacity && opts.cleartype && el.style.filter) {
			try {
				el.style.removeAttribute("filter");
			} catch (smother) {
			}
		}
	}
	function destroy(opts) {
		if (opts.next) {
			$bn2(opts.next).unbind(opts.prevNextEvent);
		}
		if (opts.prev) {
			$bn2(opts.prev).unbind(opts.prevNextEvent);
		}
		if (opts.pager || opts.pagerAnchorBuilder) {
			$bn2.each(opts.pagerAnchors || [], function() {
				this.unbind().remove();
			});
		}
		opts.pagerAnchors = null;
		if (opts.destroy) {
			opts.destroy(opts);
		}
	}
	function buildOptions($bn2cont, $bn2slides, els, options, o) {
		var opts = $bn2.extend( {}, $bn2.fn.cycle.defaults, options || {},
				$bn2.metadata ? $bn2cont.metadata() : $bn2.meta ? $bn2cont
						.data() : {});
		if (opts.autostop) {
			opts.countdown = opts.autostopCount || els.length;
		}
		var cont = $bn2cont[0];
		$bn2cont.data("cycle.opts", opts);
		opts.$bn2cont = $bn2cont;
		opts.stopCount = cont.cycleStop;
		opts.elements = els;
		opts.before = opts.before ? [ opts.before ] : [];
		opts.after = opts.after ? [ opts.after ] : [];
		opts.after.unshift(function() {
			opts.busy = 0;
		});
		if (!$bn2.support.opacity && opts.cleartype) {
			opts.after.push(function() {
				removeFilter(this, opts);
			});
		}
		if (opts.continuous) {
			opts.after.push(function() {
				go(els, opts, 0, !opts.rev);
			});
		}
		saveOriginalOpts(opts);
		if (!$bn2.support.opacity && opts.cleartype && !opts.cleartypeNoBg) {
			clearTypeFix($bn2slides);
		}
		if ($bn2cont.css("position") == "static") {
			$bn2cont.css("position", "relative");
		}
		if (opts.width) {
			$bn2cont.width(opts.width);
		}
		if (opts.height && opts.height != "auto") {
			$bn2cont.height(opts.height);
		}
		if (opts.startingSlide) {
			opts.startingSlide = parseInt(opts.startingSlide);
		}
		if (opts.random) {
			opts.randomMap = [];
			for ( var i = 0; i < els.length; i++) {
				opts.randomMap.push(i);
			}
			opts.randomMap.sort(function(a, b) {
				return Math.random() - 0.5;
			});
			opts.randomIndex = 1;
			opts.startingSlide = opts.randomMap[1];
		} else {
			if (opts.startingSlide >= els.length) {
				opts.startingSlide = 0;
			}
		}
		opts.currSlide = opts.startingSlide || 0;
		var first = opts.startingSlide;
		$bn2slides.css( {
			position : "absolute",
			top : 0,
			left : 0
		}).hide().each(
				function(i) {
					var z = first ? i >= first ? els.length - (i - first)
							: first - i : els.length - i;
					$bn2(this).css("z-index", z);
				});
		$bn2(els[first]).css("opacity", 1).show();
		removeFilter(els[first], opts);
		if (opts.fit && opts.width) {
			$bn2slides.width(opts.width);
		}
		if (opts.fit && opts.height && opts.height != "auto") {
			$bn2slides.height(opts.height);
		}
		var reshape = opts.containerResize && !$bn2cont.innerHeight();
		if (reshape) {
			var maxw = 0, maxh = 0;
			for ( var j = 0; j < els.length; j++) {
				var $bn2e = $bn2(els[j]), e = $bn2e[0], w = $bn2e.outerWidth(), h = $bn2e
						.outerHeight();
				if (!w) {
					w = e.offsetWidth || e.width || $bn2e.attr("width");
				}
				if (!h) {
					h = e.offsetHeight || e.height || $bn2e.attr("height");
				}
				maxw = w > maxw ? w : maxw;
				maxh = h > maxh ? h : maxh;
			}
			if (maxw > 0 && maxh > 0) {
				$bn2cont.css( {
					width : maxw + "px",
					height : maxh + "px"
				});
			}
		}
		if (opts.pause) {
			$bn2cont.hover(function() {
				this.cyclePause++;
			}, function() {
				this.cyclePause--;
			});
		}
		if (supportMultiTransitions(opts) === false) {
			return false;
		}
		var requeue = false;
		options.requeueAttempts = options.requeueAttempts || 0;
		$bn2slides
				.each(function() {
					var $bn2el = $bn2(this);
					this.cycleH = (opts.fit && opts.height) ? opts.height
							: ($bn2el.height() || this.offsetHeight
									|| this.height || $bn2el.attr("height") || 0);
					this.cycleW = (opts.fit && opts.width) ? opts.width
							: ($bn2el.width() || this.offsetWidth || this.width
									|| $bn2el.attr("width") || 0);
					if ($bn2el.is("img")) {
						var loadingIE = ($bn2.browser.msie && this.cycleW == 28
								&& this.cycleH == 30 && !this.complete);
						var loadingFF = ($bn2.browser.mozilla
								&& this.cycleW == 34 && this.cycleH == 19 && !this.complete);
						var loadingOp = ($bn2.browser.opera
								&& ((this.cycleW == 42 && this.cycleH == 19) || (this.cycleW == 37 && this.cycleH == 17)) && !this.complete);
						var loadingOther = (this.cycleH == 0
								&& this.cycleW == 0 && !this.complete);
						if (loadingIE || loadingFF || loadingOp || loadingOther) {
							if (o.s && opts.requeueOnImageNotLoaded
									&& ++options.requeueAttempts < 100) {
								log(
										options.requeueAttempts,
										" - img slide not loaded, requeuing slideshow: ",
										this.src, this.cycleW, this.cycleH);
								setTimeout(function() {
									$bn2(o.s, o.c).cycle(options);
								}, opts.requeueTimeout);
								requeue = true;
								return false;
							} else {
								log("could not determine size of image: "
										+ this.src, this.cycleW, this.cycleH);
							}
						}
					}
					return true;
				});
		if (requeue) {
			return false;
		}
		opts.cssBefore = opts.cssBefore || {};
		opts.animIn = opts.animIn || {};
		opts.animOut = opts.animOut || {};
		$bn2slides.not(":eq(" + first + ")").css(opts.cssBefore);
		if (opts.cssFirst) {
			$bn2($bn2slides[first]).css(opts.cssFirst);
		}
		if (opts.timeout) {
			opts.timeout = parseInt(opts.timeout);
			if (opts.speed.constructor == String) {
				opts.speed = $bn2.fx.speeds[opts.speed] || parseInt(opts.speed);
			}
			if (!opts.sync) {
				opts.speed = opts.speed / 2;
			}
			var buffer = opts.fx == "shuffle" ? 500 : 250;
			while ((opts.timeout - opts.speed) < buffer) {
				opts.timeout += opts.speed;
			}
		}
		if (opts.easing) {
			opts.easeIn = opts.easeOut = opts.easing;
		}
		if (!opts.speedIn) {
			opts.speedIn = opts.speed;
		}
		if (!opts.speedOut) {
			opts.speedOut = opts.speed;
		}
		opts.slideCount = els.length;
		opts.currSlide = opts.lastSlide = first;
		if (opts.random) {
			if (++opts.randomIndex == els.length) {
				opts.randomIndex = 0;
			}
			opts.nextSlide = opts.randomMap[opts.randomIndex];
		} else {
			opts.nextSlide = opts.startingSlide >= (els.length - 1) ? 0
					: opts.startingSlide + 1;
		}
		if (!opts.multiFx) {
			var init = $bn2.fn.cycle.transitions[opts.fx];
			if ($bn2.isFunction(init)) {
				init($bn2cont, $bn2slides, opts);
			} else {
				if (opts.fx != "custom" && !opts.multiFx) {
					log("unknown transition: " + opts.fx,
							"; slideshow terminating");
					return false;
				}
			}
		}
		var e0 = $bn2slides[first];
		if (opts.before.length) {
			opts.before[0].apply(e0, [ e0, e0, opts, true ]);
		}
		if (opts.after.length > 1) {
			opts.after[1].apply(e0, [ e0, e0, opts, true ]);
		}
		if (opts.next) {
			$bn2(opts.next).bind(opts.prevNextEvent, function() {
				return advance(opts, opts.rev ? -1 : 1);
			});
		}
		if (opts.prev) {
			$bn2(opts.prev).bind(opts.prevNextEvent, function() {
				return advance(opts, opts.rev ? 1 : -1);
			});
		}
		if (opts.pager || opts.pagerAnchorBuilder) {
			buildPager(els, opts);
		}
		exposeAddSlide(opts, els);
		return opts;
	}
	function saveOriginalOpts(opts) {
		opts.original = {
			before : [],
			after : []
		};
		opts.original.cssBefore = $bn2.extend( {}, opts.cssBefore);
		opts.original.cssAfter = $bn2.extend( {}, opts.cssAfter);
		opts.original.animIn = $bn2.extend( {}, opts.animIn);
		opts.original.animOut = $bn2.extend( {}, opts.animOut);
		$bn2.each(opts.before, function() {
			opts.original.before.push(this);
		});
		$bn2.each(opts.after, function() {
			opts.original.after.push(this);
		});
	}
	function supportMultiTransitions(opts) {
		var i, tx, txs = $bn2.fn.cycle.transitions;
		if (opts.fx.indexOf(",") > 0) {
			opts.multiFx = true;
			opts.fxs = opts.fx.replace(/\s*/g, "").split(",");
			for (i = 0; i < opts.fxs.length; i++) {
				var fx = opts.fxs[i];
				tx = txs[fx];
				if (!tx || !txs.hasOwnProperty(fx) || !$bn2.isFunction(tx)) {
					log("discarding unknown transition: ", fx);
					opts.fxs.splice(i, 1);
					i--;
				}
			}
			if (!opts.fxs.length) {
				log("No valid transitions named; slideshow terminating.");
				return false;
			}
		} else {
			if (opts.fx == "all") {
				opts.multiFx = true;
				opts.fxs = [];
				for (p in txs) {
					tx = txs[p];
					if (txs.hasOwnProperty(p) && $bn2.isFunction(tx)) {
						opts.fxs.push(p);
					}
				}
			}
		}
		if (opts.multiFx && opts.randomizeEffects) {
			var r1 = Math.floor(Math.random() * 20) + 30;
			for (i = 0; i < r1; i++) {
				var r2 = Math.floor(Math.random() * opts.fxs.length);
				opts.fxs.push(opts.fxs.splice(r2, 1)[0]);
			}
			debug("randomized fx sequence: ", opts.fxs);
		}
		return true;
	}
	function exposeAddSlide(opts, els) {
		opts.addSlide = function(newSlide, prepend) {
			var $bn2s = $bn2(newSlide), s = $bn2s[0];
			if (!opts.autostopCount) {
				opts.countdown++;
			}
			els[prepend ? "unshift" : "push"](s);
			if (opts.els) {
				opts.els[prepend ? "unshift" : "push"](s);
			}
			opts.slideCount = els.length;
			$bn2s.css("position", "absolute");
			$bn2s[prepend ? "prependTo" : "appendTo"](opts.$bn2cont);
			if (prepend) {
				opts.currSlide++;
				opts.nextSlide++;
			}
			if (!$bn2.support.opacity && opts.cleartype && !opts.cleartypeNoBg) {
				clearTypeFix($bn2s);
			}
			if (opts.fit && opts.width) {
				$bn2s.width(opts.width);
			}
			if (opts.fit && opts.height && opts.height != "auto") {
				$bn2slides.height(opts.height);
			}
			s.cycleH = (opts.fit && opts.height) ? opts.height : $bn2s.height();
			s.cycleW = (opts.fit && opts.width) ? opts.width : $bn2s.width();
			$bn2s.css(opts.cssBefore);
			if (opts.pager || opts.pagerAnchorBuilder) {
				$bn2.fn.cycle.createPagerAnchor(els.length - 1, s,
						$bn2(opts.pager), els, opts);
			}
			if ($bn2.isFunction(opts.onAddSlide)) {
				opts.onAddSlide($bn2s);
			} else {
				$bn2s.hide();
			}
		};
	}
	$bn2.fn.cycle.resetState = function(opts, fx) {
		fx = fx || opts.fx;
		opts.before = [];
		opts.after = [];
		opts.cssBefore = $bn2.extend( {}, opts.original.cssBefore);
		opts.cssAfter = $bn2.extend( {}, opts.original.cssAfter);
		opts.animIn = $bn2.extend( {}, opts.original.animIn);
		opts.animOut = $bn2.extend( {}, opts.original.animOut);
		opts.fxFn = null;
		$bn2.each(opts.original.before, function() {
			opts.before.push(this);
		});
		$bn2.each(opts.original.after, function() {
			opts.after.push(this);
		});
		var init = $bn2.fn.cycle.transitions[fx];
		if ($bn2.isFunction(init)) {
			init(opts.$bn2cont, $bn2(opts.elements), opts);
		}
	};
	function go(els, opts, manual, fwd) {
		if (manual && opts.busy && opts.manualTrump) {
			debug("manualTrump in go(), stopping active transition");
			$bn2(els).stop(true, true);
			opts.busy = false;
		}
		if (opts.busy) {
			debug("transition active, ignoring new tx request");
			return;
		}
		var p = opts.$bn2cont[0], curr = els[opts.currSlide], next = els[opts.nextSlide];
		if (p.cycleStop != opts.stopCount || p.cycleTimeout === 0 && !manual) {
			return;
		}
		if (!manual
				&& !p.cyclePause
				&& ((opts.autostop && (--opts.countdown <= 0)) || (opts.nowrap
						&& !opts.random && opts.nextSlide < opts.currSlide))) {
			if (opts.end) {
				opts.end(opts);
			}
			return;
		}
		var changed = false;
		if ((manual || !p.cyclePause) && (opts.nextSlide != opts.currSlide)) {
			changed = true;
			var fx = opts.fx;
			curr.cycleH = curr.cycleH || $bn2(curr).height();
			curr.cycleW = curr.cycleW || $bn2(curr).width();
			next.cycleH = next.cycleH || $bn2(next).height();
			next.cycleW = next.cycleW || $bn2(next).width();
			if (opts.multiFx) {
				if (opts.lastFx == undefined
						|| ++opts.lastFx >= opts.fxs.length) {
					opts.lastFx = 0;
				}
				fx = opts.fxs[opts.lastFx];
				opts.currFx = fx;
			}
			if (opts.oneTimeFx) {
				fx = opts.oneTimeFx;
				opts.oneTimeFx = null;
			}
			$bn2.fn.cycle.resetState(opts, fx);
			if (opts.before.length) {
				$bn2.each(opts.before, function(i, o) {
					if (p.cycleStop != opts.stopCount) {
						return;
					}
					o.apply(next, [ curr, next, opts, fwd ]);
				});
			}
			var after = function() {
				$bn2.each(opts.after, function(i, o) {
					if (p.cycleStop != opts.stopCount) {
						return;
					}
					o.apply(next, [ curr, next, opts, fwd ]);
				});
			};
			debug("tx firing; currSlide: " + opts.currSlide + "; nextSlide: "
					+ opts.nextSlide);
			opts.busy = 1;
			if (opts.fxFn) {
				opts.fxFn(curr, next, opts, after, fwd, manual
						&& opts.fastOnEvent);
			} else {
				if ($bn2.isFunction($bn2.fn.cycle[opts.fx])) {
					$bn2.fn.cycle[opts.fx](curr, next, opts, after, fwd, manual
							&& opts.fastOnEvent);
				} else {
					$bn2.fn.cycle.custom(curr, next, opts, after, fwd, manual
							&& opts.fastOnEvent);
				}
			}
		}
		if (changed || opts.nextSlide == opts.currSlide) {
			opts.lastSlide = opts.currSlide;
			if (opts.random) {
				opts.currSlide = opts.nextSlide;
				if (++opts.randomIndex == els.length) {
					opts.randomIndex = 0;
				}
				opts.nextSlide = opts.randomMap[opts.randomIndex];
				if (opts.nextSlide == opts.currSlide) {
					opts.nextSlide = (opts.currSlide == opts.slideCount - 1) ? 0
							: opts.currSlide + 1;
				}
			} else {
				var roll = (opts.nextSlide + 1) == els.length;
				opts.nextSlide = roll ? 0 : opts.nextSlide + 1;
				opts.currSlide = roll ? els.length - 1 : opts.nextSlide - 1;
			}
		}
		if (changed && opts.pager) {
			opts.updateActivePagerLink(opts.pager, opts.currSlide,
					opts.activePagerClass);
		}
		var ms = 0;
		if (opts.timeout && !opts.continuous) {
			ms = getTimeout(curr, next, opts, fwd);
		} else {
			if (opts.continuous && p.cyclePause) {
				ms = 10;
			}
		}
		if (ms > 0) {
			p.cycleTimeout = setTimeout(function() {
				go(els, opts, 0, !opts.rev);
			}, ms);
		}
	}
	$bn2.fn.cycle.updateActivePagerLink = function(pager, currSlide, clsName) {
		$bn2(pager).each(
				function() {
					$bn2(this).children().removeClass(clsName).eq(currSlide)
							.addClass(clsName);
				});
	};
	function getTimeout(curr, next, opts, fwd) {
		if (opts.timeoutFn) {
			var t = opts.timeoutFn(curr, next, opts, fwd);
			while ((t - opts.speed) < 250) {
				t += opts.speed;
			}
			debug("calculated timeout: " + t + "; speed: " + opts.speed);
			if (t !== false) {
				return t;
			}
		}
		return opts.timeout;
	}
	$bn2.fn.cycle.next = function(opts) {
		advance(opts, opts.rev ? -1 : 1);
	};
	$bn2.fn.cycle.prev = function(opts) {
		advance(opts, opts.rev ? 1 : -1);
	};
	function advance(opts, val) {
		var els = opts.elements;
		var p = opts.$bn2cont[0], timeout = p.cycleTimeout;
		if (timeout) {
			clearTimeout(timeout);
			p.cycleTimeout = 0;
		}
		if (opts.random && val < 0) {
			opts.randomIndex--;
			if (--opts.randomIndex == -2) {
				opts.randomIndex = els.length - 2;
			} else {
				if (opts.randomIndex == -1) {
					opts.randomIndex = els.length - 1;
				}
			}
			opts.nextSlide = opts.randomMap[opts.randomIndex];
		} else {
			if (opts.random) {
				opts.nextSlide = opts.randomMap[opts.randomIndex];
			} else {
				opts.nextSlide = opts.currSlide + val;
				if (opts.nextSlide < 0) {
					if (opts.nowrap) {
						return false;
					}
					opts.nextSlide = els.length - 1;
				} else {
					if (opts.nextSlide >= els.length) {
						if (opts.nowrap) {
							return false;
						}
						opts.nextSlide = 0;
					}
				}
			}
		}
		var cb = opts.onPrevNextEvent || opts.prevNextClick;
		if ($bn2.isFunction(cb)) {
			cb(val > 0, opts.nextSlide, els[opts.nextSlide]);
		}
		go(els, opts, 1, val >= 0);
		return false;
	}
	function buildPager(els, opts) {
		var $bn2p = $bn2(opts.pager);
		$bn2.each(els, function(i, o) {
			$bn2.fn.cycle.createPagerAnchor(i, o, $bn2p, els, opts);
		});
		opts.updateActivePagerLink(opts.pager, opts.startingSlide,
				opts.activePagerClass);
	}
	$bn2.fn.cycle.createPagerAnchor = function(i, el, $bn2p, els, opts) {
		var a;
		if ($bn2.isFunction(opts.pagerAnchorBuilder)) {
			a = opts.pagerAnchorBuilder(i, el);
			debug("pagerAnchorBuilder(" + i + ", el) returned: " + a);
		} else {
			a = '<a href="#">' + (i + 1) + "</a>";
		}
		if (!a) {
			return;
		}
		var $bn2a = $bn2(a);
		if ($bn2a.parents("body").length === 0) {
			var arr = [];
			if ($bn2p.length > 1) {
				$bn2p.each(function() {
					var $bn2clone = $bn2a.clone(true);
					$bn2(this).append($bn2clone);
					arr.push($bn2clone[0]);
				});
				$bn2a = $bn2(arr);
			} else {
				$bn2a.appendTo($bn2p);
			}
		}
		opts.pagerAnchors = opts.pagerAnchors || [];
		opts.pagerAnchors.push($bn2a);
		$bn2a.bind(opts.pagerEvent, function(e) {
			e.preventDefault();
			opts.nextSlide = i;
			var p = opts.$bn2cont[0], timeout = p.cycleTimeout;
			if (timeout) {
				clearTimeout(timeout);
				p.cycleTimeout = 0;
			}
			var cb = opts.onPagerEvent || opts.pagerClick;
			if ($bn2.isFunction(cb)) {
				cb(opts.nextSlide, els[opts.nextSlide]);
			}
			go(els, opts, 1, opts.currSlide < i);
		});
		if (!/^click/.test(opts.pagerEvent) && !opts.allowPagerClickBubble) {
			$bn2a.bind("click.cycle", function() {
				return false;
			});
		}
		if (opts.pauseOnPagerHover) {
			$bn2a.hover(function() {
				opts.$bn2cont[0].cyclePause++;
			}, function() {
				opts.$bn2cont[0].cyclePause--;
			});
		}
	};
	$bn2.fn.cycle.hopsFromLast = function(opts, fwd) {
		var hops, l = opts.lastSlide, c = opts.currSlide;
		if (fwd) {
			hops = c > l ? c - l : opts.slideCount - l;
		} else {
			hops = c < l ? l - c : l + opts.slideCount - c;
		}
		return hops;
	};
	function clearTypeFix($bn2slides) {
		debug("applying clearType background-color hack");
		function hex(s) {
			s = parseInt(s).toString(16);
			return s.length < 2 ? "0" + s : s;
		}
		function getBg(e) {
			for (; e && e.nodeName.toLowerCase() != "html"; e = e.parentNode) {
				var v = $bn2.css(e, "background-color");
				if (v.indexOf("rgb") >= 0) {
					var rgb = v.match(/\d+/g);
					return "#" + hex(rgb[0]) + hex(rgb[1]) + hex(rgb[2]);
				}
				if (v && v != "transparent") {
					return v;
				}
			}
			return "#ffffff";
		}
		$bn2slides.each(function() {
			$bn2(this).css("background-color", getBg(this));
		});
	}
	$bn2.fn.cycle.commonReset = function(curr, next, opts, w, h, rev) {
		$bn2(opts.elements).not(curr).hide();
		opts.cssBefore.opacity = 1;
		opts.cssBefore.display = "block";
		if (w !== false && next.cycleW > 0) {
			opts.cssBefore.width = next.cycleW;
		}
		if (h !== false && next.cycleH > 0) {
			opts.cssBefore.height = next.cycleH;
		}
		opts.cssAfter = opts.cssAfter || {};
		opts.cssAfter.display = "none";
		$bn2(curr).css("zIndex", opts.slideCount + (rev === true ? 1 : 0));
		$bn2(next).css("zIndex", opts.slideCount + (rev === true ? 0 : 1));
	};
	$bn2.fn.cycle.custom = function(curr, next, opts, cb, fwd, speedOverride) {
		var $bn2l = $bn2(curr), $bn2n = $bn2(next);
		var speedIn = opts.speedIn, speedOut = opts.speedOut, easeIn = opts.easeIn, easeOut = opts.easeOut;
		$bn2n.css(opts.cssBefore);
		if (speedOverride) {
			if (typeof speedOverride == "number") {
				speedIn = speedOut = speedOverride;
			} else {
				speedIn = speedOut = 1;
			}
			easeIn = easeOut = null;
		}
		var fn = function() {
			$bn2n.animate(opts.animIn, speedIn, easeIn, cb);
		};
		$bn2l.animate(opts.animOut, speedOut, easeOut, function() {
			if (opts.cssAfter) {
				$bn2l.css(opts.cssAfter);
			}
			if (!opts.sync) {
				fn();
			}
		});
		if (opts.sync) {
			fn();
		}
	};
	$bn2.fn.cycle.transitions = {
		fade : function($bn2cont, $bn2slides, opts) {
			$bn2slides.not(":eq(" + opts.currSlide + ")").css("opacity", 0);
			opts.before.push(function(curr, next, opts) {
				$bn2.fn.cycle.commonReset(curr, next, opts);
				opts.cssBefore.opacity = 0;
			});
			opts.animIn = {
				opacity : 1
			};
			opts.animOut = {
				opacity : 0
			};
			opts.cssBefore = {
				top : 0,
				left : 0
			};
		}
	};
	$bn2.fn.cycle.ver = function() {
		return ver;
	};
	$bn2.fn.cycle.defaults = {
		fx : "fade",
		timeout : 4000,
		timeoutFn : null,
		continuous : 0,
		speed : 500,
		speedIn : null,
		speedOut : null,
		next : null,
		prev : null,
		onPrevNextEvent : null,
		prevNextEvent : "click.cycle",
		pager : null,
		onPagerEvent : null,
		pagerEvent : "click.cycle",
		allowPagerClickBubble : false,
		pagerAnchorBuilder : null,
		before : null,
		after : null,
		end : null,
		easing : null,
		easeIn : null,
		easeOut : null,
		shuffle : null,
		animIn : null,
		animOut : null,
		cssBefore : null,
		cssAfter : null,
		fxFn : null,
		height : "auto",
		startingSlide : 0,
		sync : 1,
		random : 0,
		fit : 0,
		containerResize : 1,
		pause : 0,
		pauseOnPagerHover : 0,
		autostop : 0,
		autostopCount : 0,
		delay : 0,
		slideExpr : null,
		cleartype : !$bn2.support.opacity,
		cleartypeNoBg : false,
		nowrap : 0,
		fastOnEvent : 0,
		randomizeEffects : 1,
		rev : 0,
		manualTrump : true,
		requeueOnImageNotLoaded : true,
		requeueTimeout : 250,
		activePagerClass : "activeSlide",
		updateActivePagerLink : null
	};
})(jQuery);
/*
 * jQuery Cycle Plugin Transition Definitions This script is a plugin for the
 * jQuery Cycle Plugin Examples and documentation at:
 * http://malsup.com/jquery/cycle/ Copyright (c) 2007-2008 M. Alsup Version:
 * 2.72 Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 */
(function($bn2) {
	$bn2.fn.cycle.transitions.none = function($bn2cont, $bn2slides, opts) {
		opts.fxFn = function(curr, next, opts, after) {
			$bn2(next).show();
			$bn2(curr).hide();
			after();
		};
	};
	$bn2.fn.cycle.transitions.scrollUp = function($bn2cont, $bn2slides, opts) {
		$bn2cont.css("overflow", "hidden");
		opts.before.push($bn2.fn.cycle.commonReset);
		var h = $bn2cont.height();
		opts.cssBefore = {
			top : h,
			left : 0
		};
		opts.cssFirst = {
			top : 0
		};
		opts.animIn = {
			top : 0
		};
		opts.animOut = {
			top : -h
		};
	};
	$bn2.fn.cycle.transitions.scrollDown = function($bn2cont, $bn2slides, opts) {
		$bn2cont.css("overflow", "hidden");
		opts.before.push($bn2.fn.cycle.commonReset);
		var h = $bn2cont.height();
		opts.cssFirst = {
			top : 0
		};
		opts.cssBefore = {
			top : -h,
			left : 0
		};
		opts.animIn = {
			top : 0
		};
		opts.animOut = {
			top : h
		};
	};
	$bn2.fn.cycle.transitions.scrollLeft = function($bn2cont, $bn2slides, opts) {
		$bn2cont.css("overflow", "hidden");
		opts.before.push($bn2.fn.cycle.commonReset);
		var w = $bn2cont.width();
		opts.cssFirst = {
			left : 0
		};
		opts.cssBefore = {
			left : w,
			top : 0
		};
		opts.animIn = {
			left : 0
		};
		opts.animOut = {
			left : 0 - w
		};
	};
	$bn2.fn.cycle.transitions.scrollRight = function($bn2cont, $bn2slides, opts) {
		$bn2cont.css("overflow", "hidden");
		opts.before.push($bn2.fn.cycle.commonReset);
		var w = $bn2cont.width();
		opts.cssFirst = {
			left : 0
		};
		opts.cssBefore = {
			left : -w,
			top : 0
		};
		opts.animIn = {
			left : 0
		};
		opts.animOut = {
			left : w
		};
	};
	$bn2.fn.cycle.transitions.scrollHorz = function($bn2cont, $bn2slides, opts) {
		$bn2cont.css("overflow", "hidden").width();
		opts.before.push(function(curr, next, opts, fwd) {
			$bn2.fn.cycle.commonReset(curr, next, opts);
			opts.cssBefore.left = fwd ? (next.cycleW - 1) : (1 - next.cycleW);
			opts.animOut.left = fwd ? -curr.cycleW : curr.cycleW;
		});
		opts.cssFirst = {
			left : 0
		};
		opts.cssBefore = {
			top : 0
		};
		opts.animIn = {
			left : 0
		};
		opts.animOut = {
			top : 0
		};
	};
	$bn2.fn.cycle.transitions.scrollVert = function($bn2cont, $bn2slides, opts) {
		$bn2cont.css("overflow", "hidden");
		opts.before.push(function(curr, next, opts, fwd) {
			$bn2.fn.cycle.commonReset(curr, next, opts);
			opts.cssBefore.top = fwd ? (1 - next.cycleH) : (next.cycleH - 1);
			opts.animOut.top = fwd ? curr.cycleH : -curr.cycleH;
		});
		opts.cssFirst = {
			top : 0
		};
		opts.cssBefore = {
			left : 0
		};
		opts.animIn = {
			top : 0
		};
		opts.animOut = {
			left : 0
		};
	};
	$bn2.fn.cycle.transitions.slideX = function($bn2cont, $bn2slides, opts) {
		opts.before.push(function(curr, next, opts) {
			$bn2(opts.elements).not(curr).hide();
			$bn2.fn.cycle.commonReset(curr, next, opts, false, true);
			opts.animIn.width = next.cycleW;
		});
		opts.cssBefore = {
			left : 0,
			top : 0,
			width : 0
		};
		opts.animIn = {
			width : "show"
		};
		opts.animOut = {
			width : 0
		};
	};
	$bn2.fn.cycle.transitions.slideY = function($bn2cont, $bn2slides, opts) {
		opts.before.push(function(curr, next, opts) {
			$bn2(opts.elements).not(curr).hide();
			$bn2.fn.cycle.commonReset(curr, next, opts, true, false);
			opts.animIn.height = next.cycleH;
		});
		opts.cssBefore = {
			left : 0,
			top : 0,
			height : 0
		};
		opts.animIn = {
			height : "show"
		};
		opts.animOut = {
			height : 0
		};
	};
	$bn2.fn.cycle.transitions.shuffle = function($bn2cont, $bn2slides, opts) {
		var i, w = $bn2cont.css("overflow", "visible").width();
		$bn2slides.css( {
			left : 0,
			top : 0
		});
		opts.before.push(function(curr, next, opts) {
			$bn2.fn.cycle.commonReset(curr, next, opts, true, true, true);
		});
		if (!opts.speedAdjusted) {
			opts.speed = opts.speed / 2;
			opts.speedAdjusted = true;
		}
		opts.random = 0;
		opts.shuffle = opts.shuffle || {
			left : -w,
			top : 15
		};
		opts.els = [];
		for (i = 0; i < $bn2slides.length; i++) {
			opts.els.push($bn2slides[i]);
		}
		for (i = 0; i < opts.currSlide; i++) {
			opts.els.push(opts.els.shift());
		}
		opts.fxFn = function(curr, next, opts, cb, fwd) {
			var $bn2el = fwd ? $bn2(curr) : $bn2(next);
			$bn2(next).css(opts.cssBefore);
			var count = opts.slideCount;
			$bn2el.animate(opts.shuffle, opts.speedIn, opts.easeIn, function() {
				var hops = $bn2.fn.cycle.hopsFromLast(opts, fwd);
				for ( var k = 0; k < hops; k++) {
					fwd ? opts.els.push(opts.els.shift()) : opts.els
							.unshift(opts.els.pop());
				}
				if (fwd) {
					for ( var i = 0, len = opts.els.length; i < len; i++) {
						$bn2(opts.els[i]).css("z-index", len - i + count);
					}
				} else {
					var z = $bn2(curr).css("z-index");
					$bn2el.css("z-index", parseInt(z) + 1 + count);
				}
				$bn2el.animate( {
					left : 0,
					top : 0
				}, opts.speedOut, opts.easeOut, function() {
					$bn2(fwd ? this : curr).hide();
					if (cb) {
						cb();
					}
				});
			});
		};
		opts.cssBefore = {
			display : "block",
			opacity : 1,
			top : 0,
			left : 0
		};
	};
	$bn2.fn.cycle.transitions.turnUp = function($bn2cont, $bn2slides, opts) {
		opts.before.push(function(curr, next, opts) {
			$bn2.fn.cycle.commonReset(curr, next, opts, true, false);
			opts.cssBefore.top = next.cycleH;
			opts.animIn.height = next.cycleH;
		});
		opts.cssFirst = {
			top : 0
		};
		opts.cssBefore = {
			left : 0,
			height : 0
		};
		opts.animIn = {
			top : 0
		};
		opts.animOut = {
			height : 0
		};
	};
	$bn2.fn.cycle.transitions.turnDown = function($bn2cont, $bn2slides, opts) {
		opts.before.push(function(curr, next, opts) {
			$bn2.fn.cycle.commonReset(curr, next, opts, true, false);
			opts.animIn.height = next.cycleH;
			opts.animOut.top = curr.cycleH;
		});
		opts.cssFirst = {
			top : 0
		};
		opts.cssBefore = {
			left : 0,
			top : 0,
			height : 0
		};
		opts.animOut = {
			height : 0
		};
	};
	$bn2.fn.cycle.transitions.turnLeft = function($bn2cont, $bn2slides, opts) {
		opts.before.push(function(curr, next, opts) {
			$bn2.fn.cycle.commonReset(curr, next, opts, false, true);
			opts.cssBefore.left = next.cycleW;
			opts.animIn.width = next.cycleW;
		});
		opts.cssBefore = {
			top : 0,
			width : 0
		};
		opts.animIn = {
			left : 0
		};
		opts.animOut = {
			width : 0
		};
	};
	$bn2.fn.cycle.transitions.turnRight = function($bn2cont, $bn2slides, opts) {
		opts.before.push(function(curr, next, opts) {
			$bn2.fn.cycle.commonReset(curr, next, opts, false, true);
			opts.animIn.width = next.cycleW;
			opts.animOut.left = curr.cycleW;
		});
		opts.cssBefore = {
			top : 0,
			left : 0,
			width : 0
		};
		opts.animIn = {
			left : 0
		};
		opts.animOut = {
			width : 0
		};
	};
	$bn2.fn.cycle.transitions.zoom = function($bn2cont, $bn2slides, opts) {
		opts.before.push(function(curr, next, opts) {
			$bn2.fn.cycle.commonReset(curr, next, opts, false, false, true);
			opts.cssBefore.top = next.cycleH / 2;
			opts.cssBefore.left = next.cycleW / 2;
			opts.animIn = {
				top : 0,
				left : 0,
				width : next.cycleW,
				height : next.cycleH
			};
			opts.animOut = {
				width : 0,
				height : 0,
				top : curr.cycleH / 2,
				left : curr.cycleW / 2
			};
		});
		opts.cssFirst = {
			top : 0,
			left : 0
		};
		opts.cssBefore = {
			width : 0,
			height : 0
		};
	};
	$bn2.fn.cycle.transitions.fadeZoom = function($bn2cont, $bn2slides, opts) {
		opts.before.push(function(curr, next, opts) {
			$bn2.fn.cycle.commonReset(curr, next, opts, false, false);
			opts.cssBefore.left = next.cycleW / 2;
			opts.cssBefore.top = next.cycleH / 2;
			opts.animIn = {
				top : 0,
				left : 0,
				width : next.cycleW,
				height : next.cycleH
			};
		});
		opts.cssBefore = {
			width : 0,
			height : 0
		};
		opts.animOut = {
			opacity : 0
		};
	};
	$bn2.fn.cycle.transitions.blindX = function($bn2cont, $bn2slides, opts) {
		var w = $bn2cont.css("overflow", "hidden").width();
		opts.before.push(function(curr, next, opts) {
			$bn2.fn.cycle.commonReset(curr, next, opts);
			opts.animIn.width = next.cycleW;
			opts.animOut.left = curr.cycleW;
		});
		opts.cssBefore = {
			left : w,
			top : 0
		};
		opts.animIn = {
			left : 0
		};
		opts.animOut = {
			left : w
		};
	};
	$bn2.fn.cycle.transitions.blindY = function($bn2cont, $bn2slides, opts) {
		var h = $bn2cont.css("overflow", "hidden").height();
		opts.before.push(function(curr, next, opts) {
			$bn2.fn.cycle.commonReset(curr, next, opts);
			opts.animIn.height = next.cycleH;
			opts.animOut.top = curr.cycleH;
		});
		opts.cssBefore = {
			top : h,
			left : 0
		};
		opts.animIn = {
			top : 0
		};
		opts.animOut = {
			top : h
		};
	};
	$bn2.fn.cycle.transitions.blindZ = function($bn2cont, $bn2slides, opts) {
		var h = $bn2cont.css("overflow", "hidden").height();
		var w = $bn2cont.width();
		opts.before.push(function(curr, next, opts) {
			$bn2.fn.cycle.commonReset(curr, next, opts);
			opts.animIn.height = next.cycleH;
			opts.animOut.top = curr.cycleH;
		});
		opts.cssBefore = {
			top : h,
			left : w
		};
		opts.animIn = {
			top : 0,
			left : 0
		};
		opts.animOut = {
			top : h,
			left : w
		};
	};
	$bn2.fn.cycle.transitions.growX = function($bn2cont, $bn2slides, opts) {
		opts.before.push(function(curr, next, opts) {
			$bn2.fn.cycle.commonReset(curr, next, opts, false, true);
			opts.cssBefore.left = this.cycleW / 2;
			opts.animIn = {
				left : 0,
				width : this.cycleW
			};
			opts.animOut = {
				left : 0
			};
		});
		opts.cssBefore = {
			width : 0,
			top : 0
		};
	};
	$bn2.fn.cycle.transitions.growY = function($bn2cont, $bn2slides, opts) {
		opts.before.push(function(curr, next, opts) {
			$bn2.fn.cycle.commonReset(curr, next, opts, true, false);
			opts.cssBefore.top = this.cycleH / 2;
			opts.animIn = {
				top : 0,
				height : this.cycleH
			};
			opts.animOut = {
				top : 0
			};
		});
		opts.cssBefore = {
			height : 0,
			left : 0
		};
	};
	$bn2.fn.cycle.transitions.curtainX = function($bn2cont, $bn2slides, opts) {
		opts.before.push(function(curr, next, opts) {
			$bn2.fn.cycle.commonReset(curr, next, opts, false, true, true);
			opts.cssBefore.left = next.cycleW / 2;
			opts.animIn = {
				left : 0,
				width : this.cycleW
			};
			opts.animOut = {
				left : curr.cycleW / 2,
				width : 0
			};
		});
		opts.cssBefore = {
			top : 0,
			width : 0
		};
	};
	$bn2.fn.cycle.transitions.curtainY = function($bn2cont, $bn2slides, opts) {
		opts.before.push(function(curr, next, opts) {
			$bn2.fn.cycle.commonReset(curr, next, opts, true, false, true);
			opts.cssBefore.top = next.cycleH / 2;
			opts.animIn = {
				top : 0,
				height : next.cycleH
			};
			opts.animOut = {
				top : curr.cycleH / 2,
				height : 0
			};
		});
		opts.cssBefore = {
			left : 0,
			height : 0
		};
	};
	$bn2.fn.cycle.transitions.cover = function($bn2cont, $bn2slides, opts) {
		var d = opts.direction || "left";
		var w = $bn2cont.css("overflow", "hidden").width();
		var h = $bn2cont.height();
		opts.before.push(function(curr, next, opts) {
			$bn2.fn.cycle.commonReset(curr, next, opts);
			if (d == "right") {
				opts.cssBefore.left = -w;
			} else {
				if (d == "up") {
					opts.cssBefore.top = h;
				} else {
					if (d == "down") {
						opts.cssBefore.top = -h;
					} else {
						opts.cssBefore.left = w;
					}
				}
			}
		});
		opts.animIn = {
			left : 0,
			top : 0
		};
		opts.animOut = {
			opacity : 1
		};
		opts.cssBefore = {
			top : 0,
			left : 0
		};
	};
	$bn2.fn.cycle.transitions.uncover = function($bn2cont, $bn2slides, opts) {
		var d = opts.direction || "left";
		var w = $bn2cont.css("overflow", "hidden").width();
		var h = $bn2cont.height();
		opts.before.push(function(curr, next, opts) {
			$bn2.fn.cycle.commonReset(curr, next, opts, true, true, true);
			if (d == "right") {
				opts.animOut.left = w;
			} else {
				if (d == "up") {
					opts.animOut.top = -h;
				} else {
					if (d == "down") {
						opts.animOut.top = h;
					} else {
						opts.animOut.left = -w;
					}
				}
			}
		});
		opts.animIn = {
			left : 0,
			top : 0
		};
		opts.animOut = {
			opacity : 1
		};
		opts.cssBefore = {
			top : 0,
			left : 0
		};
	};
	$bn2.fn.cycle.transitions.toss = function($bn2cont, $bn2slides, opts) {
		var w = $bn2cont.css("overflow", "visible").width();
		var h = $bn2cont.height();
		opts.before.push(function(curr, next, opts) {
			$bn2.fn.cycle.commonReset(curr, next, opts, true, true, true);
			if (!opts.animOut.left && !opts.animOut.top) {
				opts.animOut = {
					left : w * 2,
					top : -h / 2,
					opacity : 0
				};
			} else {
				opts.animOut.opacity = 0;
			}
		});
		opts.cssBefore = {
			left : 0,
			top : 0
		};
		opts.animIn = {
			left : 0
		};
	};
	$bn2.fn.cycle.transitions.wipe = function($bn2cont, $bn2slides, opts) {
		var w = $bn2cont.css("overflow", "hidden").width();
		var h = $bn2cont.height();
		opts.cssBefore = opts.cssBefore || {};
		var clip;
		if (opts.clip) {
			if (/l2r/.test(opts.clip)) {
				clip = "rect(0px 0px " + h + "px 0px)";
			} else {
				if (/r2l/.test(opts.clip)) {
					clip = "rect(0px " + w + "px " + h + "px " + w + "px)";
				} else {
					if (/t2b/.test(opts.clip)) {
						clip = "rect(0px " + w + "px 0px 0px)";
					} else {
						if (/b2t/.test(opts.clip)) {
							clip = "rect(" + h + "px " + w + "px " + h
									+ "px 0px)";
						} else {
							if (/zoom/.test(opts.clip)) {
								var top = parseInt(h / 2);
								var left = parseInt(w / 2);
								clip = "rect(" + top + "px " + left + "px "
										+ top + "px " + left + "px)";
							}
						}
					}
				}
			}
		}
		opts.cssBefore.clip = opts.cssBefore.clip || clip
				|| "rect(0px 0px 0px 0px)";
		var d = opts.cssBefore.clip.match(/(\d+)/g);
		var t = parseInt(d[0]), r = parseInt(d[1]), b = parseInt(d[2]), l = parseInt(d[3]);
		opts.before.push(function(curr, next, opts) {
			if (curr == next) {
				return;
			}
			var $bn2curr = $bn2(curr), $bn2next = $bn2(next);
			$bn2.fn.cycle.commonReset(curr, next, opts, true, true, false);
			opts.cssAfter.display = "block";
			var step = 1, count = parseInt((opts.speedIn / 13)) - 1;
			(function f() {
				var tt = t ? t - parseInt(step * (t / count)) : 0;
				var ll = l ? l - parseInt(step * (l / count)) : 0;
				var bb = b < h ? b + parseInt(step * ((h - b) / count || 1))
						: h;
				var rr = r < w ? r + parseInt(step * ((w - r) / count || 1))
						: w;
				$bn2next.css( {
					clip : "rect(" + tt + "px " + rr + "px " + bb + "px " + ll
							+ "px)"
				});
				(step++ <= count) ? setTimeout(f, 13) : $bn2curr.css("display",
						"none");
			})();
		});
		opts.cssBefore = {
			display : "block",
			opacity : 1,
			top : 0,
			left : 0
		};
		opts.animIn = {
			left : 0
		};
		opts.animOut = {
			left : 0
		};
	};
})(jQuery);