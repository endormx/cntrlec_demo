<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Alarms';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="alarm-index">

	<h1><?= Html::encode($this->title) ?></h1>

	<p>
		<?= Html::a('Create Alarm', ['create'], ['class' => 'btn btn-success']) ?>
	</p>

	<?= GridView::widget([
		'dataProvider' => $dataProvider,
		'columns' => [
			['class' => 'yii\grid\SerialColumn'],

			'id',
			'title',
			'description:ntext',

			[
				'class' => 'yii\grid\ActionColumn',
				'urlCreator' => function ($action, $model, $key, $index) {
					return [$action, 'id' => $model['id']];
				},
				'template' => '{view} {update} {publish-on-facebook} {publish-on-twitter} {delete}',
				'buttons' => 
					[
						'publish-on-facebook' => function ($url, $model, $key) {
							return Html::a('<span class="glyphicon glyphicon-tags"></span>', $url, [
								'title' => 'Public on Facebook',
								'data-pjax' => '0',
							]);
						},
						'publish-on-twitter' => function ($url, $model, $key) {
							return Html::a('<span class="glyphicon glyphicon-tags"></span>', $url, [
								'title' => 'Public on Twitter',
								'data-pjax' => '0',
							]);
						},
					],
			],
		],
	]); ?>

</div>
