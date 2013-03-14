/**
 * Provides the user interface for account member management.
 */
;(function ($) {
	var self = {};

	self.opts = {};

	self.list = null;
	
	self.tpl = null;

	self.add_member_dialog = function () {
		$.open_dialog (
			'Add Member',
			'<p>...</p>'
		);
	};

	self.add_member = function () {
	};

	self.remove_member = function () {
	};

	self.disable_member = function () {
	};

	self.enable_member = function () {
	};

	self.render_members = function () {
		self.list.html ('');

		$(self.opts.data).each (function () {
			// add to list
			this.is_owner = (this.type === 'owner') ? true : false;
			self.list.append (self.tpl (this));
		});
	};

	$.account_members = function (opts) {
		var defaults = {
			list: '#members',
			add_button: '#member-add',
			member_tpl: '#member-tpl',
			data: [],
			limit: -1
		};
		
		self.opts = $.extend (defaults, opts);
		self.list = $(self.opts.list);
		self.tpl = Handlebars.compile ($(self.opts.member_tpl).html ());

		self.list.on ('click', '.member-disable', {}, self.disable_member);
		self.list.on ('click', '.member-enable', {}, self.enable_member);
		self.list.on ('click', '.member-remove', {}, self.remove_member);
		$(self.opts.add_button).click (self.add_member_dialog);

		self.render_members ();
	};
})(jQuery);