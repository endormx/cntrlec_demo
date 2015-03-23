<?php

namespace backend\actions\alarm;

use Yii;
use yii\rest\Action;

class ViewAction extends Action
{
	/**
	 * Devuelve la información de una alarma 
	 *
	 * URL del servicio web: website-url/alarms/<id|uuid>?access-token=xxxxxxxx
	 * Método: GET
	 *
	 * @return AppUser El modelo de la alarma.
	 */
	public function run($id)
	{
		$id = Yii::$app->helpers->checkUuid($id, $this->modelClass);
		$model = $this->findModel($id);
		if ($this->checkAccess) {
			call_user_func($this->checkAccess, $this->id, $model);
		}

		return $model;
	}
}
