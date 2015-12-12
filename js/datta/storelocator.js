/* 
 /**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    design
 * @package     Datta_StoreLocator
 * @created     Dattatray Yadav  2nd Dec,2013 3:58pm
 * @author      Clarion magento team<Dattatray Yadav>   
 * @purpose     Store locator google api js
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license      http://opensource.org/licenses/osl-3.0.php  Open Software License
 */ 
  
var markerVisitor = false;
function initialize(){
	bounds = new google.maps.LatLngBounds();
	var mapOption = {zoom: 22, mapTypeId: google.maps.MapTypeId.ROADMAP, disableDefaultUI : true , zoomControl : true};
	map = new google.maps.Map(document.getElementById('map_canvas'), mapOption);
    direction = new google.maps.DirectionsRenderer({
        map                 : map,
        panel               : document.getElementById('panel'), 
        suppressMarkers     : true
    });
    autocomplete = new google.maps.places.Autocomplete($("address"));
    google.maps.event.addListener(autocomplete, 'place_changed', autocompleteCallback);

    infoWindow = new google.maps.InfoWindow();
	initGeoloc();
	initStores();
}   
function getItineraire(lat, lng ){
    var destination = new google.maps.LatLng(lat, lng);
    if(markerVisitor){
    var origin = markerVisitor.getPosition();
    var request = {
        origin      : origin,
        destination : destination,
        travelMode  : google.maps.DirectionsTravelMode.DRIVING
    }
    var directionsService = new google.maps.DirectionsService();
    directionsService.route(request, function(response, status){
        if(status == google.maps.DirectionsStatus.OK){
            direction.setDirections(response);
        }
    });
    }

}   
function getMyLatLng(){
    if(navigator.geolocation){
        navigator.geolocation.getCurrentPosition(function(position){
            var latlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
        }, erreurPosition);
        return latlng;
    }
} 
function loadScript() {
	  var script = document.createElement("script");
	  script.type = "text/javascript";
	  script.src = gmapUrl;
	  document.body.appendChild(script);
}
function initGeoloc(){
	if(apiSensor){
	    if(navigator.geolocation) {
	        survId = navigator.geolocation.getCurrentPosition(maPosition,erreurPosition);
	    }
	}
} 
function maPosition(position) {
    latlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
    markerPosition(latlng);
} 
function erreurPosition(error) {
    var info = "Erreur lors de la géolocalisation : ";
    switch(error.code) {
        case error.TIMEOUT:
            info += "Timeout !";
            break;
        case error.PERMISSION_DENIED:
            info += "Vous n’avez pas donné la permission";
            break;
        case error.POSITION_UNAVAILABLE:
            info += "La position n’a pu être déterminée";
            break;
        case error.UNKNOWN_ERROR:
            info += "Erreur inconnue";
            break;
    }
}    
function initStores(){
	markers = new Array();
     var shape = {
      coord: [1, 1, 1, 20, 18, 20, 18 , 1],
      type: 'poly'
  };
	for(i=0; i< stores.items.length; i++){
		var latLng =  new google.maps.LatLng(stores.items[i].lat, stores.items[i].long);
		bounds.extend(latLng);
		if(stores.items[i].marker){
			var imgMarker = new google.maps.MarkerImage(pathMarker+stores.items[i].marker);
		}else{
			if(defaultMarker){
				var imgMarker = new google.maps.MarkerImage(pathMarker+defaultMarker);
			}else{
				var imgMarker = '';
			}
		}
        
	    markers[i] = new google.maps.Marker({position: latLng, icon: imgMarker,map: map,shape: shape, store: stores.items[i]});
	    google.maps.event.addListener(markers[i], 'click', openWindowInfo);
        $('store'+stores.items[i].entity_id).observe('click', openWindowInfo.bind(markers[i]));
	}
	map.fitBounds(bounds);
    map.panToBounds(bounds);
}   
function openWindowInfo(){  
    if(!this.store.image){
        this.store.image = defaultImage;
    }
	var content = 	'<div class="store-info"><div class="store-image-div-infoWindow"><img src="'+this.store.image +'" alt="'+this.store.name+'"class="store-image-infoWindow"/></div><div class="store-name-infoWindow"><h3>' + this.store.name + '</h3>'
     + this.store.address + '<br>'
     + this.store.zipcode+' '+ this.store.city +' <br>'+ this.store.country_id + '<br>';

    if(this.store.phone){
        content += 'Phone : '+ this.store.phone + '<br>'
    }

    if(this.store.fax){
        content += 'Fax : '+  this.store.fax + '<br>'
    }
    content += "</div>";
    if(this.store.description){
        content += '<div class="store-description">'+ this.store.description+'</div>';
    }

    if(markerVisitor && directionEnable){
        content += '<span onclick="getItineraire('+this.store.lat+','+ this.store.long+')" class="span-geoloc">'+estimateDirectionLabel+'</span></div></div>';
    }

    infoWindow.setContent(content);
    infoWindow.open(map,this); 
}

function autocompleteCallback(){
    var place = this.getPlace();
    position = place.geometry.location;
    var latLng = new google.maps.LatLng(position.lat(), position.lng());
    markerPosition(latLng);
}

function markerPosition(latlng){
    bounds.extend(latlng);
    if(markerVisitor){
        markerVisitor.setPosition(latlng);
    }else{
        markerVisitor = new google.maps.Marker({
            position: latlng,
            map: map, 
            title:"Vous êtes ici"
        });
    }
    map.panTo(latlng);
    map.setZoom(12);
}