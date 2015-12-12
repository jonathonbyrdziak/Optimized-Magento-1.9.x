<!--
/**
 * RocketWeb
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 *
 * @category   RocketWeb
 * @package    RocketWeb_UpsAddressTypeValidator
 * @copyright  Copyright (c) 2012 RocketWeb (http://rocketweb.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * @author     RocketWeb
 */
-->
var RocketWeb = RocketWeb || {}

RocketWeb.Address = (function(){
	return {
		// Overlay
		DEFAULT_OVERLAY : 'loading-mask',
		// Validate address
		validate: function(formid, url, type) {
			if(!url || typeof url == 'undefined' || url.length == 0)
			{
				alert("The URL parameter to the controller is missing.");
				return false;
			}
		    // Check if the form id is set
		    var is_form 	=  (typeof formid == 'undefined' || formid.length == 0 ? false : true);
		    // Check if the form id is set
		    var has_type 	=  (typeof type == 'undefined' || type.length == 0 ? false : true);
		    // If not
		    if(!is_form || !has_type) {
		    	alert("The id of the form or the 'type' of the form parameter is missing.")
		        // stop
		        return false;
		    }
		    // Load the overlay
		    var loading = document.getElementById(RocketWeb.Address.DEFAULT_OVERLAY);
		    // Check for the number of new addresses
		    // are bring added
		    var _new_addreses 			= RocketWeb.Address.matchIds('new_item');
		    // Check for existing customer addresses
		    var _existing_addresses		= RocketWeb.Address.matchIds('address_item_');
		    // Default new address item to 1 e.g. address[new_item1][street][0]
		    var _current_new_item 		= 'new_item1';
		    // current exting customer address e.g. address[address_item_1][street][0]
		   	var _current_existing_item 	= 'address_item_1';
		    // And its' associated form
		    var _form_number    		= '1';
		    // the name param e.g. address[1][street][0]
		    var _second_name_param		= '1';
		    // Loop through all the existing addresses are being
		    // added
		    _existing_addresses.each(function(e){
		        // Get the li item associated with it
		        var li = document.getElementById(e);
		        // If the li item has class on (meaning that
		        // the user clicked it)
		        if(li.className == 'on')
		        {
		            // Set the current item to this one
		            _current_existing_item 	= e;
		            // And its' associated form id
		            _form_number    	= li.id.replace('address_item_', '');
		            // second name param
		            _second_name_param	= _form_number;
		        }
		    });
		    // Loop through all the new addresses are being
		    // added
		    _new_addreses.each(function(e){
		        // Get the li item associated with it
		        var li = document.getElementById(e);
		        // If the li item has class on (meaning that
		        // the user clicked it)
		        if(li.className == 'on')
		        {
		            // Set the current item to this one
		            _current_new_item 	= e;
		            // And its' associated form id
		            _form_number    	= li.id.replace('new_item', '');
		            // second item name param
		            _second_name_param		= '_item' + _form_number;
		        }
		    });
		    // If the type page is order
		    if(type == 'order')
		    {
		    	var _orderform			= formid.replace('order-', '');
		    	// Second name param is billing
		    	_second_name_param		= _orderform;
		    }
		    // The name string that comes before the actual field name e.g. type[_second_name_param][city]
		    var _name_selector_pre		= type + '[' + _second_name_param + ']';
		    // Street address line 1
		    var _sa0 	= document.getElementsByName(_name_selector_pre + '[street][0]')[0];
		    // Street address line 2
		    var _sa1 	= document.getElementsByName(_name_selector_pre + '[street][1]')[0];
		    // Zip code
		    var _zip    = document.getElementsByName(_name_selector_pre + '[postcode]')[0];
		    // City
		    var _city   = document.getElementsByName(_name_selector_pre + '[city]')[0];
		    // State
		    var _state  = document.getElementsByName(_name_selector_pre + '[region_id]')[0];
		    // Country
		    var _country= document.getElementsByName(_name_selector_pre + '[country_id]')[0];
		    // URL to request
		    var _base   = url;
		    // Init query string
		    var query 	= [];
		    // If there is street address 1 or 2
		    if(_sa0 !== null || _sa1 !== null)
		    {   
		        var _sa0_value  = _sa0.value;
		        var _sa1_value  = _sa1.value;
		        // Concat the two streets
		        var _street 	= _sa0_value + " " + _sa1_value;
		        query.push('_street=' + encodeURIComponent(_street));
		    }
		    // If ZIP is set
		    if(_zip)
		    {
		        query.push('_zip=' + encodeURIComponent(_zip.value));
		    }
		    // If city is set
		    if(_city)
		    {
		        query.push('_city=' + encodeURIComponent(_city.value));
		    }
		    // If state is set
		    if(_state)
		    {
		        query.push('_state=' + encodeURIComponent(_state.value));
		    } else {
		        if(document.getElementById('_item1region'))
		        {
		            query.push('_state=' + encodeURIComponent(document.getElementById('_item1region').value));
		        }
		    }
		    // If country is set
		    if(_country)
		    {
		    	// Put it in the request array
		        query.push('_country=' + encodeURIComponent(_country.value));
		    }
		    // Build the url with query string
		    var _url    = _base + "?" + query.join('&');
		    // Begin the request
		    new Ajax.Request(_url, {
		    	// On request start
		    	onLoading: function() {
		    		// show overlay
		    		loading.style.display 		= 'block';
		    	},
		    	// When the request is successfull
			  	onSuccess: function(response) {
			  		// Get the message json element from response
			    	var _message = response.responseText.evalJSON().message;
			  		// Put the message into global message container
			  		document.getElementById("messages").innerHTML = _message;
			  	},
			  	// When the request is complete
			  	onComplete: function()
			  	{
			  		// Hide overlay
			  		loading.style.display = 'none';
			  	}
			});
		},
		// Matches all the items that have ids matchin
		// a particular string
		matchIds:function(str){
		    var nodes= document.body.getElementsByTagName('*'),
		    L= nodes.length, A= [], temp;
		    while(L){
		        temp= nodes[--L].id || '';
		        if(temp.indexOf(str) == 0) A.push(temp);
		    }
		    return A;
		}
	}
})();