//(c) W-Shadow

/*global wsEditorData, defaultMenu, customMenu */
/** @namespace wsEditorData */

var wsIdCounter = 0;

var AmeCapabilityManager = (function(roles, users) {
    var me = {};
	users = users || {};

    function parseActorString(actor) {
        var separator = actor.indexOf(':');
        if (separator == -1) {
            throw {
                name: 'InvalidActorException',
                message: "Actor string does not contain a colon.",
                value: actor
            };
        }

        return {
            'type' : actor.substring(0, separator),
            'id' : actor.substring(separator + 1)
        }
    }

    me.hasCap = function(actor, capability, context) {
		context = context || {};
        var actorData = parseActorString(actor);

	    //Super admins have access to everything, unless specifically denied.
	    if ( actor == 'special:super_admin' ) {
		    return (capability != 'do_not_allow');
	    }

	    if (actorData.type == 'role') {
			return me.roleHasCap(actorData.id, capability);
	    } else if (actorData.type == 'user') {
		    return me.userHasCap(actorData.id, capability, context);
	    }

	    throw {
		    name: 'InvalidActorTypeException',
		    message: "The specified actor type is not supported",
		    value: actor,
		    'actorType': actorData.type
	    };
    };

	me.roleHasCap = function(roleId, capability) {
		if (!roles.hasOwnProperty(roleId)) {
			throw {
				name: 'UnknownRoleException',
				message: 'Can not check capabilities for an unknown role',
				value: roleId,
				requireCapability: capability
			};
		}

		var role = roles[roleId];
		if ( role.capabilities.hasOwnProperty(capability) ) {
			return role.capabilities[capability];
		} else if (roleId == capability) {
			return true;
		}
		return false;
	};

	me.userHasCap = function(login, capability, context) {
		context = context || {};
		if (!users.hasOwnProperty(login)) {
			throw {
				name: 'UnknownUserException',
				message: 'Can not check capabilities for an unknown user',
				value: login,
				requireCapability: capability
			};
		}

		var user = users[login];
		if ( user.capabilities.hasOwnProperty(capability) ) {
			return user.capabilities[capability];
		} else {
            //Super Admins have all capabilities, except those explicitly denied.
            //We also need to check if the Super Admin actor is allowed in this context.
            if (user.is_super_admin ) {
                if (context.hasOwnProperty('special:super_admin')) {
                    return context['special:super_admin'];
                }
                return (capability != 'do_not_allow');
            }

			//Check if any of the user's roles have the capability.
			for(var index = 0; index < user.roles.length; index++) {
				var roleId = user.roles[index];

				//Skip roles that are disabled in this context (i.e. via grant_access).
				if (context.hasOwnProperty('role:' + roleId) && !context['role:' + roleId]) {
					continue;
				}

				if (me.roleHasCap(roleId, capability)) {
					return true;
				}
			}
		}

		return false;
	};

	me.roleExists = function(roleId) {
		return roles.hasOwnProperty(roleId);
	};

	/**
	 * Compare the specificity of two actors.
	 *
	 * Returns 1 if the first actor is more specific than the second, 0 if they're both
	 * equally specific, and -1 if the second actor is more specific.
	 *
	 * @param {String} actor1
	 * @param {String} actor2
	 * @return {Number}
	 */
    me.compareActorSpecificity = function(actor1, actor2) {
		var delta = me.getActorSpecificity(actor1) - me.getActorSpecificity(actor2);
		if (delta !== 0) {
			delta = (delta > 0) ? 1 : -1;
		}
		return delta;
    };

    me.getActorSpecificity = function(actorString) {
        var actor = parseActorString(actorString);
		var specificity = 0;
        switch(actor.type) {
            case 'role':
                specificity = 1;
				break;
			case 'special':
				specificity = 2;
				break;
			case 'user':
				specificity = 10;
				break;
        }
		return specificity;
    };


	return me;
})(wsEditorData.roles, wsEditorData.users);

