<?php

namespace frontend\actions\alarm;

use Yii;
use yii\base\Action;

class PublishOnTwitterAction extends Action
{
	/**
	 * Publica la alarma en Twitter.
	 *
	 * @param int $id El identificador de la alarma.
	 */
	public function run($id)
	{
		$model = $this->controller->findModel($id);
		
		if(Yii::$app->getRequest()->getQueryParam('oauth_token') !== null) {
			$model->getTwitterToken();
			return $this->controller->redirect(['alarm/index', 'success' => 1]);
		}
		else {
			return $model->publishOnTwitter();
		}
	}
}
