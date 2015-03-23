<?php

namespace frontend\controllers;

use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use frontend\models\AlarmForm;

/**
 * AlarmController implements the CRUD actions for Alarm model.
 */
class AlarmController extends Controller
{
	public function behaviors()
	{
		return [
			'access' => [
				'class' => AccessControl::className(),
				'rules' => [
					[
						'allow' => true,
						'actions' => ['index', 'create', 'view', 'update', 'delete', 'publish-on-facebook', 'publish-on-twitter'],
						'roles' => ['@'],
					],
				],
			],
		];
	}

	public function actions()
	{
		return array_merge(parent::actions(), [
			'index' => 'frontend\actions\alarm\IndexAction',
			'create' => 'frontend\actions\alarm\CreateAction',
		    'view' => 'frontend\actions\alarm\ViewAction',
		    'update' => 'frontend\actions\alarm\UpdateAction',
			'delete' => 'frontend\actions\alarm\DeleteAction',
			'publish-on-facebook' => 'frontend\actions\alarm\PublishOnFacebookAction',
			'publish-on-twitter' => 'frontend\actions\alarm\PublishOnTwitterAction',
		]);
	}

	/**
	 * Encuentra el modelo de la alarma dado un identificador.
	 *
	 * @param int $id El identificador de la alarma.
	 * @return Alarm El modelo de la alarma.
	 * @throws NotFoundHttpException Si alarma no fue encontrada.
	 */
	public function findModel($id)
	{
		if (($model = AlarmForm::find($id)) !== null) {
			return $model;
		} else {
			throw new NotFoundHttpException('The requested page does not exist.');
		}
	}
}
