<?php

namespace frontend\models;

use Yii;
use yii\base\Model;
use yii\web\ServerErrorHttpException;
use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\FacebookRequestException;
use TwitterOAuth\Auth\SingleUserAuth;
use TwitterOAuth\Serializer\ArraySerializer;
use TwitterOAuth\Exception\TwitterException;

/**
 * @property string $id
 * @property string $title
 * @property string $description
 * @property double $latitude
 * @property double $longitude
 * @property string $app_user_id
 * @property array $comments
 */
class AlarmForm extends Model
{
	public $id;
	public $title;
	public $description;
	public $latitude;
	public $longitude;
	public $app_user_id;
	public $comments = [];
	public $attached_files = [];
	public $user = null;

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['title', 'app_user_id'], 'required'],
			[['description'], 'string'],
			[['latitude', 'longitude'], 'number'],
			[['app_user_id'], 'integer'],
			[['title'], 'string', 'max' => 100],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'id' => 'ID',
			'title' => 'Title',
			'description' => 'Description',
			'latitude' => 'Latitude',
			'longitude' => 'Longitude',
			'app_user_id' => 'App User ID',
		];
	}

	/**
	 * Consume el servicio web para crear una nueva la alarma.
	 *
	 * @return boolean Verdadero si se creó correctamente o falso en otro caso.
	 */
	public function create()
	{
		$data = [
			'title' => $this->title,
			'description' => $this->description,
			'latitude' => $this->latitude,
			'longitude' => $this->longitude,
			'app_user_id' => Yii::$app->user->id,
		];
		$response = Yii::$app->helpers->callWebServiceAction('POST', '/alarms', true, $data);

		if(isset($response['status'])) {
			Yii::error($response['name'].': '.$response['message']);
			return false;
		}

		if(!$response)
			return false;

		return true;
	}

	/**
	 * Consume el servicio web para actualizar la alarma.
	 *
	 * @return boolean Verdadero si se actualizó correctamente o falso en otro caso.
	 */
	public function update()
	{
		$data = [
			'title' => $this->title,
			'description' => $this->description,
			'latitude' => $this->latitude,
			'longitude' => $this->longitude,
		];
		$response = Yii::$app->helpers->callWebServiceAction('PUT', '/alarms/'.$this->id, true, $data);

		if(isset($response['status'])) {
			Yii::error($response['name'].': '.$response['message']);
			return false;
		}

		if(!$response)
			return false;

		return true;
	}

	/**
	 * Consume el servicio web para eliminar la alarma.
	 *
	 * @return boolean Verdadero si se eliminó correctamente o falso en otro caso.
	 */
	public function delete()
	{
		$response = Yii::$app->helpers->callWebServiceAction('DELETE', '/alarms/'.$this->id, true);

		if(isset($response['status'])) {
			Yii::error($response['name'].': '.$response['message']);
			return false;
		}

		return true;
	}

	/**
	 * Consume el servicio web para añadir un comentario de la alarma.
	 * @return boolean Verdadero si se registro correctamente o falso en otro caso.
	 */
	public function comment() {
	    $data = [
    	    'content' => Yii::$app->request->post('comment'),
    	    'app_user_id' => Yii::$app->user->id,
    	    'alarm_id' => $this->id
	    ];

	    $response = Yii::$app->helpers->callWebServiceAction('POST', '/alarm/comment', true, $data);
	
	    if (isset($response['status'])) {
	        Yii::error($response['name'] . ': ' . $response['message']);
	        return false;
	    }
	    if (! $response) return false;
	    return true;
	}
	
	
	/**
	 * Consume el servicio web para solicitar la información de una alarma.
	 *
	 * @param $id El identificador de la alarma.
	 * @return AlarmForm El modelo de la alarma.
	 */
	public static function find($id)
	{
		$response = Yii::$app->helpers->callWebServiceAction('GET', '/alarms/'.$id, true);

		if(isset($response['status'])) {
			Yii::error($response['name'].': '.$response['message']);
			return null;
		}
		
		$model = new AlarmForm();
		$model->id = $response['id'];
		$model->title = $response['title'];
		$model->description = $response['description'];
		$model->latitude = $response['latitude'];
		$model->longitude = $response['longitude'];
		$model->app_user_id = $response['app_user_id'];
		$model->attached_files = $response['attached_files'];
		$model->user = null;
		$model->comments = $response['comments'];

		return $model;
	}

	/**
	 * Construye la URL para la solicitud del token de acceso de Facebook y redirecciona hacia ella.
	 */
	public function publishOnFacebook()
	{
		$url = Yii::$app->authClientCollection->clients['facebook']->buildAuthUrl(); // Build authorization URL.
		Yii::$app->getResponse()->redirect($url); // Redirect to authorization URL.
	}

	/**
	 * Esta función es llamada en el callback de la autenticación de Facebook. Genera el token de acceso
	 * para que pueda utilizarse la API de Facebook. Publica la alarma en Facebook.
	 *
	 * @return boolean Verdadero si se publicó correctamente la alarma.
	 * @throws ServerErrorHttpException Si ocurrió algún problema en la publicación de la alarma.
	 */
	public function getFacebookToken()
	{
		$code = Yii::$app->getRequest()->getQueryParam('code');
		$accessToken = Yii::$app->authClientCollection->clients['facebook']->fetchAccessToken($code); // Get access token.

		FacebookSession::setDefaultApplication(Yii::$app->params['facebook_app_id'], Yii::$app->params['facebook_app_secret']);
		$session = new FacebookSession($accessToken->getToken());

		try {
			$session->validate();
		} catch (FacebookRequestException $ex) {
			// Session not valid, Graph API returned an exception with the reason.
			throw new ServerErrorHttpException($ex->getMessage());
		} catch (\Exception $ex) {
			// Graph API returned info, but it may mismatch the current app or have expired.
			throw new ServerErrorHttpException($ex->getMessage());
		}

		if($session) {
			try {
				if(isset($this->attached_files[0])) {
					$response = (new FacebookRequest(
						$session, 'POST', '/1389662068012725/photos', [
							'source' => '@'.$this->attached_files[0],
							'message' => $this->title.': '.$this->description,
						]
					))->execute()->getGraphObject();
				}
				else {
					$response = (new FacebookRequest(
						$session, 'POST', '/1389662068012725/feed', [
							'message' => $this->title.': '.$this->description,
						]
					))->execute()->getGraphObject();
				}

				return true;
			} catch(FacebookRequestException $e) {
				throw new ServerErrorHttpException('Exception occured, code: '.$e->getCode().' with message: '.$e->getMessage());
			}
		}
	}

	/**
	 * Construye la URL para la solicitud del token de acceso de Twitter y redirecciona hacia ella.
	 */
	public function publishOnTwitter()
	{
		$requestToken = Yii::$app->authClientCollection->clients['twitter']->fetchRequestToken();
		$url = Yii::$app->authClientCollection->clients['twitter']->buildAuthUrl($requestToken); // Build authorization URL.
		return Yii::$app->getResponse()->redirect($url); // Redirect to authorization URL.
	}

	/**
	 * Esta función es llamada en el callback de la autenticación de Twitter. Genera el token de acceso
	 * para que pueda utilizarse la API de Twitter. Publica la alarma en un Tweet.
	 *
	 * @return boolean Verdadero si se publicó correctamente la alarma.
	 * @throws ServerErrorHttpException Si ocurrió algún problema en la publicación de la alarma.
	 */
	public function getTwitterToken()
	{
		try {
			$accessToken = Yii::$app->authClientCollection->clients['twitter']->fetchAccessToken(); // Get access token.

			$credentials = [
				'consumer_key' => 'G8T0msuiVJhqROfI3RZxdT92A',
				'consumer_secret' => 'wov8ezV2KRXrvCPfLlcRBNPKHIrgFtN46lHScZItomSUqheT50',
				'oauth_token' => $accessToken->getParam('oauth_token'),
				'oauth_token_secret' => $accessToken->getParam('oauth_token_secret'),
			];

			$auth = new SingleUserAuth($credentials, new ArraySerializer());
			
			if(isset($this->attached_files[0])) {
				$response = $auth->postMedia('media/upload', $this->attached_files[0]);
				$media_id = $response['media_id'];

				$response = $auth->post('statuses/update', [
					'status' => $this->title.': '.$this->description,
					'media_ids' => number_format($media_id, 0, '.', ''),
				]);
			}
			else {
				$response = $auth->post('statuses/update', ['status' => $this->title.': '.$this->description]);
			}

			return true;
		} catch(TwitterException $e) {
			throw new ServerErrorHttpException('Exception occured, code: '.$e->getCode().' with message: '.$e->getMessage());
		}
	}
}
