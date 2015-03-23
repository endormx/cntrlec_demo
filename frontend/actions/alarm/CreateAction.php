<?php

namespace frontend\actions\alarm;

use Yii;
use yii\base\Action;
use frontend\models\AlarmForm;

class CreateAction extends Action
{
	/**
	 * Crea una nuevo alarma.
	 *
	 * @return mixed El formulario para crear la alarma o redirecciona al listado de alarmas
	 *				 si se creó correctamente.
	 */
	public function run()
	{
		$model = new AlarmForm();

		if ($model->load(Yii::$app->request->post()) && $model->create()) {
			return $this->controller->redirect(['index']);
		} else {
			return $this->controller->render('create', [
				'model' => $model,
			]);
		}
	}
}