(function ($){

var selectedActor = null;

var itemTemplates = {
	templates: wsEditorData.itemTemplates,

	getTemplateById: function(templateId) {
		if (wsEditorData.itemTemplates.hasOwnProperty(templateId)) {
			return wsEditorData.itemTemplates[templateId];
		} else if ((templateId == '') || (templateId == 'custom')) {
			return wsEditorData.customItemTemplate;
		}
		return null;
	},

	getDefaults: function (templateId) {
		var template = this.getTemplateById(templateId);
		if (template) {
			return template.defaults;
		} else {
			return null;
		}
	},

	getDefaultValue: function (templateId, fieldName) {
		if (fieldName == 'template_id') {
			return null;
		}

		var defaults = this.getDefaults(templateId);
		if (defaults && (typeof defaults[fieldName] != 'undefined')) {
			return defaults[fieldName];
		}
		return null;
	},

	hasDefaultValue: function(templateId, fieldName) {
		return (this.getDefaultValue(templateId, fieldName) !== null);
	}
};

/**
 * Set an input field to a value. The only difference from jQuery.val() is that
 * setting a checkbox to true/false will check/clear it.
 *
 * @param input
 * @param value
 */
function setInputValue(input, value) {
	if (input.attr('type') == 'checkbox'){
        if (value){
            input.attr('checked', 'checked');
        } else {
            input.removeAttr('checked');
        }
    } else {
        input.val(value);
    }
}

/**
 * Get the value of an input field. The only difference from jQuery.val() is that
 * checked/unchecked checkboxes will return true/false.
 *
 * @param input
 * @return {*}
 */
function getInputValue(input) {
	if (input.attr('type') == 'checkbox'){
		return input.is(':checked');
	}
	return input.val();
}


/*
 * Utility function for generating pseudo-random alphanumeric menu IDs.
 * Rationale: Simpler than atomically auto-incrementing or globally unique IDs.
 */
function randomMenuId(prefix, size){
	prefix = (typeof prefix == 'undefined') ? 'custom_item_' : prefix;
	size = (typeof size == 'undefined') ? 5 : size;

    var suffix = "";
    var possible = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";

    for( var i=0; i < size; i++ ) {
        suffix += possible.charAt(Math.floor(Math.random() * possible.length));
    }

    return prefix + suffix;
}

function outputWpMenu(menu){
	var menuCopy = $.extend(true, {}, menu);

	//Remove the current menu data
	$('#ws_menu_box').empty();
	$('#ws_submenu_box').empty();

	//Display the new menu
	var i = 0;
	for (var filename in menuCopy){
		if (!menuCopy.hasOwnProperty(filename)){
			continue;
		}
		outputTopMenu(menuCopy[filename]);
		i++;
	}

	//Automatically select the first top-level menu
	$('#ws_menu_box .ws_menu:first').click();
}

/*
 * Create edit widgets for a top-level menu and its submenus and append them all to the DOM.
 *
 * Inputs :
 *	menu - an object containing menu data
 *	afterNode - if specified, the new menu widget will be inserted after this node. Otherwise,
 *	            it will be added to the end of the list.
 * Outputs :
 *	Object with two fields - 'menu' and 'submenu' - containing the DOM nodes of the created widgets.
 */
function outputTopMenu(menu, afterNode){
	//Create a container for menu items, even if there are none
	var submenu = buildSubmenu(menu.items);

	//Create the menu widget
	var menu_obj = buildMenuItem(menu, true);
	menu_obj.data('submenu_id', submenu.attr('id'));
	updateItemEditor(menu_obj);

	//Display
	submenu.appendTo('#ws_submenu_box');
	if ( (typeof afterNode != 'undefined') && (afterNode != null) ){
		$(afterNode).after(menu_obj);
	} else {
		menu_obj.appendTo('#ws_menu_box');
	}

	return {
		'menu' : menu_obj,
		'submenu' : submenu
	};
}

/*
 * Create and populate a submenu container.
 */
function buildSubmenu(items){
	//Create a container for menu items, even if there are none
	var submenu = $('<div class="ws_submenu" style="display:none;"></div>');
	submenu.attr('id', 'ws-submenu-'+(wsIdCounter++));

	//Only show menus that have items.
	//Skip arrays (with a length) because filled menus are encoded as custom objects.
	var entry = null;
	if (items) {
		$.each(items, function(index, item) {
			entry = buildMenuItem(item, false);
			if ( entry ){
				updateItemEditor(entry);
				submenu.append(entry);
			}
		});
	}

	//Make the submenu sortable
	makeBoxSortable(submenu);

	return submenu;
}

/**
 * Create an edit widget for a menu item.
 *
 * @param {Object} itemData
 * @param {Boolean} [isTopLevel] Specify if this is a top-level menu or a sub-menu item. Defaults to false (= sub-item).
 * @return {*} The created widget as a jQuery object.
 */
function buildMenuItem(itemData, isTopLevel) {
	isTopLevel = (typeof isTopLevel == 'undefined') ? false : isTopLevel;

	//Create the menu HTML
	var item = $('<div></div>')
		.attr('class', "ws_container")
		.attr('id', 'ws-menu-item-' + (wsIdCounter++))
		.data('menu_item', itemData)
		.data('field_editors_created', false);

	item.addClass(isTopLevel ? 'ws_menu' : 'ws_item');
	if ( itemData.separator ) {
		item.addClass('ws_menu_separator');
	}

	//Add a header and a container for property editors (to improve performance
	//the editors themselves are created later, when the user tries to access them
	//for the first time).
	var contents = [];
	contents.push(
		'<div class="ws_item_head">',
			itemData.separator ? '' : '<a class="ws_edit_link"> </a><div class="ws_flag_container"> </div>',
			'<input type="checkbox" class="ws_actor_access_checkbox">',
			'<span class="ws_item_title">',
				((itemData.menu_title != null) ? itemData.menu_title : itemData.defaults.menu_title),
			'&nbsp;</span>',

		'</div>',
		'<div class="ws_editbox" style="display: none;"></div>'
	);
	item.append(contents.join(''));

	//Apply flags based on the item's state
	var flags = ['hidden', 'unused', 'custom'];
	for (var i = 0; i < flags.length; i++) {
		setMenuFlag(item, flags[i], getFieldValue(itemData, flags[i], false));
	}

	if ( isTopLevel && !itemData.separator ){
		//Allow the user to drag menu items to top-level menus
		item.droppable({
			'hoverClass' : 'ws_menu_drop_hover',

			'accept' : (function(thing){
				return thing.hasClass('ws_item');
			}),

			'drop' : (function(event, ui){
				var droppedItemData = readItemState(ui.draggable);
				var new_item = buildMenuItem(droppedItemData, false);
				var submenu = $('#' + item.data('submenu_id'));
				submenu.append(new_item);
				ui.draggable.remove();
			})
		});
	}

	return item;
}

//Editor field spec template.
var baseField = {
	caption : '[No caption]',
    standardCaption : true,
	advanced : false,
	type : 'text',
	defaultValue: '',
	onlyForTopMenus: false,
	addDropdown : false,
	visible: true,

	write: null,
	display: null
};

/*
 * List of all menu fields that have an associated editor
 */
var knownMenuFields = {
	'menu_title' : $.extend({}, baseField, {
		caption : 'Menu title',
		display: function(menuItem, displayValue, input, containerNode) {
			//Update the header as well.
			containerNode.find('.ws_item_title').html(displayValue);
			return displayValue;
		},
		write: function(menuItem, value, input, containerNode) {
			menuItem.menu_title = value;
			containerNode.find('.ws_item_title').html(input.val() + '&nbsp;');
		}
	}),

	'template_id' : $.extend({}, baseField, {
		caption : 'Target page',
		type : 'select',
		options : (function(){
			//Generate name => id mappings for all item templates + the special "Custom" template.
			var itemTemplateIds = {};
			itemTemplateIds[wsEditorData.customItemTemplate.name] = '';
			for (var template_id in wsEditorData.itemTemplates) {
				if (wsEditorData.itemTemplates.hasOwnProperty(template_id)) {
					itemTemplateIds[wsEditorData.itemTemplates[template_id].name] = template_id;
				}
			}
			return itemTemplateIds;
		})(),

		write: function(menuItem, value, input, containerNode) {
			menuItem.template_id = value;
			menuItem.defaults = itemTemplates.getDefaults(menuItem.template_id);
		    menuItem.custom = (menuItem.template_id == '');

		    // The file/URL of non-custom items is read-only and equal to the default
		    // value. Rationale: simplifies menu generation, prevents some user mistakes.
		    if (menuItem.template_id !== '') {
			    menuItem.file = null;
		    }

		    // The new template might not have default values for some of the fields
		    // currently set to null (= "default"). In those cases, we need to make
		    // the current values explicit.
		    containerNode.find('.ws_edit_field').each(function(index, field){
			    field = $(field);
			    var fieldName = field.data('field_name');
			    var isSetToDefault = (menuItem[fieldName] === null);
			    var hasDefaultValue = itemTemplates.hasDefaultValue(menuItem.template_id, fieldName);

			    if (isSetToDefault && !hasDefaultValue) {
				    menuItem[fieldName] = getInputValue(field.find('.ws_field_value'));
			    }
		    });

		    // Display new defaults, etc.
		    updateItemEditor(containerNode);
		}
	}),

	'file' : $.extend({}, baseField, {
		caption: 'URL',
		display: function(menuItem, displayValue, input) {
			// The URL/file field is read-only for default menus. Also, since the "file"
			// field is usually set to a page slug or plugin filename for plugin/hook pages,
			// we display the dynamically generated "url" field here (i.e. the actual URL) instead.
			if (menuItem.template_id !== '') {
				input.attr('readonly', 'readonly');
				displayValue = itemTemplates.getDefaultValue(menuItem.template_id, 'url');
			} else {
				input.removeAttr('readonly');
			}
			return displayValue;
		},

		write: function(menuItem, value, input, containerNode) {
			// A menu must always have a non-empty URL. If the user deletes the current value,
			// reset it to the old value.
			if (value === '') {
				value = menuItem.file;
			}
			// Default menus always point to the default file/URL.
			if (menuItem.template_id !== '') {
				value = null;
			}
			menuItem.file = value;
			updateItemEditor(containerNode);
		}
	}),

	'access_level' : $.extend({}, baseField, {
		caption: 'Permissions',
		defaultValue: 'read',
		type: 'access_editor',

		display: function(menuItem) {
			//Permissions display is a little complicated and could use improvement.
			var requiredCap = getFieldValue(menuItem, 'access_level', '');
			var extraCap = getFieldValue(menuItem, 'extra_capability', '');

			var displayValue = (menuItem.template_id === '') ? '< Custom >' : requiredCap;
			if (extraCap !== '') {
				if (menuItem.template_id === '') {
					displayValue = extraCap;
				} else {
					displayValue = displayValue + '+' + extraCap;
				}
			}

			return displayValue;
		},

		write: function(menuItem) {
			//The required capability can't be directly edited and always equals the default.
			menuItem.access_level = null;
		}
	}),

	'page_title' : $.extend({}, baseField, {
		caption: "Window title",
        standardCaption : true,
		advanced : true
	}),

	'open_in' : $.extend({}, baseField, {
		caption: 'Open in',
		advanced : true,
		type : 'select',
		options : {
			'Same window or tab' : 'same_window',
			'New window' : 'new_window',
			'Frame' : 'iframe'
		},
		defaultValue: 'same_window',
		visible: false
	}),

	'css_class' : $.extend({}, baseField, {
		caption: 'CSS classes',
		advanced : true,
		onlyForTopMenus: true
	}),

	'hookname' : $.extend({}, baseField, {
		caption: 'Hook name',
		advanced : true,
		onlyForTopMenus: true
	}),

	'icon_url' : $.extend({}, baseField, {
		caption: 'Icon URL',
		advanced : true,
		defaultValue: 'div',
		onlyForTopMenus: true
	})
};

/*
 * Create editors for the visible fields of a menu entry and append them to the specified node.
 */
function buildEditboxFields(fieldContainer, entry, isTopLevel){
	isTopLevel = (typeof isTopLevel == 'undefined') ? false : isTopLevel;

	var basicFields = $('<div class="ws_edit_panel ws_basic"></div>').appendTo(fieldContainer);
    var advancedFields = $('<div class="ws_edit_panel ws_advanced"></div>').appendTo(fieldContainer);

    if ( wsEditorData.hideAdvancedSettings ){
    	advancedFields.css('display', 'none');
    }

	for (var field_name in knownMenuFields){
		if (!knownMenuFields.hasOwnProperty(field_name)) {
			continue;
		}

		var fieldSpec = knownMenuFields[field_name];
		if (fieldSpec.onlyForTopMenus && !isTopLevel) {
			continue;
		}

		var field = buildEditboxField(entry, field_name, fieldSpec);
		if (field){
            if (fieldSpec.advanced){
                advancedFields.append(field);
            } else {
                basicFields.append(field);
            }
		}
	}

	//Add a link that shows/hides advanced fields
	fieldContainer.append(
		'<div class="ws_toggle_container"><a href="#" class="ws_toggle_advanced_fields"'+
		(wsEditorData.hideAdvancedSettings ? '' : ' style="display:none;"')+'>'+
		(wsEditorData.hideAdvancedSettings ? wsEditorData.captionShowAdvanced : wsEditorData.captionHideAdvanced)
		+'</a></div>'
	);
}

/*
 * Create an editor for a specified field.
 */
function buildEditboxField(entry, field_name, field_settings){
	if (typeof entry[field_name] === 'undefined') {
		return null; //skip fields this entry doesn't have
	}

	//Build a form field of the appropriate type
	var inputBox = null;
	switch(field_settings.type){
		case 'select':
			inputBox = $('<select class="ws_field_value">');
			var option = null;
			for( var optionTitle in field_settings.options ){
				if (!field_settings.options.hasOwnProperty(optionTitle)) {
					continue;
				}
				option = $('<option>')
					.val(field_settings.options[optionTitle])
					.text(optionTitle);
				option.appendTo(inputBox);
			}
			break;

        case 'checkbox':
            inputBox = $('<label><input type="checkbox" class="ws_field_value"> '+
                field_settings.caption+'</label>'
            );
            break;

		case 'access_editor':
			inputBox = $('<input type="text" class="ws_field_value" readonly="readonly">')
                .add('<input type="button" class="button ws_launch_access_editor" value="Edit...">');
			break;

		case 'text':
		default:
			inputBox = $('<input type="text" class="ws_field_value">');
	}


	var className = "ws_edit_field ws_edit_field-"+field_name;
	if (field_settings.addDropdown){
		className += ' ws_has_dropdown';
	}

	var editField = $('<div>' + (field_settings.standardCaption ? (field_settings.caption+'<br>') : '') + '</div>')
		.attr('class', className)
		.append(inputBox);

	if (field_settings.addDropdown) {
		//Add a dropdown button
		var dropdownId = field_settings.addDropdown;
		editField.append(
			$('<input type="button" value="&#9660;">')
				.addClass('button ws_dropdown_button')
				.attr('tabindex', '-1')
				.data('dropdownId', dropdownId)
		);
	}

	editField
		.append('<img src="' + wsEditorData.imagesUrl + '/transparent16.png" class="ws_reset_button" title="Reset to default value">&nbsp;</img>')
		.data('field_name', field_name);

	if ( !field_settings.visible ){
		editField.css('display', 'none');
	}

	return editField;
}

/**
 * Update the UI elements that that indicate whether the currently selected
 * actor can access a menu item.
 *
 * @param containerNode
 */
function updateActorAccessUi(containerNode) {
	//Update the permissions checkbox & UI
	if (selectedActor != null) {
		var menuItem = containerNode.data('menu_item');
		var hasAccess = actorCanAccessMenu(menuItem, selectedActor);

		var checkbox = containerNode.find('.ws_actor_access_checkbox');
		if (hasAccess) {
			checkbox.attr('checked', 'checked');
		} else {
			checkbox.removeAttr('checked');
		}

		containerNode.toggleClass('ws_is_hidden_for_actor', !hasAccess);
	} else {
		containerNode.removeClass('ws_is_hidden_for_actor');
	}
}

/**
 * Update an edit widget with the current menu item settings.
 *
 * @param containerNode
 */
function updateItemEditor(containerNode) {
	var menuItem = containerNode.data('menu_item');

	//Apply flags based on the item's state.
	var flags = ['hidden', 'unused', 'custom'];
	for (var i = 0; i < flags.length; i++) {
		setMenuFlag(containerNode, flags[i], getFieldValue(menuItem, flags[i], false));
	}

	//Update the permissions checkbox & other actor-specific UI
	updateActorAccessUi(containerNode);

	//Update all input fields with the current values.
	containerNode.find('.ws_edit_field').each(function(index, field) {
		field = $(field);
		var fieldName = field.data('field_name');
		var input = field.find('.ws_field_value').first();

		var hasADefaultValue = itemTemplates.hasDefaultValue(menuItem.template_id, fieldName);
		var defaultValue = itemTemplates.getDefaultValue(menuItem.template_id, fieldName);
		var isDefault = hasADefaultValue && (menuItem[fieldName] === null);

        if (fieldName == 'access_level') {
            isDefault = (getFieldValue(menuItem, 'extra_capability', '') === '') && isEmptyObject(menuItem.grant_access);
        }

		field.toggleClass('ws_has_no_default', !hasADefaultValue);
		field.toggleClass('ws_input_default', isDefault);

		var displayValue = isDefault ? defaultValue : menuItem[fieldName];
		if (knownMenuFields[fieldName].display !== null) {
			displayValue = knownMenuFields[fieldName].display(menuItem, displayValue, input, containerNode);
		}

		if (fieldName == 'access_level') {
			//Permissions display is a little complicated and could use improvement.
			var requiredCap = getFieldValue(menuItem, 'access_level', '');
			var extraCap = getFieldValue(menuItem, 'extra_capability', '');

			displayValue = (menuItem.template_id === '') ? '< Custom >' : requiredCap;
			if (extraCap !== '') {
				if (menuItem.template_id === '') {
					displayValue = extraCap;
				} else {
					displayValue = displayValue + '+' + extraCap;
				}
			}
		}

        setInputValue(input, displayValue);
    });
}

function isEmptyObject(obj) {
    for (var prop in obj) {
        if (obj.hasOwnProperty(prop)) {
            return false;
        }
    }
    return true;
}

/*
 * Get the current value of a single menu field.
 *
 * If the specified field is not set, this function will attempt to retrieve it
 * from the "defaults" property of the menu object. If *that* fails, it will return
 * the value of the optional third argument defaultValue.
 */
function getFieldValue(entry, fieldName, defaultValue){
	if ( (typeof entry[fieldName] === 'undefined') || (entry[fieldName] === null) ) {
		if ( (typeof entry['defaults'] === 'undefined') || (typeof entry['defaults'][fieldName] === 'undefined') ){
			return defaultValue;
		} else {
			return entry.defaults[fieldName];
		}
	} else {
		return entry[fieldName];
	}
}

/*
 * Make a menu container sortable
 */
function makeBoxSortable(menuBox){
	//Make the submenu sortable
	menuBox.sortable({
		items: '> .ws_container',
		cursor: 'move',
		dropOnEmpty: true,
		cancel : '.ws_editbox, .ws_edit_link'
	});
}

/***************************************************************************
                       Parsing & encoding menu inputs
 ***************************************************************************/

/**
 * Encode the current menu structure as JSON
 *
 * @return {String} A JSON-encoded string representing the current menu tree loaded in the editor.
 */
function encodeMenuAsJSON(tree){
	if (typeof tree == 'undefined' || !tree) {
		tree = readMenuTreeState();
	}
	tree.format = {
		name: wsEditorData.menuFormatName,
		version: wsEditorData.menuFormatVersion
	};
	return $.toJSON(tree);
}

function readMenuTreeState(){
	var tree = {};
	var menu_position = 0;

	//Gather all menus and their items
	$('#ws_menu_box .ws_menu').each(function() {
		var menu = readItemState(this, menu_position++);

		//Attach the current menu to the main struct
		var filename = (menu.file !== null)?menu.file:menu.defaults.file;
		tree[filename] = menu;
	});

	return {
		tree: tree
	};
}

/**
 * Extract the current menu item settings from its editor widget.
 *
 * @param itemDiv DOM node containing the editor widget, usually with the .ws_item or .ws_menu class.
 * @param {Integer} [position] Menu item position among its sibling menu items. Defaults to zero.
 * @return {Object} A menu object in the tree format.
 */
function readItemState(itemDiv, position){
	position = (typeof position == 'undefined') ? 0 : position;

	itemDiv = $(itemDiv);
	var item = $.extend({}, wsEditorData.blankMenuItem, itemDiv.data('menu_item'), readAllFields(itemDiv));

	item.defaults = itemDiv.data('menu_item').defaults;

	//Save the position data
	item.position = position;
	item.defaults.position = position; //The real default value will later overwrite this

	item.separator = itemDiv.hasClass('ws_menu_separator');
	item.hidden = menuHasFlag(itemDiv, 'hidden');
	item.custom = menuHasFlag(itemDiv, 'custom');

	//Gather the menu's sub-items, if any
	item.items = [];
	var subMenuId = itemDiv.data('submenu_id');
	if (subMenuId) {
		var itemPosition = 0;
		$('#' + subMenuId).find('.ws_item').each(function () {
			var sub_item = readItemState(this, itemPosition++);
			item.items.push(sub_item);
		});
	}

	return item;
}

/*
 * Extract the values of all menu/item fields present in a container node
 *
 * Inputs:
 *	container - a jQuery collection representing the node to read.
 */
function readAllFields(container){
	if ( !container.hasClass('ws_container') ){
		container = container.closest('.ws_container');
	}

	if ( !container.data('field_editors_created') ){
		return container.data('menu_item');
	}

	var state = {};

	//Iterate over all fields of the item
	container.find('.ws_edit_field').each(function() {
		var field = $(this);

		//Get the name of this field
		var field_name = field.data('field_name');
		//Skip if unnamed
		if (!field_name) return true;

		//Find the field (usually an input or select element).
		var input_box = field.find('.ws_field_value');

		//Save null if default used, custom value otherwise
		if (field.hasClass('ws_input_default')){
			state[field_name] = null;
		} else {
			state[field_name] = getInputValue(input_box);
		}
	});

    //Permission settings are not stored in the visible access_level field (that's just for show),
    //so do not attempt to read them from there.
    state['access_level'] = null;

	return state;
}


/***************************************************************************
 Flag manipulation
 ***************************************************************************/

var item_flags = {
	'custom':'This is a custom menu item',
	'unused':'This item was automatically (re)inserted into your custom menu because it is present in the default WordPress menu',
	'hidden':'This item is hidden'
};

function setMenuFlag(item, flag, state) {
	item = $(item);

	var item_class = 'ws_' + flag;
	var img_class = 'ws_' + flag + '_flag';

	item.toggleClass(item_class, state);
	if (state) {
		//Add the flag image,
		var flag_container = item.find('.ws_flag_container');
		if ( flag_container.find('.' + img_class).length == 0 ){
			flag_container.append('<div class="ws_flag '+img_class+'" title="'+item_flags[flag]+'"></div>');
		}
	} else {
		//Remove the flag image.
		item.find('.' + img_class).remove();
	}
}

function menuHasFlag(item, flag){
	return $(item).hasClass('ws_'+flag);
}

/***********************************************************
                  Capability manipulation
 ************************************************************/

function actorCanAccessMenu(menuItem, actor) {
	if (!$.isPlainObject(menuItem.grant_access)) {
		menuItem.grant_access = {};
	}

	//By default, any actor that has the required cap has access to the menu.
	//Users can override this on a per-menu basis.
	var requiredCap = getFieldValue(menuItem, 'access_level', '< Error: access_level is missing! >');
	var actorHasAccess = false;
	if (menuItem.grant_access.hasOwnProperty(actor)) {
		actorHasAccess = menuItem.grant_access[actor];
	} else {
		actorHasAccess = AmeCapabilityManager.hasCap(actor, requiredCap, menuItem.grant_access);
	}
	return actorHasAccess;
}

function setActorAccess(containerNode, actor, allowAccess) {
	var menuItem = containerNode.data('menu_item');

	//grant_access comes from PHP, which JSON-encodes empty assoc. arrays as arrays.
	//However, we want it to be a dictionary.
	if (!$.isPlainObject(menuItem.grant_access)) {
		menuItem.grant_access = {};
	}

	menuItem.grant_access[actor] = allowAccess;
}

function setSelectedActor(actor) {
	selectedActor = actor;

	//Highlight the actor.
	var actorSelector = $('#ws_actor_selector');
	$('.current', actorSelector).removeClass('current');

	if (selectedActor == null) {
		$('a.ws_no_actor').addClass('current');
	} else {
		$('a[href$="#'+ actor +'"]').addClass('current');
	}

	//There are some UI elements that can be visible or hidden depending on whether an actor is selected.
	$('#ws_menu_editor').toggleClass('ws_is_actor_view', (selectedActor != null));

	//Update the menu item states to indicate whether they're accessible.
	if (selectedActor != null) {
		$('#ws_menu_editor .ws_container').each(function() {
			updateActorAccessUi($(this));
		});
	} else {
		$('#ws_menu_editor .ws_is_hidden_for_actor').removeClass('ws_is_hidden_for_actor');
	}
}

/**
 * Make a menu item inaccessible to everyone except a particular actor.
 *
 * Will not change access settings for actors that are more specific than the input actor.
 * For example, if the input actor is a "role:", this function will only disable other roles,
 * but will leave "user:" actors untouched.
 *
 * @param {Object} menuItem
 * @param {String} actor
 * @return {Object}
 */
function denyAccessForAllExcept(menuItem, actor) {
	//grant_access comes from PHP, which JSON-encodes empty assoc. arrays as arrays.
	//However, we want it to be a dictionary.
	if (!$.isPlainObject(menuItem.grant_access)) {
		menuItem.grant_access = {};
	}

	$.each(wsEditorData.actors, function(otherActor) {
		//If the input actor is more or equally specific...
		if (AmeCapabilityManager.compareActorSpecificity(actor, otherActor) >= 0) {
			menuItem.grant_access[otherActor] = false;
		}
	});
	menuItem.grant_access[actor] = true;
	return menuItem;
}

/***************************************************************************
 Event handlers
 ***************************************************************************/

//Cut & paste stuff
var menu_in_clipboard = null;
var ws_paste_count = 0;

$(document).ready(function(){
	if (wsEditorData.wsMenuEditorPro) {
		knownMenuFields['open_in'].visible = true;
		$('.ws_hide_if_pro').hide();
	}

	//Make the top menu box sortable (we only need to do this once)
    var mainMenuBox = $('#ws_menu_box');
    makeBoxSortable(mainMenuBox);

	/***************************************************************************
	                  Event handlers for editor widgets
	 ***************************************************************************/

	//Highlight the clicked menu item and show it's submenu
	var currentVisibleSubmenu = null;
    $('#ws_menu_editor .ws_container').live('click', (function () {
		var container = $(this);
		if ( container.hasClass('ws_active') ){
			return;
		}

		//Highlight the active item and un-highlight the previous one
		container.addClass('ws_active');
		container.siblings('.ws_active').removeClass('ws_active');
		if ( container.hasClass('ws_menu') ){
			//Show/hide the appropriate submenu
			if ( currentVisibleSubmenu ){
				currentVisibleSubmenu.hide();
			}
			currentVisibleSubmenu = $('#'+container.data('submenu_id')).show();
		}
    }));

    //Show/hide a menu's properties
    $('#ws_menu_editor .ws_edit_link').live('click', (function () {
    	var container = $(this).parents('.ws_container').first();
		var box = container.find('.ws_editbox');

		//For performance, the property editors for each menu are only created
		//when the user tries to access access them for the first time.
		if ( !container.data('field_editors_created') ){
			buildEditboxFields(box, container.data('menu_item'), container.hasClass('ws_menu'));
			container.data('field_editors_created', true);
			updateItemEditor(container);
		}

		$(this).toggleClass('ws_edit_link_expanded');
		//show/hide the editbox
		if ($(this).hasClass('ws_edit_link_expanded')){
			box.show();
		} else {
			//Make sure changes are applied before the menu is collapsed
			box.find('input').change();
			box.hide();
		}
    }));

    //The "Default" button : Reset to default value when clicked
    $('#ws_menu_editor .ws_reset_button').live('click', (function () {
        //Find the field div (it holds the field name)
        var field = $(this).parents('.ws_edit_field');
	    var fieldName = field.data('field_name');
    	//Find the related input field
		var input = field.find('.ws_field_value');

		if ( (input.length > 0) && (field.length > 0) && fieldName ) {
			//Extract the default value from the menu item.
            var containerNode = field.closest('.ws_container');
			var menuItem = containerNode.data('menu_item');
			var defaultValue = itemTemplates.getDefaultValue(menuItem.template_id, fieldName);

            if (fieldName == 'access_level') {
	            //This is a pretty nasty hack.
	            menuItem.grant_access = {};
	            menuItem.extra_capability = null;
                updateItemEditor(containerNode);
            } else {
                //Set the value to the default, if one exists.
                if (defaultValue !== null) {
                    setInputValue(input, defaultValue);
                    field.addClass('ws_input_default');
                }

                //Trigger the change event to ensure consistency
                input.change();
            }

		}
	}));

	//When a field is edited, change it's appearance if it's contents don't match the default value.
    function fieldValueChange(){
        var input = $(this);
		var field = input.parents('.ws_edit_field').first();
	    var fieldName = field.data('field_name');

        if (fieldName == 'access_level') {
            //This field is read-only and can never be directly edited by the user.
            //Ignore spurious change events.
            return;
        }

	    var containerNode = field.parents('.ws_container').first();
	    var menuItem = containerNode.data('menu_item');

	    var oldValue = menuItem[fieldName];
	    var value = getInputValue(input);
	    var defaultValue = itemTemplates.getDefaultValue(menuItem.template_id, fieldName);
        var hasADefaultValue = (defaultValue !== null);

	    //Some fields/templates have no default values.
        field.toggleClass('ws_has_no_default', !hasADefaultValue);
        if (!hasADefaultValue) {
            field.removeClass('ws_input_default');
        }

        if (field.hasClass('ws_input_default') && (value == defaultValue)) {
            value = null; //null = use default.
        }

	    //Ignore changes where the new value is the same as the old one.
	    if (value === oldValue) {
		    return;
	    }

	    //Update the item.
	    if (knownMenuFields[fieldName].write !== null) {
		    knownMenuFields[fieldName].write(menuItem, value, input, containerNode);
	    } else {
		    menuItem[fieldName] = value;
	    }

	    field.toggleClass('ws_input_default', (menuItem[fieldName] === null));
    }
	$('#ws_menu_editor .ws_field_value').live('click', fieldValueChange);
	$('#ws_menu_editor .ws_field_value').live('change', fieldValueChange);

	//Show/hide advanced fields
	$('#ws_menu_editor .ws_toggle_advanced_fields').live('click', function(){
		var self = $(this);
		var advancedFields = self.parents('.ws_container').first().find('.ws_advanced');

		if ( advancedFields.is(':visible') ){
			advancedFields.hide();
			self.text(wsEditorData.captionShowAdvanced);
		} else {
			advancedFields.show();
			self.text(wsEditorData.captionHideAdvanced);
		}

		return false;
	});

	//Allow/forbid items in actor-specific views
	$('#ws_menu_editor input.ws_actor_access_checkbox').live('click', function() {
		if (selectedActor == null) {
			return;
		}

		var checked = $(this).is(':checked');

		var containerNode = $(this).closest('.ws_container');
		setActorAccess(containerNode, selectedActor, checked);
		updateItemEditor(containerNode);

		//Apply the same permissions to sub-menus.
		var subMenuId = containerNode.data('submenu_id');
		if (subMenuId && containerNode.hasClass('ws_menu')) {
			$('.ws_item', '#' + subMenuId).each(function() {
				var node = $(this);
				setActorAccess(node, selectedActor, checked);
				updateItemEditor(node);
			});
		}
	});

	/*************************************************************************
	                  Access editor dialog
	 *************************************************************************/

	var accessEditorState = {
		containerNode : null,
		menuItem: null,
		rowPrefix: 'access_settings_for-'
	};

	$('#ws_menu_access_editor').dialog({
		autoOpen: false,
		closeText: ' ',
		modal: true,
		minHeight: 100,
		draggable: false
	});

	$('.ws_launch_access_editor').live('click', function() {
		var containerNode = $(this).parents('.ws_container').first();
		var menuItem = containerNode.data('menu_item');

		//Write the values of this item to the editor fields.
		var editor = $('#ws_menu_access_editor');

		var requiredCap = getFieldValue(menuItem, 'access_level', '< Error: access_level is missing! >');
		var requiredCapField = editor.find('#ws_required_capability').empty();
		if (menuItem.template_id === '') {
			//Custom items have no required caps, only what users set.
			requiredCapField.empty().append('<em>None</em>');
		} else {
			requiredCapField.text(requiredCap);
		}

		editor.find('#ws_extra_capability').val(getFieldValue(menuItem, 'extra_capability', ''));

		//Generate the actor list.
		var table = editor.find('.ws_role_table_body tbody').empty();
		var alternate = '';
		for(var actor in wsEditorData.actors) {
			if (!wsEditorData.actors.hasOwnProperty(actor)) {
				continue;
			}
			var actorName = wsEditorData.actors[actor];

			var checkboxId = 'allow_' + actor.replace(/[^a-zA-Z0-9_]/g, '_');
			var checkbox = $('<input type="checkbox">').addClass('ws_role_access').attr('id', checkboxId);

			var actorHasAccess = actorCanAccessMenu(menuItem, actor);
			if (actorHasAccess) {
				checkbox.attr('checked', 'checked');
			}

			alternate = (alternate == '') ? 'alternate' : '';

			var row = $('<tr>').data('actor', actor).attr('class', alternate).append(
				$('<td>').addClass('ws_column_role post-title').append(
					$('<label>').attr('for', checkboxId).append(
						$('<strong>').text(actorName)
					)
				),
				$('<td>').addClass('ws_column_access').append(checkbox)
			);

			table.append(row);
		}

		accessEditorState.containerNode = containerNode;
		accessEditorState.menuItem = menuItem;

		//Show/hide the hint about sub menus overriding menu permissions.
		var itemHasSubmenus = containerNode.data('submenu_id') &&
			$('#' + containerNode.data('submenu_id')).find('.ws_item').length > 0;
		var hintIsEnabled = !wsEditorData.showHints.hasOwnProperty('ws_hint_menu_permissions') || wsEditorData.showHints['ws_hint_menu_permissions'];
		if (hintIsEnabled && itemHasSubmenus) {
			$('#ws_hint_menu_permissions').show();
		} else {
			$('#ws_hint_menu_permissions').hide();
		}

		//Warn the user if the required capability == role. Can't make it less restrictive.
		if (requiredCap && AmeCapabilityManager.roleExists(requiredCap)) {
			$('#ws_hardcoded_role_error').show();
			$('#ws_hardcoded_role_name').text(requiredCap);
		} else {
			$('#ws_hardcoded_role_error').hide();
		}

		$('#ws_menu_access_editor').dialog('open');
	});

	$('#ws_save_access_settings').click(function() {
		//Save the new settings.
		accessEditorState.menuItem.extra_capability = $('#ws_extra_capability').val();

		var grantAccess = accessEditorState.menuItem.grant_access;
		if (!$.isPlainObject(grantAccess)) {
			grantAccess = {};
		}
		$('#ws_menu_access_editor .ws_role_table_body tbody tr').each(function() {
			var row = $(this);
			var actor = row.data('actor');
			grantAccess[actor] = row.find('input.ws_role_access').is(':checked');
		});
		accessEditorState.menuItem.grant_access = grantAccess;

		updateItemEditor(accessEditorState.containerNode);
		$('#ws_menu_access_editor').dialog('close');
	});

	/***************************************************************************
		              General dialog handlers
	 ***************************************************************************/

	$('.ws_close_dialog').live('click', function() {
		$(this).parents('.ui-dialog-content').dialog('close');
	});


	/***************************************************************************
	              Drop-down list for combo-box fields
	 ***************************************************************************/

	var capSelectorDropdown = $('#ws_cap_selector');

	//Show/hide the capability drop-down list when the button is clicked
	$('#ws_trigger_capability_dropdown').bind('click', function(){
		var inputBox = $('#ws_extra_capability');

		if (capSelectorDropdown.is(':visible')) {
			capSelectorDropdown.hide();
			return;
		}

		//Pre-select the current capability (will clear selection if there's no match)
		capSelectorDropdown.val(inputBox.val()).show();

		//Move the drop-down near the input box.
		var inputPos = inputBox.offset();
		capSelectorDropdown
			.css({
				position: 'absolute',
				zIndex: 1010 //Must be higher than the permissions dialog overlay.
			})
			.offset({
				left: inputPos.left,
				top : inputPos.top + inputBox.outerHeight()
			}).
			width(inputBox.outerWidth());

		capSelectorDropdown.focus();
	});

	//Also show it when the user presses the down arrow in the input field (doesn't work in Opera).
	$('#ws_extra_capability').bind('keyup', function(event){
		if ( event.which == 40 ){
			$('#ws_trigger_capability_dropdown').click();
		}
	});

	//Event handlers for the drop-down lists themselves
	var dropdownNodes = $('.ws_dropdown');

	// Hide capability drop-down when it loses focus.
	// The timeout prevents a situation where the list is hidden and immediately displayed
	// again because the user clicked the trigger button while it was visible.
	dropdownNodes.blur(function(){
		setTimeout(function() { capSelectorDropdown.hide(); }, 100);
	});

	dropdownNodes.keydown(function(event){
		var inputBox = $('#ws_extra_capability');

		//Hide it when the user presses Esc
		if ( event.which == 27 ){
			capSelectorDropdown.hide();
			inputBox.focus();

		//Select an item & hide the list when the user presses Enter or Tab
		} else if ( (event.which == 13) || (event.which == 9) ){
			capSelectorDropdown.hide();

			if ( capSelectorDropdown.val() ){
				inputBox.val(capSelectorDropdown.val());
				inputBox.change();
			}

			inputBox.focus();

			event.preventDefault();
		}
	});

	//Eat Tab keys to prevent focus theft. Required to make the "select item on Tab" thing work.
	dropdownNodes.keyup(function(event){
		if ( event.which == 9 ){
			event.preventDefault();
		}
	});


	//Update the input & hide the list when an option is clicked
	dropdownNodes.click(function(){
		if ( capSelectorDropdown.val() ){
			capSelectorDropdown.hide();
			$('#ws_extra_capability').val(capSelectorDropdown.val()).change().focus();
		}
	});

	//Highlight an option when the user mouses over it (doesn't work in IE)
	dropdownNodes.mousemove(function(event){
		if ( !event.target ){
			return;
		}

		var option = $(event.target);
		if ( !option.attr('selected') && option.attr('value')){
			option.attr('selected', 'selected');
		}
	});


    /*************************************************************************
	                           Menu toolbar buttons
	 *************************************************************************/
	//Show/Hide menu
	$('#ws_hide_menu').click(function () {
		//Get the selected menu
		var selection = $('#ws_menu_box .ws_active');
		if (!selection.length) return;

		//Mark the menu as hidden/visible
		var menuItem = selection.data('menu_item');
		menuItem.hidden = !menuItem.hidden;
		setMenuFlag(selection, 'hidden', menuItem.hidden);

		//Also mark all of it's submenus as hidden/visible
		$('#' + selection.data('submenu_id') + ' .ws_item').each(function(){
			var submenuItem = $(this).data('menu_item');
			submenuItem.hidden = menuItem.hidden;
			setMenuFlag(this, 'hidden', submenuItem.hidden);
		});
	});

	//Delete menu
	$('#ws_delete_menu').click(function () {
		//Get the selected menu
		var selection = $('#ws_menu_box .ws_active');
		if (!selection.length) return;

		if (confirm('Delete this menu?')){
			//Delete the submenu first
			$('#' + selection.data('submenu_id')).remove();
			//Delete the menu
			selection.remove();
		}
	});

	//Copy menu
	$('#ws_copy_menu').click(function () {
		//Get the selected menu
		var selection = $('#ws_menu_box .ws_active');
		if (!selection.length) return;

		//Store a copy of the current menu state in clipboard
		menu_in_clipboard = readItemState(selection);
	});

	//Cut menu
	$('#ws_cut_menu').click(function () {
		//Get the selected menu
		var selection = $('#ws_menu_box .ws_active');
		if (!selection.length) return;

		//Store a copy of the current menu state in clipboard
		menu_in_clipboard = readItemState(selection);

		//Remove the original menu and submenu
		$('#'+selection.data('submenu_id')).remove();
		selection.remove();
	});

	//Paste menu
	function pasteMenu(menu, afterMenu) {
		//The user shouldn't need to worry about giving separators a unique filename.
		if (menu.separator) {
			menu.defaults.file = randomMenuId('separator_');
		}

		//If we're pasting from a sub-menu, we may need to fix some properties
		//that are blank for sub-menu items but required for top-level menus.
		if (getFieldValue(menu, 'css_class', '') == '') {
			menu.css_class = 'menu-top';
		}
		if (getFieldValue(menu, 'icon_url', '') == '') {
			menu.icon_url = 'images/generic.png';
		}
		if (getFieldValue(menu, 'hookname', '') == '') {
			menu.hookname = randomMenuId();
		}

		//Paste the menu after the specified one, or at the end of the list.
		if (afterMenu) {
			outputTopMenu(menu, afterMenu);
		} else {
			outputTopMenu(menu);
		}
	}

	$('#ws_paste_menu').click(function () {
		//Check if anything has been copied/cut
		if (!menu_in_clipboard) return;

		var menu = $.extend(true, {}, menu_in_clipboard);

		//Get the selected menu
		var selection = $('#ws_menu_box .ws_active');
		//Paste the menu after the selection.
		pasteMenu(menu, (selection.length > 0) ? selection : null);
	});

	//New menu
	$('#ws_new_menu').click(function () {
		ws_paste_count++;

		//The new menu starts out rather bare
		var randomId = randomMenuId();
		var menu = $.extend({}, wsEditorData.blankMenuItem, {
			custom: true, //Important : flag the new menu as custom, or it won't show up after saving.
			template_id : '',
			menu_title : 'Custom Menu ' + ws_paste_count,
			file : randomId,
			items: [],
			defaults: itemTemplates.getDefaults('')
		});

		//Make it accessible only to the current actor if one is selected.
		if (selectedActor != null) {
			denyAccessForAllExcept(menu, selectedActor);
		}

		//Insert the new menu
		var selection = $('#ws_menu_box .ws_active');
		var result = outputTopMenu(menu, (selection.length > 0) ? selection : null);

		//The menus's editbox is always open
		result.menu.find('.ws_edit_link').click();
	});

	//New separator
	$('#ws_new_separator, #ws_new_submenu_separator').click(function () {
		ws_paste_count++;

		//The new menu starts out rather bare
		var randomId = randomMenuId('separator_');
		var menu = $.extend(true, {}, wsEditorData.blankMenuItem, {
			separator: true, //Flag as a separator
			custom: false,   //Separators don't need to flagged as custom to be retained.
			items: [],
			defaults: {
				separator: true,
				css_class : 'wp-menu-separator',
				access_level : 'read',
				file : randomId,
				hookname : randomId
			}
		});

		if ( $(this).attr('id').indexOf('submenu') == -1 ) {
			//Insert in the top-level menu.
			var selection = $('#ws_menu_box .ws_active');
			outputTopMenu(menu, (selection.length > 0) ? selection : null);
		} else {
			//Insert in the currently visible submenu.
			pasteItem(menu);
		}
	});

	/*************************************************************************
	                          Item toolbar buttons
	 *************************************************************************/
	//Show/Hide item
	$('#ws_hide_item').click(function () {
		//Get the selected item
		var selection = $('#ws_submenu_box .ws_submenu:visible .ws_active');
		if (!selection.length) return;

		//Mark the item as hidden/visible
		var menuItem = selection.data('menu_item');
		menuItem.hidden = !menuItem.hidden;
		setMenuFlag(selection, 'hidden', menuItem.hidden);
	});

	//Delete menu
	$('#ws_delete_item').click(function () {
		//Get the selected menu
		var selection = $('#ws_submenu_box .ws_submenu:visible .ws_active');
		if (!selection.length) return;

		if (confirm('Delete this menu item?')){
			//Delete the item
			selection.remove();
		}
	});

	//Copy item
	$('#ws_copy_item').click(function () {
		//Get the selected item
		var selection = $('#ws_submenu_box .ws_submenu:visible .ws_active');
		if (!selection.length) return;

		//Store a copy of item state in the clipboard
		menu_in_clipboard = readItemState(selection);
	});

	//Cut item
	$('#ws_cut_item').click(function () {
		//Get the selected item
		var selection = $('#ws_submenu_box .ws_submenu:visible .ws_active');
		if (!selection.length) return;

		//Store a copy of item state in the clipboard
		menu_in_clipboard = readItemState(selection);

		//Remove the original item
		selection.remove();
	});

	//Paste item
	function pasteItem(item) {
		//We're pasting this item into a sub-menu, so it can't have a sub-menu of its own.
		//Instead, any sub-menu items belonging to this item will be pasted after the item.
		var newItems = [];
		for (var file in item.items) {
			if (item.items.hasOwnProperty(file)) {
				newItems.push(buildMenuItem(item.items[file], false));
			}
		}
		item.items = [];

		newItems.unshift(buildMenuItem(item, false));

		//Get the selected menu
		var visibleSubmenu = $('#ws_submenu_box .ws_submenu:visible');
		var selection = visibleSubmenu.find('.ws_active');
		for(var i = 0; i < newItems.length; i++) {
			if (selection.length > 0) {
				//If an item is selected add the pasted items after it
				selection.after(newItems[i]);
			} else {
				//Otherwise add the pasted items at the end
				visibleSubmenu.append(newItems[i]);
			}
			newItems[i].show();
		}
	}

	$('#ws_paste_item').click(function () {
		//Check if anything has been copied/cut
		if (!menu_in_clipboard) return;

		//You can only add separators to submenus in the Pro version.
		if ( menu_in_clipboard.separator && !wsEditorData.wsMenuEditorPro ) {
			return;
		}

		//Paste it.
		var item = $.extend(true, {}, menu_in_clipboard);
		pasteItem(item);
	});

	//New item
	$('#ws_new_item').click(function () {
		if ($('.ws_submenu:visible').length < 1) {
			return; //Abort if no submenu visible
		}

		ws_paste_count++;

		var entry = $.extend({}, wsEditorData.blankMenuItem, {
			custom: true,
			template_id : '',
			menu_title : 'Custom Item ' + ws_paste_count,
			file : randomMenuId(),
			items: [],
			defaults: itemTemplates.getDefaults('')
		});

		//Make it accessible to only the currently selected actor.
		if (selectedActor != null) {
			denyAccessForAllExcept(entry, selectedActor);
		}

		var menu = buildMenuItem(entry);
		updateItemEditor(menu);

		//Insert the item into the currently open submenu.
		var visibleSubmenu = $('#ws_submenu_box .ws_submenu:visible');
		var selection = visibleSubmenu.find('.ws_active');
		if (selection.length > 0) {
			selection.after(menu);
		} else {
			visibleSubmenu.append(menu);
		}

		//The items's editbox is always open
		menu.find('.ws_edit_link').click();
	});

	function compareMenus(a, b){
		function jsTrim(str){
			return str.replace(/^\s+|\s+$/g, "");
		}

		var aTitle = jsTrim( $(a).find('.ws_item_title').text() );
		var bTitle = jsTrim( $(b).find('.ws_item_title').text() );

		aTitle = aTitle.toLowerCase();
		bTitle = bTitle.toLowerCase();

		return aTitle > bTitle ? 1 : -1;
	}

	//Sort items in ascending order
	$('#ws_sort_ascending').click(function () {
		var submenu = $('#ws_submenu_box .ws_submenu:visible');
		if (submenu.length < 1) {
			return; //Abort if no submenu visible
		}

		submenu.find('.ws_container').sort(compareMenus);
	});

	//Sort items in descending order
	$('#ws_sort_descending').click(function () {
		var submenu = $('#ws_submenu_box .ws_submenu:visible');
		if (submenu.length < 1) {
			return; //Abort if no submenu visible
		}

		submenu.find('.ws_container').sort((function(a, b){
			return -compareMenus(a, b);
		}));
	});

	//==============================================
	//				Main buttons
	//==============================================

	//Save Changes - encode the current menu as JSON and save
	$('#ws_save_menu').click(function () {
		var tree = readMenuTreeState();

		function findItemByTemplateId(items, templateId) {
			var foundItem = null;

			$.each(items, function(index, item) {
				if (item.template_id == templateId) {
					foundItem = item;
					return false;
				}
				if (item.hasOwnProperty('items') && (item.items.length > 0)) {
					foundItem = findItemByTemplateId(item.items, templateId);
					if (foundItem != null) {
						return false;
					}
				}
			});

			return foundItem;
		}

		//Abort the save if it would make the editor inaccessible.
        if (wsEditorData.wsMenuEditorPro) {
            var myMenuItem = findItemByTemplateId(tree.tree, 'options-general.php>menu_editor');
            if (myMenuItem == null) {
                //This is OK - the missing menu item will be re-inserted automatically.
            } else if (!actorCanAccessMenu(myMenuItem, 'user:' + wsEditorData.currentUserLogin)) {
                alert("Error: This configuration would make you unable to access the menu editor!");
                return;
            }
        }

		var data = encodeMenuAsJSON(tree);
		$('#ws_data').val(data);
		$('#ws_data_length').val(data.length);
		$('#ws_main_form').submit();
	});

	//Load default menu - load the default WordPress menu
	$('#ws_load_menu').click(function () {
		if (confirm('Are you sure you want to load the default WordPress menu?')){
			outputWpMenu(defaultMenu.tree);
		}
	});

	//Reset menu - re-load the custom menu. Discards any changes made by user.
	$('#ws_reset_menu').click(function () {
		if (confirm('Undo all changes made in the current editing session?')){
			outputWpMenu(customMenu.tree);
		}
	});

	//Export menu - download the current menu as a file
	$('#export_dialog').dialog({
		autoOpen: false,
		closeText: ' ',
		modal: true,
		minHeight: 100
	});

	$('#ws_export_menu').click(function(){
		var button = $(this);
		button.attr('disabled', 'disabled');
		button.val('Exporting...');

		$('#export_complete_notice, #download_menu_button').hide();
		$('#export_progress_notice').show();
		$('#export_dialog').dialog('open');

		//Encode and store the menu for download
		var exportData = encodeMenuAsJSON();

		$.post(
			wsEditorData.adminAjaxUrl,
			{
				'data' : exportData,
				'action' : 'export_custom_menu',
				'_ajax_nonce' : wsEditorData.exportMenuNonce
			},
			function(data){
				button.val('Export');
				button.removeAttr('disabled');

				if ( typeof data['error'] != 'undefined' ){
					$('#export_dialog').dialog('close');
					alert(data.error);
				}

				if ( (typeof data['download_url'] != 'undefined') && data.download_url ){
					//window.location = data.download_url;
					$('#download_menu_button').attr('href', data.download_url);
					$('#export_progress_notice').hide();
					$('#export_complete_notice, #download_menu_button').show();
				}
			},
			'json'
		);
	});

	$('#ws_cancel_export').click(function(){
		$('#export_dialog').dialog('close');
	});

	$('#download_menu_button').click(function(){
		$('#export_dialog').dialog('close');
	});

	//Import menu - upload an exported menu and show it in the editor
	$('#import_dialog').dialog({
		autoOpen: false,
		closeText: ' ',
		modal: true
	});

	$('#ws_cancel_import').click(function(){
		$('#import_dialog').dialog('close');
	});

	$('#ws_import_menu').click(function(){
		$('#import_progress_notice, #import_progress_notice2, #import_complete_notice').hide();
		$('#import_menu_form').resetForm();
		//The "Upload" button is disabled until the user selects a file
		$('#ws_start_import').attr('disabled', 'disabled');

		$('#import_dialog .hide-when-uploading').show();

		$('#import_dialog').dialog('open');
	});

	$('#import_file_selector').change(function(){
		if ( $(this).val() ){
			$('#ws_start_import').removeAttr('disabled');
		} else {
			$('#ws_start_import').attr('disabled', 'disabled');
		}
	});

	//AJAXify the upload form
	//noinspection JSUnusedGlobalSymbols
	$('#import_menu_form').ajaxForm({
		dataType : 'json',
		beforeSubmit: function(formData) {

			//Check if the user has selected a file
			for(var i = 0; i < formData.length; i++){
				if ( formData[i].name == 'menu' ){
					if ( (typeof formData[i]['value'] == 'undefined') || !formData[i]['value']){
						alert('Select a file first!');
						return false;
					}
				}
			}

			$('#import_dialog .hide-when-uploading').hide();
			$('#import_progress_notice').show();

			$('#ws_start_import').attr('disabled', 'disabled');
		},
		success: function(data){
			if ( !$('#import_dialog').dialog('isOpen') ){
				//Whoops, the user closed the dialog while the upload was in progress.
				//Discard the response silently.
				return;
			}

			if ( typeof data['error'] != 'undefined' ){
				alert(data.error);
				//Let the user try again
				$('#import_menu_form').resetForm();
				$('#import_dialog .hide-when-uploading').show();
			}
			$('#import_progress_notice').hide();

			if ( (typeof data['tree'] != 'undefined') && data.tree ){
				//Whee, we got back a (seemingly) valid menu. A veritable miracle!
				//Lets load it into the editor.
				$('#import_progress_notice2').show();
				outputWpMenu(data.tree);
				$('#import_progress_notice2').hide();
				//Display a success notice, then automatically close the window after a few moments
				$('#import_complete_notice').show();
				setTimeout((function(){
					//Close the import dialog
					$('#import_dialog').dialog('close');
				}), 500);
			}

		}
	});

	/*************************************************************************
	                 Drag & drop items between menu levels
	 *************************************************************************/

	if (wsEditorData.wsMenuEditorPro) {
		//Allow the user to drag sub-menu items to the top level.
		$('#ws_top_menu_dropzone').droppable({
			'hoverClass' : 'ws_dropzone_hover',

			'accept' : (function(thing){
				return thing.hasClass('ws_item');
			}),

			'drop' : (function(event, ui){
				var droppedItemData = readItemState(ui.draggable);
				pasteMenu(droppedItemData);
				ui.draggable.remove();
			})
		});

		//...and to drag top level menus to a sub-menu.
		$('#ws_sub_menu_dropzone').droppable({
			'hoverClass' : 'ws_dropzone_hover',

			'accept' : (function(thing){
				var visibleSubmenu = $('#ws_submenu_box .ws_submenu:visible');
				return (
					//Accept top-level menus
					thing.hasClass('ws_menu') &&

					//Prevent users from dropping a menu on its own sub-menu.
					(visibleSubmenu.attr('id') != thing.data('submenu_id'))
				);
			}),

			'drop' : (function(event, ui){
				var droppedItemData = readItemState(ui.draggable);
				pasteItem(droppedItemData);
				ui.draggable.remove();
			})
		});
	}


	//Set up tooltips
	$('.ws_tooltip_trigger').qtip();

	//Flag closed hints as hidden by sending the appropriate AJAX request to the backend.
	$('.ws_hint_close').click(function() {
		var hint = $(this).parents('.ws_hint').first();
		hint.hide();
		wsEditorData.showHints[hint.attr('id')] = false;
		$.post(
			wsEditorData.adminAjaxUrl,
			{
				'action' : 'ws_ame_hide_hint',
				'hint' : hint.attr('id')
			}
		);
	});


	/******************************************************************
	                           Actor views
	 ******************************************************************/

	//Build the list of available actors
	var actorSelector = $('#ws_actor_selector').empty();
	actorSelector.append('<li><a href="#" class="current ws_no_actor">All</a></li>');
	selectedActor = null;

	if (wsEditorData.wsMenuEditorPro) {
		for(var actor in wsEditorData.actors) {
			if (!wsEditorData.actors.hasOwnProperty(actor)) {
				continue;
			}
			actorSelector.append(
				$('<li></li>').append(
					$('<a></a>')
						.attr('href', '#' + actor)
						.text(wsEditorData.actors[actor])
				)
			);
		}
		actorSelector.show();
	}

	$('li a', actorSelector).click(function(event) {
		var actor = $(this).attr('href').substring(1);
		if (actor == '') {
			actor = null;
		}

		setSelectedActor(actor);

		event.preventDefault();
	});

	//Finally, show the menu
    outputWpMenu(customMenu.tree);
  });

})(jQuery);

