<?php

namespace backend\actions\alarm;

use Yii;
use yii\rest\Action;
use yii\data\ActiveDataProvider;

class IndexAction extends Action
{
	public $prepareDataProvider;

	/**
	 * Devuelve la lista de todos las alarmas creadas.
	 *
	 * URL del servicio web: website-url/alarms?access-token=xxxxxxxx
	 * Método: GET
	 *
	 * @return array La lista de modelos de todos las alarmas.
	 */
	public function run()
	{
		if ($this->checkAccess) {
			call_user_func($this->checkAccess, $this->id);
		}

		return $this->prepareDataProvider();
	}

	protected function prepareDataProvider()
	{
		if ($this->prepareDataProvider !== null) {
			return call_user_func($this->prepareDataProvider, $this);
		}

		$modelClass = $this->modelClass;
        $query = $modelClass::find();

        if(Yii::$app->user->identity->user_type != 1)
            $query->where(['app_user_id' => Yii::$app->user->id]);

		return new ActiveDataProvider([
			'query' => $query,
			'pagination' => false,
		]);
	}
}
