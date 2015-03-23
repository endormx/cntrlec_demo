<?php

namespace backend\actions\alarm;

use Yii;
use yii\rest\Action;
use yii\web\ServerErrorHttpException;
use common\models\Alarm;

class PublishOnTwitterAction extends Action
{
	/**
	 * Publica una alarma en Twitter. 
	 *
	 * URL del servicio web: website-url/alarms/publish-on-twitter/<id|uuid>?access-token=xxxxxxxx
	 * Método: PUT
	 *
	 * @throws ServerErrorHttpException Si no se puede publicar la alarm.
	 */
	public function run($id) 
	{
		if ($this->checkAccess) {
			call_user_func($this->checkAccess, $this->id);
		}

		$id = Yii::$app->helpers->checkUuid($id, $this->modelClass);
		$model = Alarm::findOne($id);
		return $model->publishOnTwitter();
	}
}
