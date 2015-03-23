<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\BaseUrl;
use yii\web\JqueryAsset;
use yii\web\View;
use dosamigos\google\maps\LatLng;
use dosamigos\google\maps\Map;
use dosamigos\google\maps\Event;
use dosamigos\google\maps\overlays\Marker;

$this->registerJsFile(BaseUrl::base().'/js/Helpers.js', ['position' => View::POS_HEAD, 'depends' => [JqueryAsset::className()]]);

/* @var $this yii\web\View */
/* @var $model common\models\Alarm */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="alarm-form">

	<?php $form = ActiveForm::begin(); ?>

	<?= $form->field($model, 'title')->textInput(['maxlength' => 100]) ?>

	<?= Html::activeHiddenInput($model, 'latitude') ?>

	<?= Html::activeHiddenInput($model, 'longitude') ?>	

	<?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

	<?php 

	$map = new Map([
		'center' => new LatLng(['lat' => 20.9753656, 'lng' => -89.6164604]),
		'width' => '100%',
		'zoom' => 12,
	]);

	$event = new Event([
		'trigger' => 'click',
		'js' => 'processMapClick('.$map->name.', event, "alarmform")',
	]);

	$map->addEvent($event);

	if(!$isNewRecord) {
		$marker_coord = new LatLng(['lat' => $model->latitude, 'lng' => $model->longitude]);
		$marker = new Marker([
			'position' => $marker_coord,
		]);

		$map->appendScript('marker = '.$marker->name);

		$map->addOverlay($marker);
	}
	
	echo $map->display();
	?>

	<br>

	<div class="form-group">
		<?= Html::submitButton($isNewRecord ? 'Create' : 'Update', ['class' => $isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
	</div>

	<?php ActiveForm::end(); ?>

</div>
