<?php

namespace backend\actions\alarm;

use Yii;
use yii\rest\Action;
use yii\base\Model;
use yii\web\ServerErrorHttpException;
use yii\db\Expression;
use common\models\AlarmAttachedFile;
use common\models\Alarm;
use common\components\phpMQTT;
use yii\helpers\Json;

class CreateAction extends Action
{
	/**
	 * Crea una nueva alarma. 
	 *
	 * URL del servicio web: website-url/alarms?access-token=xxxxxxxx
	 * Método: POST
	 * JSON de ejemplo para crear la alarma:
	 * {
     *      "uuid": "<uuid>",
     *      "title": "Título de la alarma",
	 *		"description": "lorem ipsum",
	 *		"latitude": 21,
	 *		"longitude": -89,
     *      "app_user_id": "1",
	 *		"attached_files": [
	 *			"path/to/filename1.png"
	 * 		]
	 * }
	 *
	 * @return boolean Indica si se creó la alarma o no.
	 * @throws ServerErrorHttpException Si no se crea el objeto por alguna razón desconocida.
	 */
	public function run() 
	{
		if ($this->checkAccess) {
			call_user_func($this->checkAccess, $this->id);
		}

		$model = new $this->modelClass([
			'scenario' => Model::SCENARIO_DEFAULT,
		]);

		$params = Yii::$app->getRequest()->getBodyParams();

		$connection = \Yii::$app->db;
		$transaction = $connection->beginTransaction();
		try {
			$model->load($params, '');

            if(!isset($params['uuid']))
                $model->uuid = new Expression('UUID()');

			$model->generateLastSync();
			if (!$model->save()) {
				throw new ServerErrorHttpException('Failed to create the alarm object for unknown reason.');
			}

			if(isset($params['attached_files'])) {
				foreach ($params['attached_files'] as $filename) { 
					$alarm_attached_file = new AlarmAttachedFile();
					$alarm_attached_file->alarm_id = $model->id;
					$alarm_attached_file->filename = $filename;
					if (!$alarm_attached_file->save()) {
						throw new ServerErrorHttpException('Failed to create the alarm_attached_file object for unknown reason.');
					}
				}
			}

			$response = Yii::$app->getResponse();
			$response->setStatusCode(201);
			$transaction->commit();

            $this->publishAlarm(Alarm::findOne($model->id));

			return $model->id;
		} catch (ServerErrorHttpException $e) {
			$transaction->rollBack();
			Yii::error($e->getMessage());
			throw $e;
		}
	}

    /**
     * Publica la alarma en el servicio MQTT.
     */
	public function publishAlarm($model)
    {
        $mqtt = new phpMQTT();
        $mqtt->broker(Yii::$app->params['mosquitto_server'], 1883, 'alarms_publisher_'.$model->uuid);

        if ($mqtt->connect()) {
            $message = Json::encode([
                'id' => $model->id,
                'uuid' => $model->uuid,
                'title' => $model->title,
                'description' => $model->description,
                'latitude' => $model->latitude,
                'longitude' => $model->longitude,
                'app_user_id' => $model->app_user_id,
            ]);

            $mqtt->publish('alarms_client', $message, 1);
            $mqtt->close();
        }
    }
}
