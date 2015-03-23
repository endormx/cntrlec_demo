<?php

namespace backend\controllers;

use Yii;
use yii\rest\ActiveController;
use yii\filters\auth\QueryParamAuth;
use yii\web\ForbiddenHttpException;

class AlarmController extends ActiveController
{
	public $modelClass = 'common\models\Alarm';

	public function actions()
	{
		return array_merge(parent::actions(), [
			'index' => [
				'class' => 'backend\actions\alarm\IndexAction',
				'modelClass' => $this->modelClass,
				'checkAccess' => [$this, 'checkAccess'],
			],
			'create' => [
				'class' => 'backend\actions\alarm\CreateAction',
				'modelClass' => $this->modelClass,
				'checkAccess' => [$this, 'checkAccess'],
			],
			'update' => [
				'class' => 'backend\actions\alarm\UpdateAction',
				'modelClass' => $this->modelClass,
				'checkAccess' => [$this, 'checkAccess'],
			],
			'view' => [
				'class' => 'backend\actions\alarm\ViewAction',
				'modelClass' => $this->modelClass,
				'checkAccess' => [$this, 'checkAccess'],
			],
                        'comment' => [
				'class' => 'backend\actions\alarm\CommentAction',
				'modelClass' => $this->modelClass,
				'checkAccess' => [$this, 'checkAccess'],
			],
                        'upload' => [
                                'class' => 'backend\actions\alarm\UploadFileAction',
                                'modelClass' => $this->modelClass,
                                'checkAccess' => [$this, 'checkAccess'],
                        ],
		]);
	}

	protected function verbs()
	{
	    return array_merge(parent::verbs(), [
	        'comment' => ['POST'],
                'upload' => ['POST']
	        ]);
	}
	
	public function behaviors()
	{
		$behaviors = parent::behaviors();
		$behaviors['authenticator'] = [
			'class' => QueryParamAuth::className(),
		];
		return $behaviors;
	}

	/**
	 * Checa los privilegios del usuario autenticado.
	 *
	 * @param string $action El ID de la acción que será ejecutada.
	 * @param \yii\base\Model $model El modelo que está siendo accesado.
	 * @param array $params Parámetros adicionales
	 * @throws ForbiddenHttpException Si el usuario no tiene acceso.
	 */
	public function checkAccess($action, $model = null, $params = [])
	{
        if($action == 'update' || $action == 'delete') {
            if(Yii::$app->user->identity->user_type != 1 && $model->app_user_id != Yii::$app->user->id) {
                throw new ForbiddenHttpException('Forbidden');
            }
        }
	}
}