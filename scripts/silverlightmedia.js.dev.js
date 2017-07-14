//----------------------------------------------------------
// Copyright (C) Microsoft Corporation. All rights reserved.
//----------------------------------------------------------
// SilverlightMedia.js
Type.registerNamespace("Sys.UI.Silverlight");
Sys.UI.Silverlight._DomElement = function (a, b) {
	this._element = a;
	this._visible = !!b;
	this._bindAutoAnimations(a, a.Name)
};
Sys.UI.Silverlight._DomElement.prototype = {
	_events: null,
	_animations: null,
	_enabled: true,
	_mouseOver: false,
	get_element: function () {
		return this._element
	},
	get_enabled: function () {
		return this._enabled
	},
	set_enabled: function (a) {
		if (a !== this.get_enabled()) {
			this._enabled = a;
			this._play(a ? "enable" : "disable");
			if (!a && this._mouseOver) {
				this._play("leave");
				this._mouseOver = false
			}
		}
	},
	get_visible: function () {
		return this._visible
	},
	set_visible: function (a) {
		if (a !== this.get_visible()) {
			this._visible = a;
			if (!this._play(a ? "show" : "hide")) this.get_element().visibility = a ? 0 : 1
		}
	},
	_bindAutoAnimations: function (b, a) {
		this._animations = {
			"show": b.findName(a + "_Show"),
			"hide": b.findName(a + "_Hide"),
			"enable": b.findName(a + "_Enable"),
			"disable": b.findName(a + "_Disable"),
			"leave": b.findName(a + "_MouseLeave")
		};
		if (this._animations["leave"]) {
			this.bindEvent("mouseEnter", a + "_MouseEnter", this._onEnter);
			this.bindEvent("mouseLeave", a + "_MouseLeave", this._onLeave)
		}
	},
	bindEvent: function (d, c, a, g) {
		var e = this.get_element(),
		b = null;
		if (c) b = e.findName(c);
		if (!b && !a) return;
		if (a) a = Function.createDelegate(g || this, a);
		var f = this._createEventHandler(b, a),
		h = e.addEventListener(d, f);
		if (!this._events) this._events = [];
		this._events[this._events.length] = {
			eventName: d,
			token: h,
			handler: f
		}
	},
	_createEventHandler: function (a, b) {
		return Function.createDelegate(this, function (c, d) {
			if (!this.get_enabled()) return;
			if (b && !b(c, d)) return;
			if (a) a.begin()
		})
	},
	dispose: function () {
		if (this._events) {
			var c = this.get_element();
			for (var a = 0, d = this._events.length; a < d; a++) {
				var b = this._events[a];
				c.removeEventListener(b.eventName, b.token)
			}
			this._events = null
		}
		this._animations = null;
		this._element = null
	},
	_onEnter: function () {
		this._mouseOver = true;
		return true
	},
	_onLeave: function () {
		this._mouseOver = false;
		return true
	},
	_play: function (b) {
		var a = this._animations[b];
		if (a) {
			a.begin();
			return true
		}
		return false
	}
};
Sys.UI.Silverlight._DomElement.registerClass("Sys.UI.Silverlight._DomElement", null, Sys.IDisposable);
Sys.UI.Silverlight._Button = function (b, i, h, f, d, e, c) {
	Sys.UI.Silverlight._Button.initializeBase(this, [b, i]);
	b.cursor = "Hand";
	this._repeatInterval = h;
	this._clickDelegate = f ? Function.createDelegate(e, f) : null;
	this._doubleClickDelegate = d ? Function.createDelegate(e, d) : null;
	this._elements = [];
	if (c) for (var a = 0; a < c.length; a++) {
		var g = b.findName(c[a]);
		this._elements[a] = g ? new Sys.UI.Silverlight._DomElement(g, a === 0) : null
	}
};
Sys.UI.Silverlight._Button.prototype = {
	_down: false,
	_last: 0,
	_state: 0,
	_repeatTimeout: null,
	_repeatClickDelegate: null,
	set_enabled: function (a) {
		Sys.UI.Silverlight._Button.callBaseMethod(this, "set_enabled", [a]);
		this.get_element().cursor = a ? "Hand" : "Default"
	},
	get_state: function () {
		return this._state
	},
	set_state: function (b) {
		if (b === this.get_state()) return;
		var a = this._elements[this._state];
		if (a) a.set_visible(false);
		this._state = b;
		a = this._elements[this._state];
		if (a) a.set_visible(true)
	},
	_bindAutoAnimations: function (b, a) {
		Sys.UI.Silverlight._Button.callBaseMethod(this, "_bindAutoAnimations", [b, a]);
		this.bindEvent("mouseLeftButtonDown", a + "_MouseDown", this._mouseDown);
		this.bindEvent("mouseLeftButtonUp", a + "_MouseUp", this._mouseUp);
		this.bindEvent("mouseLeave", a + "_MouseUp", this._mouseLeave)
	},
	_cancelRepeat: function () {
		window.clearTimeout(this._repeatTimeout);
		this._repeatTimeout = null
	},
	dispose: function () {
		this._cancelRepeat();
		if (this._elements) {
			for (var a = 0, c = this._elements.length; a < c; a++) {
				var b = this._elements[a];
				if (b) b.dispose()
			}
			this._elements = null
		}
		Sys.UI.Silverlight._Button.callBaseMethod(this, "dispose")
	},
	_doClick: function (a) {
		if (a && this._doubleClickDelegate) this._doubleClickDelegate(this);
		else if (this._clickDelegate) this._clickDelegate(this)
	},
	_mouseDown: function () {
		this._down = true;
		if (this._repeatInterval && !this._repeatTimeout) {
			this._doClick(false);
			this._repeatClickDelegate = Function.createDelegate(this, this._repeatClick);
			this._repeatTimeout = window.setTimeout(this._repeatClickDelegate, 500)
		}
		return true
	},
	_mouseLeave: function () {
		if (!this._down) return false;
		this._down = false;
		this._cancelRepeat();
		return true
	},
	_mouseUp: function () {
		if (!this._down) return false;
		this._down = false;
		if (this._repeatTimeout) this._cancelRepeat();
		else {
			var b = this._last;
			this._last = new Date;
			var a = b && this._last - b < 300;
			if (a) this._last = 0;
			this._doClick(a)
		}
		return true
	},
	_repeatClick: function () {
		this._repeatTimeout = window.setTimeout(this._repeatClickDelegate, this._repeatInterval);
		this._doClick(false)
	}
};
Sys.UI.Silverlight._Button.registerClass("Sys.UI.Silverlight._Button", Sys.UI.Silverlight._DomElement);
Sys.UI.Silverlight._Slider = function (a, d, f, e, b) {
	this._horizontal = a.width >= a.height;
	var c = a.findName(d);
	Sys.UI.Silverlight._Slider.initializeBase(this, [a, f]);
	this._changedHandler = b ? Function.createDelegate(b, e) : null;
	a.cursor = "Hand";
	c.cursor = "Hand";
	this._thumb = new Sys.UI.Silverlight._DomElement(c, true);
	this._thumb.bindEvent("mouseLeftButtonDown", null, this._thumbDown, this);
	this._thumb.bindEvent("mouseLeftButtonUp", null, this._thumbUp, this);
	this._thumb.bindEvent("mouseMove", null, this._thumbMove, this);
	this.bindEvent("mouseLeftButtonDown", null, this._sliderDown);
	var g = a.getHost().content.root;
	this._rootToken = g.addEventListener("mouseLeave", Function.createDelegate(this, this._thumbUp))
};
Sys.UI.Silverlight._Slider.prototype = {
	_readOnly: false,
	_dragging: false,
	_last: null,
	set_enabled: function (a) {
		if (this.get_enabled() !== a) {
			Sys.UI.Silverlight._Slider.callBaseMethod(this, "set_enabled", [a]);
			if (!a) this.set_value(0);
			this.get_element().cursor = a ? "Hand" : "Default";
			if (this._highlight) this._highlight.set_visible(a);
			this._thumb.set_visible(a);
			this._thumb.get_element().cursor = a ? "Hand" : "Default"
		}
	},
	get_readOnly: function () {
		return this._readOnly
	},
	set_readOnly: function (a) {
		if (a !== this._readOnly) {
			this._readOnly = a;
			this._stopDragging()
		}
	},
	get_value: function () {
		var a, c = this._thumb.get_element(),
		b = this.get_element();
		if (this._horizontal) a = (c["Canvas.Left"] - b["Canvas.Left"]) / (b.width - c.width);
		else {
			a = c["Canvas.Top"] - b["Canvas.Top"];
			a = 1 - a / (b.height - c.height)
		}
		a = Math.round(a * 1000) / 1000;
		return Math.min(1, Math.max(0, a))
	},
	set_value: function (a) {
		this._last = null;
		if (!this._dragging) {
			a = Math.max(0, Math.min(1, a));
			this._setThumbPosition(a)
		}
	},
	_bindAutoAnimations: function (b, c) {
		Sys.UI.Silverlight._Slider.callBaseMethod(this, "_bindAutoAnimations", [b, c]);
		var a = b.findName(c + "_Highlight");
		if (a) {
			a[this._horizontal ? "width" : "height"] = 0;
			this._highlight = new Sys.UI.Silverlight._DomElement(a, true)
		} else this._highlight = null
	},
	_detectChanged: function (a) {
		if (a !== this._last && this._changedHandler) {
			this._last = a;
			this._changedHandler(this)
		}
	},
	dispose: function () {
		if (this._thumb) {
			this._thumb.dispose();
			this._thumb = null
		}
		if (this._highlight) {
			this._highlight.dispose();
			this._highlight = null
		}
		if (this._rootToken !== null) {
			this.get_element().getHost().content.root.removeEventListener("mouseLeave", this._rootToken);
			this._rootToken = null
		}
		Sys.UI.Silverlight._Slider.callBaseMethod(this, "dispose")
	},
	_setThumbPosition: function (e) {
		var b = this._toLocation(e),
		a = this._thumb.get_element(),
		d = this.get_element(),
		c = this._highlight ? this._highlight.get_element() : null;
		if (this._horizontal) {
			a["Canvas.Left"] = b + d["Canvas.Left"] - a.width / 2;
			if (c) c.width = b
		} else {
			a["Canvas.Top"] = b + d["Canvas.Top"] - a.height / 2;
			if (c) {
				c["Canvas.Top"] = d["Canvas.Top"] + b;
				c.height = d.height - b + a.height / 2
			}
		}
	},
	_sliderDown: function (c, b) {
		if (this._readOnly) return false;
		var a = this._toValue(b.getPosition(c));
		this._setThumbPosition(a);
		this._detectChanged(a);
		this._startDragging();
		return true
	},
	_startDragging: function () {
		this._dragging = true;
		this._thumb.get_element().CaptureMouse()
	},
	_stopDragging: function () {
		if (this._dragging) {
			this._thumb.get_element().ReleaseMouseCapture();
			this._dragging = false
		}
	},
	_thumbDown: function () {
		if (this._readOnly) return false;
		this._startDragging();
		return true
	},
	_thumbUp: function () {
		if (this._readOnly) return false;
		if (this._dragging) this._detectChanged(this.get_value());
		this._stopDragging();
		return true
	},
	_thumbMove: function (b, a) {
		if (this._dragging) this._setThumbPosition(this._toValue(a.getPosition(this.get_element())));
		return true
	},
	_toLocation: function (c) {
		c = Math.min(1, Math.max(0, c));
		var b = this._thumb.get_element(),
		d = this.get_element(),
		a;
		if (this._horizontal) {
			a = d.width - b.width;
			return b.width / 2 + c * a
		} else {
			a = d.height - b.height;
			return b.height / 2 + (1 - c) * a
		}
	},
	_toValue: function (d) {
		var a, b = this._thumb.get_element(),
		c = this.get_element();
		if (this._horizontal) a = (d.X - b.width / 2) / (c.width - b.width);
		else {
			a = (d.Y - b.height / 2) / (c.height - b.height);
			a = 1 - a
		}
		a = Math.round(a * 1000) / 1000;
		return Math.min(1, Math.max(0, a))
	}
};
Sys.UI.Silverlight._Slider.registerClass("Sys.UI.Silverlight._Slider", Sys.UI.Silverlight._DomElement);
Sys.UI.Silverlight._TextBlock = function (b, a, c) {
	Sys.UI.Silverlight._TextBlock.initializeBase(this, [b, c]);
	if (a) {
		this._bg = new Sys.UI.Silverlight._DomElement(a, c);
		this._centerX = a["Canvas.Left"] + a.width / 2;
		this._bottomY = a["Canvas.Top"] + a.height
	} else {
		this._bg = null;
		this._centerX = b["Canvas.Left"] + b.ActualWidth / 2;
		this._bottomY = b["Canvas.Top"] + b.ActualHeight
	}
};
Sys.UI.Silverlight._TextBlock.prototype = {
	get_text: function () {
		return this.get_element().Text || ""
	},
	set_text: function (c) {
		var b = this.get_element();
		b.Text = c || "";
		this.set_visible( !! c);
		var a = this._bg ? this._bg.get_element() : b;
		a.width = b.ActualWidth;
		a.height = b.ActualHeight;
		a["Canvas.Left"] = this._centerX - a.width / 2;
		a["Canvas.Top"] = this._bottomY - a.height
	},
	set_visible: function (a) {
		Sys.UI.Silverlight._TextBlock.callBaseMethod(this, "set_visible", [a]);
		if (this._bg) this._bg.set_visible(a)
	},
	dispose: function () {
		Sys.UI.Silverlight._TextBlock.callBaseMethod(this, "dispose");
		if (this._bg) this._bg.dispose()
	}
};
Sys.UI.Silverlight._TextBlock.registerClass("Sys.UI.Silverlight._TextBlock", Sys.UI.Silverlight._DomElement);
Sys.UI.Silverlight._ProgressBar = function (a, b, c) {
	Sys.UI.Silverlight._ProgressBar.initializeBase(this, [a, c]);
	this._fullWidth = a.width;
	a.width = 0;
	if (b) {
		this._text = new Sys.UI.Silverlight._TextBlock(b, null, c);
		this._text.set_text("")
	} else this._text = null
};
Sys.UI.Silverlight._ProgressBar.prototype = {
	get_value: function () {
		var a = this._fullWidth !== 0 ? this.get_element().width / this._fullWidth : 0;
		return Math.round(a * 1000) / 1000
	},
	set_value: function (a) {
		this.get_element().width = this._fullWidth * a;
		if (this._text) this._text.set_text("" + Math.floor(a * 100))
	},
	set_visible: function (a) {
		Sys.UI.Silverlight._ProgressBar.callBaseMethod(this, "set_visible", [a]);
		if (this._text) this._text.set_visible(a)
	},
	dispose: function () {
		Sys.UI.Silverlight._ProgressBar.callBaseMethod(this, "dispose");
		if (this._text) this._text.dispose()
	}
};
Sys.UI.Silverlight._ProgressBar.registerClass("Sys.UI.Silverlight._ProgressBar", Sys.UI.Silverlight._DomElement);
Sys.UI.Silverlight._ImageList = function (a, b, c, d, e) {
	this._horizontal = a && a.width >= a.height;
	this._reference = this._horizontal ? "Canvas.Left" : "Canvas.Top";
	Sys.UI.Silverlight._ImageList.initializeBase(this, [a, c]);
	this._toggle = b ? new Sys.UI.Silverlight._Button(b, c, 0, this._onToggle, null, this) : null;
	this._itemClickDelegate = Function.createDelegate(e, d);
	this._virtualItems = [];
	this._imageItems = []
};
Sys.UI.Silverlight._ImageList.prototype = {
	_next: null,
	_previous: null,
	_scrollAnimation: null,
	_scrollStoryboard: null,
	_itemSize: 0,
	_itemSpacing: 0,
	_canActivate: true,
	_active: false,
	_scrollOffset: 0,
	_overflowIndex: 0,
	get_active: function () {
		return this._active
	},
	set_active: function (a) {
		if (a !== this.get_active()) {
			this._active = a;
			this.get_element().visibility = a ? 0 : 1;
			if (this._toggle) this._toggle.set_visible(a);
			if (a) {
				if (!this._toggle) this.get_element().IsHitTestVisible = true
			} else this.set_visible(false)
		}
	},
	get_canActivate: function () {
		return this._canActivate
	},
	set_canActivate: function (a) {
		if (a !== this.get_canActivate()) {
			this._canActivate = a;
			this._ensureActivation()
		}
	},
	get_items: function () {
		return this._virtualItems
	},
	set_items: function (a) {
		this._virtualItems = a || [];
		this._imageItems = [];
		if (a) for (var b = 0, c = a.length; b < c; b++) if (a[b].get_thumbnailSource()) this._imageItems[this._imageItems.length] = b;
		this._ensureActivation()
	},
	set_visible: function (a) {
		Sys.UI.Silverlight._ImageList.callBaseMethod(this, "set_visible", [a]);
		this.get_element().IsHitTestVisible = a
	},
	_assignImages: function () {
		for (var b = 0, f = this._items.length; b < f; b++) {
			var a = this._items[b],
			c = this._scrollOffset + b;
			if (c < this._imageItems.length) {
				var e = a.image.get_element(),
				d = this._virtualItems[this._imageItems[c]];
				e.source = null;
				e.source = d.get_thumbnailSource();
				a.button.set_visible(true);
				a.button._imageIndex = c;
				if (a.title) a.title.set_text(d.get_title())
			} else {
				a.button.set_visible(false);
				a.button._imageIndex = null
			}
		}
	},
	_bindAutoAnimations: function (a, b) {
		Sys.UI.Silverlight._ImageList.callBaseMethod(this, "_bindAutoAnimations", [a, b]);
		var g = a.findName(b + "_ScrollAnimationStoryboard"),
		c = a.findName(b + "_ScrollAnimation");
		if (g && c) {
			this._scrollStoryboard = g;
			this._scrollAnimation = c;
			var d = c.duration.seconds * 1000,
			e = a.findName(b + "_ScrollNext"),
			f = a.findName(b + "_ScrollPrevious");
			if (e && f) {
				this._next = new Sys.UI.Silverlight._Button(e, true, d, this._scrollNext, null, this);
				this._previous = new Sys.UI.Silverlight._Button(f, true, d, this._scrollPrevious, null, this)
			}
		}
		this._bindItems(a, b)
	},
	_bindItems: function (f, e) {
		this._items = [];
		var d, c, g, title;
		for (var a = 1; d = f.findName(e + "_ScrollItem" + a), (c = f.findName(e + "_ScrollItem" + a + "_Image"), (title = f.findName(e + "_ScrollItem" + a + "_Title"), d && c)); a++) this._items[a - 1] = {
			button: new Sys.UI.Silverlight._Button(d, true, 0, this._itemClick, null, this),
			image: new Sys.UI.Silverlight._DomElement(c, true),
			title: title ? new Sys.UI.Silverlight._TextBlock(title, null, true) : null
		};
		if (this._items.length > 0) {
			var b = this._items[0].button.get_element();
			this._itemSize = this._horizontal ? b.width : b.height;
			this._itemSpacing = b[this._reference] * 2
		}
	},
	dispose: function () {
		if (this._next) this._next.dispose();
		if (this._previous) this._previous.dispose();
		if (this._toggle) this._toggle.dispose();
		for (var b = 0, c = this._items.length; b < c; b++) {
			var a = this._items[b];
			a.button.dispose();
			a.image.dispose();
			if (a.title) a.title.dispose()
		}
		this._virtualItems = null;
		this._imageItems = null;
		this._scrollAnimation = null;
		this._scrollStoryboard = null;
		Sys.UI.Silverlight._ImageList.callBaseMethod(this, "dispose")
	},
	_ensureActivation: function () {
		if (this._imageItems.length === 0 || !this.get_canActivate()) this.set_active(false);
		else {
			this.set_active(true);
			this._reset();
			this._assignImages()
		}
	},
	_handleOverflow: function (c) {
		var f = c === 1 ? this._items.length - 1 : -1,
		b = this._scrollOffset + f,
		a = this._items[this._overflowIndex],
		e = a.image.get_element(),
		d = this._virtualItems[this._imageItems[b]];
		e.source = null;
		e.source = d.get_thumbnailSource();
		a.button._imageIndex = b;
		if (a.title) a.title.set_text(d.get_title());
		var g = a.button.get_element();
		g[this._reference] = b * (this._itemSize + this._itemSpacing) + this._itemSpacing / 2;
		this._overflowIndex += c;
		if (this._overflowIndex < 0) this._overflowIndex = this._items.length - 1;
		else if (this._overflowIndex >= this._items.length) this._overflowIndex = 0
	},
	_itemClick: function (b) {
		var a = b._imageIndex;
		if (a !== null) this._itemClickDelegate(this._imageItems[a])
	},
	_onToggle: function () {
		this.set_visible(!this.get_visible())
	},
	_reset: function () {
		var c = this._scrollOffset;
		this._scrollOffset = 0;
		for (var a = 0, d = this._items.length; a < d; a++) {
			var b = this._items[a].button;
			b._imageIndex = a;
			b.get_element()[this._reference] = a * (this._itemSize + this._itemSpacing) + this._itemSpacing / 2
		}
		this._overflowIndex = this._items.length - 1;
		if (this._scrollAnimation && c !== 0) {
			this._scrollAnimation.To = "0";
			this._scrollStoryboard.begin()
		}
	},
	_scroll: function (a) {
		if (this._scrollAnimation) {
			this._handleOverflow(a);
			var b = this._scrollOffset;
			this._scrollOffset += a;
			this._scrollAnimation.From = "-" + b * (this._itemSize + this._itemSpacing);
			this._scrollAnimation.To = "-" + this._scrollOffset * (this._itemSize + this._itemSpacing);
			this._scrollStoryboard.begin()
		} else {
			this._scrollOffset += a;
			this._assignImages()
		}
	},
	_scrollNext: function () {
		if (this._scrollOffset < this._imageItems.length - this._items.length + 1) this._scroll(1)
	},
	_scrollPrevious: function () {
		if (this._scrollOffset > 0) this._scroll(-1)
	}
};
Sys.UI.Silverlight._ImageList.registerClass("Sys.UI.Silverlight._ImageList", Sys.UI.Silverlight._DomElement);
Sys.UI.Silverlight.MarkerEventArgs = function (a) {
	this._marker = a;
	Sys.UI.Silverlight.MarkerEventArgs.initializeBase(this)
};
Sys.UI.Silverlight.MarkerEventArgs.prototype = {
	get_marker: function () {
		return this._marker || null
	}
};
Sys.UI.Silverlight.MarkerEventArgs.registerClass("Sys.UI.Silverlight.MarkerEventArgs", Sys.EventArgs);
Sys.UI.Silverlight.MediaChapterEventArgs = function (a) {
	this._chapter = a;
	Sys.UI.Silverlight.MediaChapterEventArgs.initializeBase(this)
};
Sys.UI.Silverlight.MediaChapterEventArgs.prototype = {
	get_chapter: function () {
		return this._chapter || null
	}
};
Sys.UI.Silverlight.MediaChapterEventArgs.registerClass("Sys.UI.Silverlight.MediaChapterEventArgs", Sys.CancelEventArgs);
Sys.UI.Silverlight.MediaChapter = function (c, b, a) {
	this._title = c;
	this._position = b;
	this._thumbnailSource = a;
	Sys.UI.Silverlight.MediaChapter.initializeBase(this)
};
Sys.UI.Silverlight.MediaChapter.prototype = {
	get_position: function () {
		return this._position
	},
	get_thumbnailSource: function () {
		return this._thumbnailSource || ""
	},
	get_title: function () {
		return this._title || ""
	}
};
Sys.UI.Silverlight.MediaChapter._createChapters = function () {
	var b = [];
	for (var a = 0, c = arguments.length; a < c; a += 3) {
		var f = arguments[a] || "",
		d = arguments[a + 1],
		e = arguments[a + 2] || "";
		b[b.length] = new Sys.UI.Silverlight.MediaChapter(arguments[a], arguments[a + 1], arguments[a + 2])
	}
	return b
};
Sys.UI.Silverlight.MediaChapter.registerClass("Sys.UI.Silverlight.MediaChapter");
Sys.UI.Silverlight.MediaPlayer = function (a) {
	this._children = {};
	this._timeline = [];
	Sys.UI.Silverlight.MediaPlayer.initializeBase(this, [a])
};
Sys.UI.Silverlight.MediaPlayer.prototype = {
	_autoPlay: false,
	_autoLoad: true,
	_forcePlay: false,
	_bufferPlaying: false,
	_canSeek: false,
	_caption: "",
	_chapters: null,
	_chapterStarted: -1,
	_duration: 0,
	_enableCaptions: true,
	_me: null,
	_mediaAvailable: false,
	_mediaSource: "",
	_muted: false,
	_oldState: null,
	_placeholder: "",
	_watermarkSource: "",
	_watermarkOpacity: 1,
	_watermarkPosition: 4,
	_toggledCaptions: true,
	_volume: .5,
	_forcePlayOnStop: false,
	add_chapterSelected: function (a) {
		this.get_events().addHandler("chapterSelected", a)
	},
	remove_chapterSelected: function (a) {
		this.get_events().removeHandler("chapterSelected", a)
	},
	add_chapterStarted: function (a) {
		this.get_events().addHandler("chapterStarted", a)
	},
	remove_chapterStarted: function (a) {
		this.get_events().removeHandler("chapterStarted", a)
	},
	add_currentStateChanged: function (a) {
		this.get_events().addHandler("currentStateChanged", a)
	},
	remove_currentStateChanged: function (a) {
		this.get_events().removeHandler("currentStateChanged", a)
	},
	add_markerReached: function (a) {
		this.get_events().addHandler("markerReached", a)
	},
	remove_markerReached: function (a) {
		this.get_events().removeHandler("markerReached", a)
	},
	add_mediaEnded: function (a) {
		this.get_events().addHandler("mediaEnded", a)
	},
	remove_mediaEnded: function (a) {
		this.get_events().removeHandler("mediaEnded", a)
	},
	add_mediaFailed: function (a) {
		this.get_events().addHandler("mediaFailed", a)
	},
	remove_mediaFailed: function (a) {
		this.get_events().removeHandler("mediaFailed", a)
	},
	add_mediaOpened: function (a) {
		this.get_events().addHandler("mediaOpened", a)
	},
	remove_mediaOpened: function (a) {
		this.get_events().removeHandler("mediaOpened", a)
	},
	add_volumeChanged: function (a) {
		this.get_events().addHandler("volumeChanged", a)
	},
	remove_volumeChanged: function (a) {
		this.get_events().removeHandler("volumeChanged", a)
	},
	get_autoPlay: function () {
		return this._autoPlay
	},
	set_autoPlay: function (a) {
		this._autoPlay = a;
		if (this._me) {
			this._me.autoPlay = a;
			this._ensureMedia()
		}
	},
	get_autoLoad: function () {
		return this._autoLoad
	},
	set_autoLoad: function (a) {
		this._autoLoad = a;
		if (this._me) this._ensureMedia()
	},
	get_caption: function () {
		return this._caption
	},
	set_caption: function (a) {
		this._caption = a;
		this._ensureCaption()
	},
	get_chapters: function () {
		if (this._chapters) return Array.clone(this._chapters);
		return []
	},
	set_chapters: function (a) {
		this._chapters = a;
		this._setProperties("items", ["ChapterArea"], a);
		this._timeline = [];
		if (a) for (var b = 0, c = a.length; b < c; b++) this._timeline[this._timeline.length] = a[b].get_position();
		this._ensureChapterStarted(true)
	},
	get_currentChapter: function () {
		return this._chapterStarted === -1 ? null : this.get_chapters()[this._chapterStarted]
	},
	set_currentChapter: function (a) {
		this._ensureLoaded();
		var b = this.get_chapters();
		this.set_position(a.get_position());
		this._ensureChapterStarted(false)
	},
	get_currentState: function () {
		return this._me ? this._me.currentState : null
	},
	get_enableCaptions: function () {
		return this._enableCaptions
	},
	set_enableCaptions: function (a) {
		if (a !== this.get_enableCaptions()) {
			this._enableCaptions = a;
			this._ensureCaption()
		}
	},
	get_mediaElement: function () {
		return this._me
	},
	get_mediaSource: function () {
		return this._mediaSource
	},
	set_mediaSource: function (b) {
		this._mediaSource = b;
		this._forcePlay = false;
		if (this._me) {
			this._loadPlaceholder();
			var a = !this.get_autoPlay() && !this.get_autoLoad();
			this._me.source = a ? null : b;
			if (a) this._ensureMedia()
		}
	},
	get_muted: function () {
		return this._muted
	},
	set_muted: function (a) {
		if (a !== this.get_muted()) {
			this._muted = a;
			if (this._me) {
				this._me.isMuted = a;
				this._setProperties("state", ["MuteButton"], a ? 1 : 0)
			}
			this.onVolumeChanged(Sys.EventArgs.Empty);
			this._raiseEvent("volumeChanged")
		}
	},
	get_placeholderSource: function () {
		return this._placeholder
	},
	set_placeholderSource: function (a) {
		this._placeholder = a
	},
	get_watermarkSource: function () {
		return this._watermarkSource
	},
	set_watermarkSource: function (a) {
		this._watermarkSource = a
	},
	get_watermarkOpacity: function () {
		return this._watermarkOpacity;
	},
	set_watermarkOpacity: function (a) {
		if (.2 <= a && a <= 1)  
			this._watermarkOpacity = a;
		else
			this._watermarkOpacity = 1;
	},
	get_watermarkPosition: function () {
		return this._watermarkPosition;
	},
	set_watermarkPosition: function (a) {
		if (1 <= a && a <= 4)  
			this._watermarkPosition = a;
		else
			this._watermarkPosition = 4;
	},
	get_position: function () {
		return this._me ? this._me.position.seconds : 0
	},
	set_position: function (a) {
		this._ensureLoaded();
		if (!this._canSeek) return;
		this._mediaEnded = false;
		this.set_caption("");
		a = Math.min(this._duration, Math.max(0, a));
		var b = this._me.position;
		b.seconds = a;
		this._me.position = b;
		this._ensurePosition(a)
	},
	get_volume: function () {
		return this._volume
	},
	set_volume: function (a) {
		if (a !== this.get_volume()) {
			this._volume = a;
			if (this._me && this._me.currentState !== "Closed") this._me.volume = a;
			this.onVolumeChanged(Sys.EventArgs.Empty);
			this._raiseEvent("volumeChanged")
		}
		this._setProperties("value", ["VolumeSlider"], a)
	},
	_bindAllControls: function () {
		var b = this.get_element().content.root;
		this._bindElements(b, [
			[0, ["FullScreenVideoWindow", false],
			["BufferingArea", false],
			["PlayerControls", true],
			["PlaceholderImage", false],
			["WatermarkImage", false],
			["FullScreenWatermarkImage", false]],
			[1, ["VideoWindow", true, 0, this._onTogglePlayPause, this._meDoubleClick, this],
			["FullScreenArea", false, 0, this._onTogglePlayPause, this._meDoubleClick, this],
			["PlayButton", true, 0, this._onPlay, null, this],
			["StartButton", false, 0, this._onPlay, null, this],
			["PlayPauseButton", true, 0, this._onTogglePlayPause, null, this, ["PlaySymbol", "PauseSymbol"]],
			["StopButton", true, 0, this._onStop, null, this],
			["PauseButton", true, 0, this._onPause, null, this],
			["MuteButton", true, 0, this._onMute, null, this, ["MuteOffSymbol", "MuteOnSymbol"]],
			["FullScreenButton", true, 0, this._onToggleFullScreen, null, this],
			["NextButton", true, 0, this._onNext, null, this],
			["PreviousButton", true, 0, this._onPrevious, null, this],
			["VolumeUpButton", true, 20, this._onVolumeUp, null, this],
			["VolumeDownButton", true, 20, this._onVolumeDown, null, this],
			["CaptionToggleButton", false, 0, this._onCaptionToggle, null, this, ["CaptionOnSymbol", "CaptionOffSymbol"]]],
			[2, ["TotalTimeText", null, true],
			["CurrentTimeText", null, true],
			["CaptionText", b.findName("CaptionArea"), false],
			["BufferingText", null, false],
			["FullScreenCaptionText", b.findName("FullScreenCaptionArea"), false]],
			[3, ["TimeSlider", "TimeThumb", true, this._onTimeChanged, this],
			["VolumeSlider", "VolumeThumb", true, this._onVolumeChanged, this]],
			[4, ["ChapterArea", b.findName("ChapterToggleButton"), false, this._onChapterClick, this]],
			[5, ["DownloadProgressSlider", b.findName("DownloadProgressText"), true]]]);
		this._bufferingStoryboard = b.findName("BufferingArea_Buffering");
		var a = this._children["VideoWindow"];
		if (!a) throw Error.invalidOperation(Sys.UI.Silverlight.MediaRes.noMediaElement);
		this._me = a.get_element();
		a.bindEvent("mediaOpened", null, this._meOpened, this);
		a.bindEvent("mediaFailed", null, this._meFailed, this);
		a.bindEvent("mediaEnded", null, this._meEnded, this);
		a.bindEvent("downloadProgressChanged", null, this._meDownloadProgress, this);
		a.bindEvent("bufferingProgressChanged", null, this._meBufferingProgress, this);
		a.bindEvent("markerReached", null, this._meMarker, this);
		a.bindEvent("currentStateChanged", null, this._meState, this)
	},
	_bindElements: function (k, f) {
		for (var d = 0, l = f.length; d < l; d++) {
			var c = f[d],
			i = c[0];
			for (var e = 1, j = c.length; e < j; e++) {
				var b = c[e],
				g = b[0],
				h = k.findName(g);
				if (!h) continue;
				var a;
				switch (i) {
				case 0:
					a = "_DomElement";
					break;
				case 1:
					a = "_Button";
					break;
				case 2:
					a = "_TextBlock";
					break;
				case 3:
					a = "_Slider";
					break;
				case 4:
					a = "_ImageList";
					break;
				case 5:
					a = "_ProgressBar"
				}
				a = Sys.UI.Silverlight[a];
				this._children[g] = new a(h, b[1], b[2], b[3], b[4], b[5], b[6])
			}
		}
	},
	_detectChapterChange: function (a) {
		if (this._timeline.length === 0) return;
		var b = this._chapterStarted === -1 ? -Infinity : this._timeline[this._chapterStarted],
		c = this._chapterStarted + 1 >= this._timeline.length ? Infinity : this._timeline[this._chapterStarted + 1];
		if (a < b || a > c) this._ensureChapterStarted(false, a)
	},
	_enableBuffering: function (b) {
		var a = b !== null && b < 100;
		if (a) this._setProperties("text", ["BufferingText"], Math.floor(b).toString());
		this._setProperties("visible", ["BufferingText", "BufferingArea"], a);
		var c = this._bufferingStoryboard;
		if (!c) return;
		if (!a) {
			c.stop();
			this._bufferPlaying = false
		} else if (!this._bufferPlaying) {
			c.begin();
			this._bufferPlaying = true
		}
	},
	_ensureCaption: function () {
		var b = this._toggledCaptions && this.get_enableCaptions(),
		a = b ? this.get_caption() : "";
		this._setProperties("text", ["CaptionText", "FullScreenCaptionText"], a);
		if (a) this._setProperties("visible", ["CaptionToggleButton"], true)
	},
	_ensureChapterStarted: function (c, b) {
		if (!this._me) return;
		if (!b) b = this.get_position();
		var a = this._canSeek ? this._getChapterAt(b) : -1;
		if (c && (a !== -1 || a !== this._chapterStarted) || a !== this._chapterStarted) this._raiseChapterStarted(a)
	},
	_ensureLoaded: function () {
		if (!this._loaded) throw Error.invalidOperation(Sys.UI.Silverlight.MediaRes.silverlightNotLoaded)
	},
	_ensureMedia: function () {
		var b = this._mediaAvailable,
		a = this._duration > 0,
		c = a && this._canSeek,
		f = this.get_autoPlay() || this.get_autoLoad(),
		e = !!this.get_mediaSource(),
		h = !this._children["StartButton"] && !f && e;
		this._setProperties("canActivate", ["ChapterArea"], c);
		this._setProperties("readOnly", ["TimeSlider"], !c);
		this._setProperties("enabled", ["TimeSlider"], a);
		this._setProperties("visible", ["TotalTimeText", "CurrentTimeText"], a);
		this._setProperties("enabled", ["PreviousButton", "NextButton"], c);
		this._setProperties("enabled", ["PauseButton", "StopButton"], b);
		this._setProperties("enabled", ["PlayPauseButton", "PlayButton"], b || h);
		var d = this._children["StartButton"];
		if (d) {
			var g = !b && !f && e;
			d.set_visible(g);
			d.get_element().IsHitTestVisible = g
		}
		if (a) {
			this._setProperties("text", ["TotalTimeText"], this._formatTime(this._duration));
		}
		this._loadWatermark();
	},
	_ensurePosition: function (a) {
		if (this._duration) {
			a = a || this.get_position();
			this._setProperties("text", ["CurrentTimeText"], this._formatTime(a));
			this._setProperties("value", ["TimeSlider"], a / this._duration)
		}
	},
	_formatTime: function (e) {
		var a = Math.floor(e / 60 / 60),
		b = Math.floor(e / 60) - a * 60,
		c = Math.floor(e) - b * 60 - a * 60 * 60,
		d = "";
		if (a) {
			a = "0" + a;
			d = a.substr(a.length - 2, 2) + ":"
		}
		b = "0" + b;
		d += b.substr(b.length - 2, 2) + ":";
		c = "0" + c;
		d += c.substr(c.length - 2, 2);
		return d
	},
	_getChapterAt: function (b) {
		for (var a = 0, c = this._timeline.length; a <= c; a++) if (a === this._timeline.length || this._timeline[a] - .001 > b) return a - 1
	},
	_loadPlaceholder: function () {
		var b = this.get_placeholderSource(),
		a = this._children["PlaceholderImage"];
		if (a && b) {
			a.get_element().source = b;
			a.set_visible(true)
		}
	},
	_loadWatermark: function () {
		var b = this.get_watermarkSource(),
		a = this._children["WatermarkImage"],
		c = this._children["VideoWindow"],
		d = this._children["FullScreenWatermarkImage"],
		e = this._children["FullScreenArea"];
		if (a && b && b !== "" && c && d && e) {
			a.get_element().source = d.get_element().source = b;
			a.get_element().opacity = d.get_element().opacity = this.get_watermarkOpacity();
			this._setWatermarkLocation( c, a, this.get_watermarkPosition() );
			this._setWatermarkLocation( e, d, this.get_watermarkPosition() );	
		}
	},
	_setWatermarkLocation: function (a, b, p) {
		var c = a.get_element(),
		d = b.get_element(),
		offset = 10;
		if (d.ActualWidth == 0 || d.ActualHeight == 0) return;
		switch (p){
			case 1: {
				d["Canvas.Left"] = c["Canvas.Left"] + offset;
				d["Canvas.Top"] = c["Canvas.Top"] + offset;
				break;
			}
			case 2: {
				d["Canvas.Left"] = c["Canvas.Left"] + c.width - d.ActualWidth - offset;
				d["Canvas.Top"] = c["Canvas.Top"] + offset;
				break;
			}
			case 3: {
				d["Canvas.Left"] = c["Canvas.Left"] + offset;
				d["Canvas.Top"] = c["Canvas.Top"] + c.height - d.ActualHeight - offset;
				break;
			}
			default: {
				d["Canvas.Left"] = c["Canvas.Left"] + c.width - d.ActualWidth - offset;
				d["Canvas.Top"] = c["Canvas.Top"] + c.height - d.ActualHeight - offset;
				break;
			}
		}
	},
	_meBufferingProgress: function () {
		if (!this._me) return;
		var a = Math.round(this._me.bufferingProgress * 100);
		this._enableBuffering(a)
	},
	_mediaQueued: function () {
		return !this.get_autoPlay() && !this.get_autoLoad() && !this._me.source && this.get_mediaSource()
	},
	_meDoubleClick: function () {
		if (!this._me) return;
		this._onTogglePlayPause();
		this._onToggleFullScreen()
	},
	_meDownloadProgress: function () {
		if (!this._me) return;
		this._enableBuffering(null);
		this._setProperties("value", ["DownloadProgressSlider"], this._me.downloadProgress)
	},
	_meMarker: function (f, d) {
		if (!this._me) return;
		var a = d.marker;
		if (this._toggledCaptions && this.get_enableCaptions()) {
			var e = a.type ? a.type.toLowerCase() : "";
			if (e === "caption") {
				var b = a.text ? a.text : "";
				if (b.trim().length === 0) b = "";
				this.set_caption(b);
				this.raisePropertyChanged("caption")
			}
		}
		var c = new Sys.UI.Silverlight.MarkerEventArgs(a);
		this.onMarkerReached(c);
		this._raiseEvent("markerReached", c)
	},
	_meEnded: function () {
		if (!this._me) return;
		this._mediaEnded = true;
		this._forcePlay = false;
		this.onMediaEnded(Sys.EventArgs.Empty);
		this._raiseEvent("mediaEnded")
	},
	_meFailed: function (c, b) {
		if (!this._me) return;
		this._mediaAvailable = false;
		this._mediaEnded = false;
		this._canSeek = false;
		this._forcePlay = false;
		this._duration = 0;
		this._ensureMedia();
		this._enableBuffering(null);
		this.set_caption("");
		var a = new Sys.UI.Silverlight.ErrorEventArgs(b);
		this.onMediaFailed(a);
		this._raiseEvent("mediaFailed", a)
	},
	_meOpened: function () {
		if (!this._me) return;
		this._mediaEnded = false;
		this._mediaAvailable = true;
		this._canSeek = this._me.canSeek;
		this._duration = this._me.naturalDuration.seconds;
		this._me.volume = this.get_volume();
		this._setProperties("visible", ["PlaceholderImage"], false);
		this.set_caption("");
		this._ensurePosition();
		this._ensureMedia();
		if (!this._timerCookie) {
			this._tickTimerDelegate = Function.createDelegate(this, this._tickTimer);
			this._timerCookie = window.setTimeout(this._tickTimerDelegate, 200)
		}
		this.onMediaOpened(Sys.EventArgs.Empty);
		this._raiseEvent("mediaOpened");
		if (this._forcePlay) {
			this._forcePlay = false;
			this._me.play()
		}
	},
	_meState: function () {
		if (!this._me) return;
		var a = this._me.currentState;
		if (a === "Stopped" && this._forcePlayOnStop) {
			this._forcePlayOnStop = false;
			this._me.play()
		}
		if (a === this._oldState) return;
		this._oldState = a;
		if (a === "Closed") {
			this._enableBuffering(null);
			this._mediaAvailable = false;
			this._canSeek = false;
			this._forcePlay = false;
			this._duration = 0;
			this._mediaEnded = false;
			this._ensureMedia();
			this.set_caption("")
		} else if (a === "Playing" || a === "Paused") this._enableBuffering(null);
		this._setProperties("state", ["PlayPauseButton"], a === "Playing" ? 1 : 0);
		this.onCurrentStateChanged(Sys.EventArgs.Empty);
		this._raiseEvent("currentStateChanged")
	},
	_onCaptionToggle: function () {
		this._toggledCaptions = !this._toggledCaptions;
		this._ensureCaption();
		this._setProperties("state", ["CaptionToggleButton"], this._toggledCaptions ? 0 : 1)
	},
	_onChapterClick: function (d) {
		var b = this.get_chapters();
		if (d < b.length) {
			var c = b[d],
			a = new Sys.UI.Silverlight.MediaChapterEventArgs(c);
			this.onChapterSelected(a);
			this._raiseEvent("chapterSelected", a);
			if (!a.get_cancel()) this.set_currentChapter(c)
		}
	},
	onChapterSelected: function () {},
	onChapterStarted: function () {},
	onCurrentStateChanged: function () {},
	onMarkerReached: function () {},
	onMediaEnded: function () {},
	onMediaFailed: function () {},
	onMediaOpened: function () {},
	_onMute: function () {
		this.set_muted(!this.get_muted())
	},
	_onNext: function () {
		var a = this.get_chapters();
		if (!a || !a.length) this._skipTime(1);
		else {
			var d = this._chapterStarted + 1;
			if (d < a.length) {
				var c = a[d],
				b = new Sys.UI.Silverlight.MediaChapterEventArgs(c);
				this.onChapterSelected(b);
				this._raiseEvent("chapterSelected", b);
				if (!b.get_cancel()) this.set_currentChapter(c)
			}
		}
	},
	_onPause: function () {
		this.pause()
	},
	_onPlay: function () {
		this.play()
	},
	onPluginFullScreenChanged: function () {
		var a = this._children["FullScreenArea"];
		if (!a) return;
		a = a.get_element();
		var b = this.get_element().content,
		d = b.root;
		if (b.FullScreen) {
			a.Visibility = 0;
			var g = 0,
			h = 0,
			e = this.get_scaleMode(),
			c = Sys.UI.Silverlight.Control._computeScale(d, e);
			if (e !== Sys.UI.Silverlight.ScaleMode.stretch) {
				var f = Math.min(c.horizontal, c.vertical);
				g = (b.ActualWidth - a.width * f) / 2;
				h = (b.ActualHeight - a.height * f) / 2
			}
			this._originalScale = Sys.UI.Silverlight.Control._applyMatrix(d, c.horizontal, c.vertical, g, h)
		} else {
			Sys.UI.Silverlight.Control._applyMatrix(d, this._originalScale.horizontal, this._originalScale.vertical, 0, 0);
			a.Visibility = 1;
		}
	},
	_onPrevious: function () {
		var b = this.get_chapters();
		if (!b || !b.length) this._skipTime(-1);
		else {
			var a = -1;
			if (this._chapterStarted >= 0) {
				var e = this._timeline[this._chapterStarted],
				f = this.get_position();
				if (f - e > 1) a = this._chapterStarted;
				else a = this._chapterStarted - 1
			}
			var c = a === -1 ? null : b[a],
			d = new Sys.UI.Silverlight.MediaChapterEventArgs(c);
			this.onChapterSelected(d);
			this._raiseEvent("chapterSelected", d);
			if (!d.get_cancel()) if (c) this.set_currentChapter(c);
			else this.set_position(0)
		}
	},
	_onStop: function () {
		this.stop()
	},
	_onToggleFullScreen: function () {
		var a = this.get_element().content;
		a.FullScreen = !a.FullScreen
	},
	_onTogglePlayPause: function () {
		this.get_currentState() === "Playing" ? this.pause() : this.play()
	},
	_onTimeChanged: function (a) {
		this.set_position(a.get_value() * this._duration)
	},
	onVolumeChanged: function () {},
	_onVolumeChanged: function (a) {
		this.set_volume(a.get_value())
	},
	_onVolumeDown: function () {
		this.set_volume(Math.max(0, this.get_volume() - .02))
	},
	_onVolumeUp: function () {
		this.set_volume(Math.min(1, this.get_volume() + .02))
	},
	pause: function () {
		this._ensureLoaded();
		this._me.pause()
	},
	play: function () {
		this._ensureLoaded();
		if (this._mediaQueued()) {
			this._forcePlay = true;
			this._me.source = this.get_mediaSource();
			var a = this._children["StartButton"];
			if (a) {
				a.set_visible(false);
				a.get_element().IsHitTestVisible = false
			}
		} else if (this._mediaEnded) {
			this._mediaEnded = false;
			this.set_caption("");
			this._forcePlayOnStop = true;
			this._me.stop()
		} else this._me.play()
	},
	pluginDispose: function () {
		if (this._timerCookie) {
			window.clearTimeout(this._timerCookie);
			this._timerCookie = null
		}
		for (var a in this._children) this._children[a].dispose();
		if (this._me) {
			this._me.stop();
			this._me = null
		}
		if (this._bufferingStoryboard) {
			this._bufferingStoryboard.stop();
			this._bufferingStoryboard = null
		}
		Sys.UI.Silverlight.MediaPlayer.callBaseMethod(this, "pluginDispose")
	},
	_raiseChapterStarted: function (a) {
		this._chapterStarted = a;
		var c = a === -1 ? null : this.get_chapters()[a],
		b = new Sys.UI.Silverlight.MediaChapterEventArgs(c);
		this.onChapterStarted(b);
		this._raiseEvent("chapterStarted", b)
	},
	_raisepluginLoaded: function () {
		Sys.UI.Silverlight.MediaPlayer.callBaseMethod(this, "_raisepluginLoaded");
		this._bindAllControls();
		var a = this._me;
		this._loadPlaceholder();
		a.autoPlay = this.get_autoPlay();
		a.isMuted = this.get_muted();
		this._setProperties("state", ["MuteButton"], this.get_muted() ? 1 : 0);
		a.volume = this.get_volume();
		this._setProperties("value", ["VolumeSlider"], this.get_volume());
		this._setProperties("items", ["ChapterArea"], this.get_chapters());
		this._ensureCaption();
		if (this.get_mediaSource() && (this.get_autoPlay() || this.get_autoLoad())) a.source = this.get_mediaSource();
		else this._ensureMedia()
	},
	_setProperties: function (e, b, d) {
		for (var a = 0, f = b.length; a < f; a++) {
			var c = this._children[b[a]];
			if (c) c["set_" + e](d)
		}
	},
	_skipTime: function (b) {
		var a = Math.max(5, this._duration / 10);
		a = b * a;
		var c = a + this.get_position();
		this.set_position(c)
	},
	stop: function () {
		this._ensureLoaded();
		this._me.stop();
		this._ensurePosition();
		this._mediaEnded = false;
		this.set_caption("")
	},
	_tickTimer: function () {
		this._timerCookie = window.setTimeout(this._tickTimerDelegate, 200);
		var a = this.get_position();
		this._detectChapterChange(a);
		if (this._forceUpdate || this.get_currentState() === "Playing") {
			this._forceUpdate = false;
			this._ensurePosition(a)
		}
	}
};
Sys.UI.Silverlight.MediaPlayer.registerClass("Sys.UI.Silverlight.MediaPlayer", Sys.UI.Silverlight.Control);