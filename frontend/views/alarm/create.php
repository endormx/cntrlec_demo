<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model common\models\Alarm */

$this->title = 'Create Alarm';
$this->params['breadcrumbs'][] = ['label' => 'Alarms', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="alarm-create">

	<h1><?= Html::encode($this->title) ?></h1>

	<?= $this->render('_form', [
		'model' => $model,
		'isNewRecord' => true,
	]) ?>

</div>
