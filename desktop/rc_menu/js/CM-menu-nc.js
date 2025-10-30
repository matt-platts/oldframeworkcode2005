/*
---
description: CM-menu.

license: MIT-style

authors:
- crazycooder

requires:
- core/1.2.4: '*'

provides: CM-menu

...
*/

var CM = {
    pid: 0
};

CM.menu = new Class({
    Implements: Options,
	
	options: {
		mergeParent: true,
	    links: []
	},
	
	initialize: function(target, options) {
	    this.setOptions(options);
	    this.target = $(target);
	    this.target.CM = {};
		this.bounds = {};
		this.links = [];
		this.childs = [];
		this.build();
		this.target.CM.menu = this;
		this.bounds.unrender = this.unrender.bind(this);
		this.bounds.keydown = this.keydown.bind(this);
	    this.target.addEvent('contextmenu', this.contextmenu.bind(this));
	},
	
	build: function() {
		this.div = new Element('div', {'class' : 'CM-menu'}).inject($(document.body));
		if (this.options.mergeParent) this.buildParent();
	    this.options.links.each(function(link) {
			this.links.push(new CM.link(this, link));
	    }, this);
		this.links.each(function(link) {
			link.div.inject(this.div);
		}, this);
	},
	
	buildParent: function() {
	    var parent = this.target;
		while(parent = parent.getParent()) {
			if (parent.CM && parent.CM.menu) {
				parent.CM.menu.options.links.each(function(link) {
					this.links.push(new CM.link(this, link));
				}, this);
			}
		}
	},
	
	render: function(e) {
		e.stop();
	    this.show(e.page);
		this.links.each(function(link) {
			link.render();
		}, this);
		document.addEvent('click', this.bounds.unrender);
		document.addEvent('keydown', this.bounds.keydown);
	},
	
	unrender: function() {
		this.hide();
		this.links.each(function(link) {
			link.unrender();
		}, this);
		document.removeEvent('click', this.bounds.unrender);
		document.removeEvent('keydown', this.bounds.keydown);
	},
	
	contextmenu: function(e) {
	    document.fireEvent('click', this.bounds.unrender);
	    this.render(e);
	},
	
	keydown: function(e) {
		if (e.key == "esc") {
			this.unrender();
		}
	},
	
	show: function(pos) {
		this.div.setStyles({'display' : 'block'});
		if (pos.x + this.div.getCoordinates().width > $(document).getCoordinates().width) {
	        pos.x -= this.div.getCoordinates().width;
	    }
		if (pos.y + this.div.getCoordinates().height > $(document).getCoordinates().height) {
	        pos.y -= this.div.getCoordinates().height;
	    }
		this.div.setStyles({
			'top' : pos.y,
			'left': pos.x
		});
	},
	
	hide: function() {
		this.div.setStyles({'display' : 'none'});
	}
});

CM.link = new Class({
	Implements: Options,
	
	options: {
	    links: [],
		libelle: '',
        shortcut: '',
	    icon: '',
		onclick: '',
		sep: false
	},
	
	initialize: function(menu, options) {
		CM.pid += 1;
	    this.setOptions(options);
		this.pid = 'CM-link::' + CM.pid;
	    this.CM = {};
		this.CM.menu = menu;
		this.CM.shortcut = new CM.shortcut(this, this.options.shortcut);
	    this.build();
	},
	
	build: function() {
		if (this.div) return;
		var sepClass = (this.options.sep) ? ' sep' : '';
		this.div = new Element('div', {'class' : 'link' + sepClass, 'text' : this.options.libelle});
		if (this.options.icon) { this.img = new Element('img', {'class' : 'icon', 'src' : this.options.icon}).inject(this.div, 'top'); }
		else { this.img = new Element('div', {'class' : 'icon'}).inject(this.div, 'top'); }
		this.span = new Element('span', {'class' : 'note', 'text' : this.CM.shortcut.getLibelle()}).inject(this.div);
		try {
			this.options.onclick.create();
			this.div.addEvent('click', this.options.onclick);
		}
		catch(e) {
			this.div.addEvent('click', function(e) {
				e.stop();
			});
		}
		this.div.CM = {};
		this.div.CM.link = this;
		this.div.CM.menu = this.CM.menu;
		if (this.options.links.length) {
			this.span.innerHTML = '>';
			this.span.addClass('arrow');
			this.CM.menu.childs.push(new CM.child(this.div, {
				theme: this.CM.menu.options.theme,
				links: this.options.links
			}));
		}
	},
	
	render: function() {
		this.CM.shortcut.render();
	},
	
	unrender: function() {
		this.CM.shortcut.unrender();
		this.CM.menu.childs.each(function(child) {
			child.unrender();
		}, this);
	}
});

