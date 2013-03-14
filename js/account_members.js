/**
 * Provides the user interface for account member management.
 */
;(function ($) {
	var self = {};

	self.opts = {};

	self.list = null;

	self.add_member_dialog = function () {
		$.open_dialog (
			'Add Member',
			'<p>...</p>'
		);
	};

	self.add_member = function () {
	};

	$.account_members = function (opts) {
		var defaults = {
			list: '#members',
			add_button: '#member-add',
			data: [],
			limit: -1
		};
		
		self.opts = $.extend (defaults, opts);

		self.list = $(self.opts.list);

		$(self.opts.add_button).click (self.add_member_dialog);

		$(self.opts.data).each (function () {
			// add to list
		});
	};
})(jQuery);