<?php
namespace backend\actions\alarm;

use Yii;
use yii\rest\Action;
use yii\base\Model;
use yii\web\ServerErrorHttpException;
use yii\db\Expression;
use common\models\AlarmAttachedFile;
use common\models\Comment;
use common\components\phpMQTT;
use yii\helpers\Json;

class CommentAction extends Action {

    /**
     * Crea un comentario de una alarma.
     * URL del servicio web: website-url/alarms?access-token=xxxxxxxx
     * Método: POST
     * JSON de ejemplo para crear la alarma:
     * {
     * "content": "Contenido comentario",
     * "app_user_id": "Identificador del usuario que realiza el comentario",
     * "alarm_id": "Identificador de la alarma"
     * }
     * @return boolean Indica si se creó el comentario o no.
     * @throws ServerErrorHttpException Si no se crea el objeto por alguna razón desconocida.
     */
    public function run() {
        $params = Yii::$app->getRequest()->getBodyParams();
        $connection = \Yii::$app->db;
        $transaction = $connection->beginTransaction();
        try {
            $model = new Comment();
            $model->load($params, '');
            if (! $model->save()) {throw new ServerErrorHttpException(
                    'Failed to create the comment object for unknown
                reason.');}
            $response = Yii::$app->getResponse();
            $response->setStatusCode(201);
            $transaction->commit();

            $this->publishComment(Comment::findOne($model->id));

            return $model->id;
        } catch (ServerErrorHttpException $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage());
            throw $e;
        }
    }

    /**
     * Publica el comentario en el servicio MQTT.
     */
    public function publishComment($model)
    {
        $mqtt = new phpMQTT();
        $mqtt->broker(Yii::$app->params['mosquitto_server'], 1883, 'alarms_comments_publisher_'.$model->id);

        if ($mqtt->connect()) {
            $message = Json::encode([
                'id' => $model->id,
                'content' => $model->content,
                'date_created' => $model->date_created,
                'app_user_id' => $model->app_user_id,
                'alarm_id' => $model->alarm_id,
            ]);

            $mqtt->publish('alarms_comments_client', $message, 1);
            $mqtt->close();
        }
    }

}
