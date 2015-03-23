<?php

namespace common\components;
 
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\UnauthorizedHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotAcceptableHttpException;
use yii\web\ConflictHttpException;
use yii\web\GoneHttpException;
use yii\web\UnsupportedMediaTypeHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\HttpException;
use yii\base\InvalidParamException;
use yii\helpers\Url;
 
class Helpers extends Component
{
	/**
	 * @param string $string es la cadena a encriptar
	 * @param string $key es la llave inicial que sirve para encriptar
	 */
	public function encrypt($string, $key = ' ') {
		$result = '';
		for ($i = 0; $i < strlen($string); $i++) {
			$char = substr($string, $i, 1);
			$keychar = substr($key, ($i % strlen($key)) - 1, 1);
			$char = chr(ord($char) + ord($keychar));
			$result.=$char;
		}
		return base64_encode(base64_encode($result));
	}

	/**
	 * @param string $string es la cadena a encriptar
	 * @param string $key es la llave inicial que sirve para desencriptar
	 */
	public function decrypt($string, $key = ' ') {
		$result = '';
		$string = base64_decode(base64_decode($string));
		for ($i = 0; $i < strlen($string); $i++) {
			$char = substr($string, $i, 1);
			$keychar = substr($key, ($i % strlen($key)) - 1, 1);
			$char = chr(ord($char) - ord($keychar));
			$result.=$char;
		}
		return $result;
	}

	/**
	 * @param string $string es la cadena a encriptar
	 * @param string $key es la llave inicial que sirve para encriptar
	 */
	public function encryptChatMessage($string, $key = ' ') {
		return Yii::$app->helpers->encrypt($string, Yii::$app->params['auth_key']);
	}

	/**
	 * @param string $string es la cadena a encriptar
	 * @param string $key es la llave inicial que sirve para desencriptar
	 */
	public function decryptChatMessage($string, $key = ' ') {
		return Yii::$app->helpers->decrypt($string, Yii::$app->params['auth_key']);
	}

	/**
	 * Llama a un servicio web, con la posibilidad de utilizar el token de acceso y enviar información;
	 * devuelve la información de la solicitud al servicio web.
	 *
	 * @param string $method El método que será utilizado para llamar al servicio web.
	 * @param string $action La acción que será ejecutada.
	 * @param boolean $use_access_token Indica si debe utilizarse el token de acceso en la solicitud.
	 * @param mixed $data Arreglo con los datos que serán enviados, falso en otro caso.
	 * @return array Los datos obtenidos del servicio web.
	 */
	public function callWebServiceAction($method, $action, $use_access_token = false, $data = false)
	{
		$action_url = Yii::$app->params['web_services_url'].$action;

		if($use_access_token) {
			$action_url = $this->prepareAccessToken($action_url);
			$response = $this->callWebService($method, $action_url, $data);
		}
		else {
			$response = $this->callWebService($method, $action_url, $data);
		}

		$this->checkResponseStatus($response);
		return $response;
	}

	/**
	 * Ejecuta el servicio web.
	 *
	 * @param string $method El método que será utilizado para llamar al servicio web.
	 * @param string $url La URL completa del servicio web.
	 * @param mixed $data Arreglo con los datos que serán enviados, falso en otro caso.
	 * @return mixed Los datos obtenidos de la respuesta del servicio web.
	 */
	private function callWebService($method, $url, $data = false)
	{
		$curl = curl_init();

		switch ($method)
		{
			case 'POST':
				curl_setopt($curl, CURLOPT_POST, true);

				if ($data)
					curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
				break;

			case 'PUT':
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');

				if ($data) {
					$data_json = JSON::encode($data);
					curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
					curl_setopt($curl, CURLOPT_POSTFIELDS, $data_json);
				}
				break;

			case 'DELETE':
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
				break;

			default:
				if ($data)
					$url = sprintf("%s?%s", $url, http_build_query($data));
		}

		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		$response = curl_exec($curl);

		curl_close($curl);

		try {
			return JSON::decode($response);
		} catch (InvalidParamException $e) {
			echo $url;die;
			throw new ServerErrorHttpException('Incorrect response JSON format.');
		}
	}

	private function getHeadersasArray($response)
	{
		$headers = array();

		$header_text = substr($response, 0, strpos($response, "\r\n\r\n"));

		foreach (explode("\r\n", $header_text) as $i => $line)
			if ($i === 0)
				$headers['http_code'] = $line;
			else
			{
				list ($key, $value) = explode(': ', $line);

				$headers[$key] = $value;
			}

		return $headers;
	}

