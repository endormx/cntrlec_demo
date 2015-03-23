<?php
return [
	'adminEmail' => 'admin@example.com',
	'supportEmail' => 'support@example.com',
	'user.passwordResetTokenExpire' => 3600,
	// Llave utilizada para la generación de contraseñas aleatorias cuando se registra un nuevo usuario.
	'auth_key' => 'Smc4avowi9tHIfTZD6ZWVoclXenJ4DTV', 	
	// Expresión regular utilizada para validar el formato del UUID.
	'uuid_pattern' => '/^\w{8}-\w{4}-\w{4}-\w{4}-\w{12}$/',
	'facebook_app_id' => '337486879780717', 
	'facebook_app_secret' => '041eb5b954a06c585853149a64ac2ddc',
        'mosquitto_server' => $_SERVER['MOSQUITTO_SERVER']
];
