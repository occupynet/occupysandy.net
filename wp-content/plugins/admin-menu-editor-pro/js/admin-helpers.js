jQuery(function($) {
	//Menu separators shouldn't be clickable and should have a custom class.
	$('#adminmenu')
			.find('.ws-submenu-separator')
			.closest('a').click(function() {
				return false;
			})
			.closest('li').addClass('ws-submenu-separator-wrap');
});