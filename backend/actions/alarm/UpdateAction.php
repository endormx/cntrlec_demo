<?php

namespace backend\actions\alarm;

use Yii;
use yii\rest\Action;
use yii\base\Model;
use yii\web\ServerErrorHttpException;

class UpdateAction extends Action
{
	/**
	 * Actualiza una alarma. 
	 *
	 * URL del servicio web: website-url/alarms/<id|uuid>?access-token=xxxxxxxx
	 * Método: PUT
	 * JSON de ejemplo para actualizar la alarma:
	 * {
	 *		"title": "Título de la alarma",
	 *		"description": "lorem ipsum",
	 *		"latitude": 21,
	 *		"longitude": -89,
	 *		"publish_on_facebook": "1",
	 *		"publish_on_twitter": "1",
	 *		"publish_on_instagram": "0",
	 *		"attached_files": [
	 *			"path/to/filename1.png"
	 * 		]
	 * }
	 *
	 * @return boolean Indica si se actualizó la alarma o no.
	 * @throws ServerErrorHttpException Si no se actualiza la alarma.
	 */
	public function run($id) 
	{
		$id = Yii::$app->helpers->checkUuid($id, $this->modelClass);
		$model = $this->findModel($id);

		if ($this->checkAccess) {
			call_user_func($this->checkAccess, $this->id, $model);
		}

		$model->scenario = Model::SCENARIO_DEFAULT;
		$model->load(Yii::$app->getRequest()->getBodyParams(), '');
		if ($model->save() === false && !$model->hasErrors()) {
			throw new ServerErrorHttpException('Failed to update the object for unknown reason.');
		}

		return true;
	}
}
