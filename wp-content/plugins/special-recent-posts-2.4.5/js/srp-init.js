/*
| --------------------------------------------------------
| File        : srp-init.js
| Project     : Special Recent Posts PRO plugin for Wordpress
| Version     : 2.4.5
| Description : Custom js init file.
| Author      : Luca Grandicelli
| Author URL  : http://www.lucagrandicelli.com
| Plugin URL  : http://codecanyon.net/item/special-recent-posts-pro/552356
| Copyright (C) 2011-2012  Luca Grandicelli
| --------------------------------------------------------
*/

/*
| ------------------------------------------------------
| This function handles the switching admin tabs.
| ------------------------------------------------------
*/
function srpTabsSwitcher(tab) {
	
	// Switching mode.
	switch(tab) {
	
		case 1:
			
			// Adding active class to tab links.
			jQuery('a.srp_tab_1').addClass('active');
			jQuery('a.srp_tab_2').removeClass('active');
			
			// Switching Tab.
			jQuery('div#srp_tab2').hide();
			jQuery('div#srp_tab1').show();
		break;
		
		case 2:
		
			// Adding active class to tab links.
			jQuery('a.srp_tab_2').addClass('active');
			jQuery('a.srp_tab_1').removeClass('active');
			
			// Switching Tab.
			jQuery('div#srp_tab1').hide();
			jQuery('div#srp_tab2').show();
		break;
	}
}

/*
| ------------------------------------------------------
| This function handles the widget accordion animation
| ------------------------------------------------------
*/
function initAccordion() {

	// Main logic for accordion headers links.
	jQuery('dl.srp-wdg-accordion dt a').live({

		click: function() {
		
			// Removing highlight from all headers links.
			jQuery('dl.srp-wdg-accordion dt a').removeClass("accordion-active-link");
			
			// Highlighting current header link.
			jQuery(this).addClass("accordion-active-link");
			
			// Normal Behaviour. Accordion Logic.
			jQuery('dl.srp-wdg-accordion dd').slideUp();
			jQuery(this).parent().next().slideDown();
			
			// Return false.
			return false;
		}
	});
	
	// Main logic for textareas highlighting.
	jQuery('dl.srp-wdg-accordion textarea').live({

		click: function() {
		
			// Setting focus on clicked textarea.
			this.focus();
			
			// Highlighting inner text.
			this.select();
			
			// Return false.
			return false;
		}
	});
}

/*
| ------------------------------------------------------
| JQUERY DOMREADY
| ------------------------------------------------------
*/

// Setting up jQuery no-conflict.
jQuery.noConflict();

// DOM READY START.
jQuery(document).ready(function() {

	// Initialize Accordion.
	initAccordion();
	
	jQuery()
});