<?php

namespace frontend\actions\alarm;

use yii\base\Action;

class DeleteAction extends Action
{
	/**
	 * Elimina una alarma.
	 *
	 * @param int $id El identificador de la alarma.
	 * @return string Redirecciona al listado de alarmas.
	 */
	public function run($id)
	{
		$this->controller->findModel($id)->delete();

		return $this->controller->redirect(['index']);
	}
}
