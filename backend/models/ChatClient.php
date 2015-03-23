<?php
namespace backend\models;

use Yii;
use yii\base\Action;
use \yii\db\Expression;
use common\models\ChatMessage;
use common\models\AppUser;
use common\models\Chat;
use backend\actions\chatMessage\PublishMessageAction;
use common\components\phpMQTT;

class ChatClient
{
    public $mqtt;
    public $is_private;
    public $separator = '~';

    /**
     * Se suscribe a una sala de chat para recibir notificaciones de mensajes.
     *
     * @param string $id El identificador del chat.
     */
    public function init($id)
    {
        $id = Yii::$app->helpers->checkUuid($id, 'common\models\Chat');
        $chat = Chat::findOne($id);

        $this->mqtt = new phpMQTT();
        $this->mqtt->broker(Yii::$app->params['mosquitto_server'], 1883, 'subscriber_'.$chat->uuid);

        if(!$this->mqtt->connect()){
            Yii::error('El chat con el ID '.$id.' no existe.');
            return;
        }

        $this->is_private = $chat->is_private;
        $topics['chat_server_'.$chat->uuid] = array('qos' => 2, 'function' => array($this, 'processReceivedMessage'));
        $this->mqtt->subscribe($topics, 2);
    }

    /**
     * <user_id>&<chat_uuid>&<access_token>&<device_id>&message => Encrypted with <key="Smc4avowi9tHIfTZD6ZWVoclXenJ4DTV">
     */
    public function processReceivedMessage($topic, $message)
    {
        $message = substr($message, 2);

        if($this->is_private)
            $message = Yii::$app->helpers->decryptChatMessage($message);

        $message_parts = explode($this->separator, $message);
        if(!isset($message_parts[0]) || !isset($message_parts[1]) || !isset($message_parts[2]) 
            || !isset($message_parts[3]) || !isset($message_parts[4]) || !isset($message_parts[5])) 
        {
            Yii::error('El formato del mensaje de chat es incorrecto.');
            echo 'El formato del mensaje de chat es incorrecto.';
            return false;
        }

        $user_id = $message_parts[0];
        $chat_uuid = $message_parts[1];
        $access_token = $message_parts[2];
        $device_id = $message_parts[3];
        $message = $message_parts[4];
        $message_uuid = $message_parts[5];

        $user = AppUser::find()
            ->where(['id' => $user_id])
            // ->where(['id' => $user_id, 'access_token' => $access_token])
            // ->andWhere(['>=', 'access_token_expiration', new Expression('NOW()')])
            ->one();

        if ($user === null) {
            Yii::error('Usuario no autorizado.');
            echo 'Usuario no autorizado.';
            return false;
        }

        if(!$user->is_active) {
            Yii::error('Usuario inactivo.');
            echo 'Usuario inactivo.';
            return false;
        }

        if(!$user->isAllowedDevice($device_id)) {
            Yii::error('Dispositivo bloqueado.');
            echo 'Dispositivo bloqueado.';
            return false;
        }

        $chat = Chat::find()->where(['uuid' => $chat_uuid])->one();

        $chat_message = new ChatMessage();

        if($chat->is_private)
            $chat_message->message = Yii::$app->helpers->encryptChatMessage($message);
        else
            $chat_message->message = $message;

        if(ChatMessage::find()->where(['uuid' => $message_uuid])->one() !== null)
            return false;

        $chat_message->uuid = $message_uuid;
        $chat_message->sent_time = new Expression('NOW()');
        $chat_message->received_time = new Expression('NOW()');
        $chat_message->app_user_id = $user_id;
        $chat_message->chat_id = $chat->id;
        $chat_message->save();

        $this->publishMessage($chat_message->id);

        return true;
    }

    public function publishMessage($id)
    {
        $chat_message = ChatMessage::findOne($id);

        if($chat_message === null)
        {
            Yii::error('El mensaje de chat con el ID '.$id.' no existe.');
            return;
        }

        $mqtt = new phpMQTT();
        $mqtt->broker(Yii::$app->params['mosquitto_server'], 1883, 'publisher_'.$chat_message->chat->uuid);

        if ($mqtt->connect()) {
            $messageArray = [
                $chat_message->id,
                $chat_message->chat->id,
                $chat_message->chat->uuid,
                $chat_message->app_user_id,
                $chat_message->message,
                $chat_message->uuid,
                $chat_message->sent_time,
            ];
            $message = implode($this->separator, $messageArray);

            if($chat_message->chat->is_private)
                $message = '1'.Yii::$app->helpers->encryptChatMessage($message);
            else
                $message = '0'.$message;

            $mqtt->publish('chat_client_'.$chat_message->chat->uuid, $message, 1);
            $mqtt->close();
        }
    }
}