CM.child = new Class({
	Implements: Options,
	Extends: CM.menu,
	
	initialize: function(target, options) {
	    this.setOptions(options);
	    this.target = $(target);
		this.bounds = {};
		this.links = [];
		this.childs = [];
		this.build();
		this.bounds.unrender = this.unrender.bind(this);
		this.bounds.keydown = this.keydown.bind(this);
	    this.target.addEvent('mouseenter', this.render.bind(this));
	},
	
	show: function(pos) {
		this.div.setStyles({'display' : 'block'});
		var coord = this.target.getCoordinates();
		coord.x  = coord.left;
		coord.x += coord.width;
		if (coord.x + this.div.getCoordinates().width > $(document).getCoordinates().width) {
			coord.x  = coord.left;
			coord.x -= coord.width;
			coord.x -= this.div.getStyle('borderLeft').toInt();
			coord.x -= this.div.getStyle('borderRight').toInt();
		}
		coord.y  = coord.top;
		coord.y -= this.div.getStyle('borderTop').toInt();
		if (coord.y + this.div.getCoordinates().height > $(document).getCoordinates().height) {
			coord.y = coord.bottom;
			coord.y -= this.div.getCoordinates().height;
			coord.y += this.div.getStyle('borderTop').toInt();
			coord.y += this.div.getStyle('borderBottom').toInt();
		}
		this.div.setStyles({'top' : coord.y, 'left': coord.x});
	},
	
	render: function(e) {
		this.parent(e);
		this.target.CM.menu.links.each(function(link) {
			if (this.target.CM.link.pid != link.pid) {
				link.div.addEvent('mouseenter', this.bounds.unrender);
			}
		}, this);
	},
	
	unrender: function() {
		this.parent();
		this.target.CM.menu.links.each(function(link) {
			if (this.target.CM.link.pid != link.pid) {
				link.div.removeEvent('mouseenter', this.bounds.unrender);
			}
		}, this);
	}
});

CM.shortcut = new Class({
	Implements: Options,
	
	options: {
		control: false,
        shift: false,
	    alt: false,
		key: '',
	},
	
	initialize: function(link, options) {
	    this.setOptions(options);
		this.CM = {};
		this.CM.link = link;
		this.bounds = {};
		this.bounds.keypress = this.keypress.bind(this);
	},
	
	getLibelle: function() {
		var lib = '';
		lib += (this.options.control) ? 'Ctrl + ' : '';
		lib += (this.options.shift) ? 'Shift + ' : '';
		lib += (this.options.alt) ? 'Alt + ' : '';
		lib += this.options.key.capitalize();
		return lib;
	},
	
	render: function() {
		document.addEvent('keypress', this.bounds.keypress);
	},
	
	unrender: function() {
		document.removeEvent('keypress', this.bounds.keypress);
	},
	
	keypress: function(e) {
		if (e.control == this.options.control && e.shift == this.options.shift && e.alt == this.options.alt && e.key == this.options.key) {
			e.preventDefault();
			try { this.CM.link.options.onclick(); } catch(e) {}
		}
	}
});