//==============================================
//				Screen options
//==============================================

jQuery(function($){
	var screenOptions = $('#ws-ame-screen-meta-contents');
	var checkbox = screenOptions.find('#ws-hide-advanced-settings');

	if ( wsEditorData.hideAdvancedSettings ){
		checkbox.attr('checked', 'checked');
	} else {
		checkbox.removeAttr('checked');
	}

	//Update editor state when settings change
	checkbox.click(function(){
		wsEditorData.hideAdvancedSettings = $(this).attr('checked'); //Using '$(this)' instead of 'checkbox' due to jQuery bugs
		if ( wsEditorData.hideAdvancedSettings ){
			$('#ws_menu_editor div.ws_advanced').hide();
			$('#ws_menu_editor a.ws_toggle_advanced_fields').text(wsEditorData.captionShowAdvanced).show();
		} else {
			$('#ws_menu_editor div.ws_advanced').show();
			$('#ws_menu_editor a.ws_toggle_advanced_fields').text(wsEditorData.captionHideAdvanced).hide();
		}

		$.post(
			wsEditorData.adminAjaxUrl,
			{
				'action' : 'ws_ame_save_screen_options',
				'hide_advanced_settings' : wsEditorData.hideAdvancedSettings ? 1 : 0,
				'_ajax_nonce' : wsEditorData.hideAdvancedSettingsNonce
			}
		);
	});

	//Move our options into the screen meta panel
	$('#adv-settings').empty().append(screenOptions.show());
});