<?php

namespace backend\actions\alarm;

use Yii;
use yii\rest\Action;
use yii\base\Model;
use backend\models\UploadForm;
use yii\web\ServerErrorHttpException;
use yii\db\Expression;

class UploadFileAction extends Action {

    public function run() {

        $model = new UploadForm();
        $params = Yii::$app->getRequest()->getBodyParams();
        $model->load($params, '');
        $model->file = UploadedFile::getInstances($model, 'file');

        if ($model->file && $model->validate()) {
            foreach ($model->file as $file) {
                $file->saveAs('uploads/' . $file->baseName . '.' . $file->extension);
            }
        }
    }

}
