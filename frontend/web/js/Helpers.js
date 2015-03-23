/**
 * Helpers.js
 */

var marker = null;
var keyStr = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';

function processMapClick(map, event, id)
{
	if(marker != null)
		marker.setMap(null);

	$('#' + id + '-latitude').val(event.latLng.lat());
	$('#' + id + '-longitude').val(event.latLng.lng());

	marker = new google.maps.Marker({
	  position: event.latLng,
	  map: map,
  });
}

function base64_encode(string) 
{
	string = escape(string);
	var output = "";
	var chr1, chr2, chr3 = "";
	var enc1, enc2, enc3, enc4 = "";
	var i = 0;

	do {
		chr1 = string.charCodeAt(i++);
		chr2 = string.charCodeAt(i++);
		chr3 = string.charCodeAt(i++);

		enc1 = chr1 >> 2;
		enc2 = ((chr1 & 3) << 4) | (chr2 >> 4);
		enc3 = ((chr2 & 15) << 2) | (chr3 >> 6);
		enc4 = chr3 & 63;

		if (isNaN(chr2)) 
			enc3 = enc4 = 64;
		else if (isNaN(chr3))
			enc4 = 64;

		output = output +
			keyStr.charAt(enc1) +
			keyStr.charAt(enc2) +
			keyStr.charAt(enc3) +
			keyStr.charAt(enc4);
		chr1 = chr2 = chr3 = "";
		enc1 = enc2 = enc3 = enc4 = "";
	} while (i < string.length);

	 return output;
}

/**
 * Carga por medio de AJAX el HTML de las opciones de una lista.
 *
 * @param string url La URL de donde se carga la lista.
 * @param input Elemento "Select" donde se lanza el evento después de cambiar la selección.
 * @param id Identificador 
 */
function loadDropDownList(url, select, id, ids_to_clear)
{
	$.ajax({
		type: 'post',
		dataType: 'html',
		url: url.replace('.html', '') + '/' + $(select).val() + '.html',
		success: function(data){
			for (var i = 0; i < ids_to_clear.length; i++) {
				var first_element = $('#' + ids_to_clear[i] + ' option')[0];
				$('#' + ids_to_clear[i]).empty();
				$('#' + ids_to_clear[i]).append(first_element);
			};

			$('#' + id).html(data);
		}
	});
}
