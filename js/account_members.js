/**
 * Provides the user interface for account member management.
 */
;(function ($) {
	var self = {};

	self.opts = {};

	self.list = null;
	
	self.tpl = null;

	self.limit_reached = function () {
		if (self.opts.limit === -1) {
			return false;
		} else if (self.opts.limit === 0) {
			return true;
		}
		if (self.opts.limit > self.opts.data.length) {
			return false;
		}
		return true;
	};

	self.add_member_dialog = function () {
		if (self.limit_reached ()) {
			$('#limit-reached').modal ();
			return false;
		}
		$('#add-member-name').val ('');
		$('#add-member-email').val ('');
		$('#add-member-name-notice').hide ();
		$('#add-member-email-notice').hide ();
		$('#add-member-email2-notice').hide ();
		$('#add-member').modal ();
		return false;
	};

	self.update_members = function () {
		$.get (
			'/saasy/api/members',
			function (res) {
				if (res.success) {
					self.opts.data = res.data;
				}
				self.render_members ();
			}
		);
	};

	self.add_member = function () {
		var name = $('#add-member-name').val (),
			email = $('#add-member-email').val ();

		if (name.length === 0) {
			$('#add-member-name-notice').show ();
			return false;
		} else {
			$('#add-member-name-notice').hide ();
		}

		if (email.length === 0) {
			$('#add-member-email-notice').show ();
			$('#add-member-email2-notice').hide ();
			return false;
		} else {
			$('#add-member-email-notice').hide ();
			$('#add-member-email2-notice').hide ();
		}

		$.post (
			'/saasy/api/member_add',
			{name: name, email: email},
			function (res) {
				if (! res.success) {
					$('#add-member-email2-notice').show ();
					return;
				}
				$('#add-member').modal ('hide');
				$.add_notice ('Member added: ' + name);
				self.update_members ();
			}
		);

		return false;
	};

	self.remove_member = function () {
		var account = $(this).data ('id'),
			name = $(this).data ('name');

		if (! confirm ('Are you sure you want to remove this member?')) {
			return false;
		}
		
		$.post (
			'/saasy/api/member_remove',
			{account: account},
			function (res) {
				if (! res.success) {
					alert (res.error);
					return;
				}
				$.add_notice ('Member removed: ' + name);
				self.update_members ();
			}
		);

		return false;
	};

	self.disable_member = function () {
		var account = $(this).data ('id'),
			name = $(this).data ('name');
		
		$.post (
			'/saasy/api/member_disable',
			{account: account},
			function (res) {
				if (! res.success) {
					alert (res.error);
					return;
				}
				$.add_notice ('Member disabled: ' + name);
				self.update_members ();
			}
		);

		return false;
	};

	self.enable_member = function () {
		var account = $(this).data ('id'),
			name = $(this).data ('name');
		
		$.post (
			'/saasy/api/member_enable',
			{account: account},
			function (res) {
				if (! res.success) {
					alert (res.error);
					return;
				}
				$.add_notice ('Member enabled: ' + name);
				self.update_members ();
			}
		);

		return false;
	};

	self.render_members = function () {
		self.list.html ('');

		$(self.opts.data).each (function () {
			// add to list
			this.is_owner = (this.type === 'owner') ? true : false;
			this.enabled = parseInt (this.enabled);
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
		$('#add-member-form').submit (self.add_member);

		self.render_members ();
	};
})(jQuery);