	/**
	 * Prepara el token de acceso para poder consumir los servicios web que requieren autenticación.
	 * El usuario contiene una llave de encriptación "auth_key" y un "access_token". Ambos son
	 * utilizados para la generación de las credenciales de autenticación.
	 *
	 * El usuario que inició sesión recibe estos valores desde el servidor de la siguiente manera:
	 *		"auth_key"		=> 	Encriptado con la llave común (params["auth_key"]).
	 *		"access_token"	=>	Encriptado con la llave del usuario ("auth_key").
	 *
	 * Para enviar las credenciales de autenticación al servicio web se deben encriptar los datos
	 * de la manera correcta. Para ello es necesario seguir los siguientes pasos:
	 *		1. Desencriptar la "auth_key" con la llave común (params["auth_key"]).
	 *		2. Desencriptar el "access_token" utilando "auth_key" obtenida en el paso 1.
	 *		3. Obtener la IP del cliente y encriptarla utilizando la "auth_key" obtenida en el paso 1.
	 *		4. Concatenar el "access_token" obtenido en el paso 2 con la IP obtenida en el paso 3 utilizando
	 *		   el caracter "&" como separador. Ej. <access_token>&<client_ip>
	 *		5. Encriptar la cadena resultante del paso 4 utilizando la llave común (params["auth_key"]).
	 *		6. Concatenar el token resultante del paso 5 con la URL del servicio web utilizando el 
	 *		   parámetro GET "access-token".
	 *
	 * @param string $action_url La URL del servicio web.
	 * @return string La url del servicio web concatenado con el token de acceso.
	 */
	private function prepareAccessToken($action_url)
	{
		$identity = Yii::$app->user->identity;

		$auth_key = Yii::$app->helpers->decrypt($identity->auth_key, Yii::$app->params['auth_key']);
		$access_token = Yii::$app->helpers->decrypt($identity->access_token, $auth_key);
		$client_ip = Yii::$app->helpers->encrypt(Yii::$app->getRequest()->getUserIP(), $auth_key);

		$access_token = Yii::$app->helpers->encrypt($access_token.'&'.$client_ip, Yii::$app->params['auth_key']);
		return $action_url.'?access-token='.$access_token;
	}

	/**
	 * Checa si la respuesta del servicio web arrojó una excepción.
	 *
	 * @param array $response Arreglo con la respuesta del servicio web.
	 */
	private function checkResponseStatus($response)
	{
		if(!isset($response['status']) && !isset($response['code']))
			return;

		$message = '';
		if(isset($response['message']))
			$message = $response['message'];

		if(isset($response['code']) && defined('YII_DEBUG') && constant('YII_DEBUG'))
			throw new ServerErrorHttpException($message);

		switch ($response['status']) {
			case 400: throw new BadRequestHttpException($message);
			case 401: throw new UnauthorizedHttpException($message);
			case 403: throw new ForbiddenHttpException($message);
			case 404: throw new NotFoundHttpException($message);
			case 405: throw new MethodNotAllowedHttpException($message);
			case 406: throw new NotAcceptableHttpException($message);
			case 409: throw new ConflictHttpException($message);
			case 410: throw new GoneHttpException($message);
			case 415: throw new UnsupportedMediaTypeHttpException($message);
			case 500: throw new ServerErrorHttpException($message); 	// Internal Server Error
			case 501: throw new ServerErrorHttpException($message); 	// Not Implemented
			case 502: throw new ServerErrorHttpException($message); 	// Bad Gateway
			case 503: throw new ServerErrorHttpException($message); 	// Service Unavailable
			case 504: throw new ServerErrorHttpException($message); 	// Gateway Timeout
			case 505: throw new ServerErrorHttpException($message); 	// HTTP Version Not Supported
			default: break;
		}
	}

	/**
	 * Valida si el identificador recibido es un UUID. Devuelve el identificador primario
	 * del modelo; convirtiendo el UUID al identificador primario, de ser necesario.
	 *
	 * @param int $id El identificador a ser validado.
	 * @param string $model_class Clase a la que pertenece el modelo en donde se validará el UUID.
	 * @return int El identificador primario del modelo.
	 */
	public function checkUuid($id, $model_class) 
	{
		if(preg_match(Yii::$app->params['uuid_pattern'], $id)) {
			$model = $model_class::find()->where(['uuid' => $id])->one();
			if($model !== null) {
				$id = $model->id;
			}
		}

		return $id;
	}

	/**
	 * Crea una nueva conexión para el chat
	 */
	public function createAsyncConnectionForChat($chatId) 
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, Url::to(['chat-message/subscribe-to-chat', 'id' => $chatId], true));
		curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($ch, CURLOPT_TIMEOUT_MS, 1);
		$response = curl_exec($ch);
		curl_close($ch);
	}
}
