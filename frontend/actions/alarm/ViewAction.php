<?php
namespace frontend\actions\alarm;

use Yii;
use yii\base\Action;

class ViewAction extends Action {

    /**
     * Detalles una alarma.
     * @param int $id Identificador de la alarma.
     * @return mixed Vista donde se mostraran los detalles.
     */
    public function run($id) {
        $model = $this->controller->findModel($id);
        if (isset($_POST['comment']) && trim($_POST['comment']) !== "") {
            $model->comment();
        }
        return $this->controller->render('view', [
            'model' => $model
        ]);
    }

}
