<?php

namespace frontend\actions\alarm;

use Yii;
use yii\base\Action;
use yii\data\ArrayDataProvider;

class IndexAction extends Action
{
	/**
	 * Despliega el listado de alarmas.
	 *
	 * @return string La vista del listado de alarmas.
	 */
	public function run()
	{
		$response = Yii::$app->helpers->callWebServiceAction('GET', '/alarms', true);

		if(isset($response['status']) && $response['status'] == 401) {
			$data = [];
		}
		else {
			$data = $response;
		}

		$dataProvider = new ArrayDataProvider([
			'allModels' => $data,
			'sort' => [
				'attributes' => ['id', 'title'],
			],
			'pagination' => [
				'pageSize' => 10,
			],
		]);

		return $this->controller->render('index', [
			'dataProvider' => $dataProvider,
		]);
	}
}
