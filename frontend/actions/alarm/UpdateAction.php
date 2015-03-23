<?php

namespace frontend\actions\alarm;

use Yii;
use yii\base\Action;

class UpdateAction extends Action
{
	/**
	 * Actualiza los datos de una alarma.
	 *
	 * @param int $id El identificador de la alarma.
	 * @return mixed El formulario para actualizar la alarma o redirecciona al listado de alarmas
	 * 				 si se guardó correctamente.
	 */
	public function run($id)
	{
		$model = $this->controller->findModel($id);

		if ($model->load(Yii::$app->request->post()) && $model->update()) {
			return $this->controller->redirect(['index']);
		} else {
			return $this->controller->render('update', [
				'model' => $model,
			]);
		}
	}
}
