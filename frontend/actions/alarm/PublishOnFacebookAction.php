<?php

namespace frontend\actions\alarm;

use Yii;
use yii\base\Action;

class PublishOnFacebookAction extends Action
{
	/**
	 * Publica la alarma en Facebook.
	 *
	 * @param int $id El identificador de la alarma.
	 */
	public function run($id)
	{
		$model = $this->controller->findModel($id);
		
		if(Yii::$app->getRequest()->getQueryParam('code') !== null) {
			$model->getFacebookToken();
			return $this->controller->redirect(['alarm/index', 'success' => 1]);
		}
		else {
			$model->publishOnFacebook();
		}
	}
}
