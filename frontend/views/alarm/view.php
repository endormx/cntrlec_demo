<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Alarm */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => 'Alarms', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="alarm-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'id',
            'title',
            'description:ntext',
            'latitude',
            'longitude',
            'app_user_id',
        ],
    ]) ?>
    
    
	<?php $form = ActiveForm::begin(['options' => [
	    'class' => 'row'
	]]); ?>

	<div class="col-xs-12 col-sm-3 col-lg-2">
	   <?= Html::label(Yii::t('frontend', 'Comment'), null, ['class' => 'control-label']) ?>
    </div>
    
    <div class="col-xs-12 col-sm-6 col-lg-8">
	   <?= Html::textarea('comment', null, ['class' => 'form-control', 'rows' => 3]) ?>
    </div>
	
	<div class="col-xs-12 col-sm-3 col-lg-2">
	   <?= Html::submitButton(Yii::t('frontend', 'Add comment'), ['class' => 'btn btn-primary btn-block']) ?>
    </div>

	<?php ActiveForm::end(); ?>

    <?php $reverse = false; ?>
    
	<?php foreach ($model->comments as $item){ ?>
	
    <blockquote class="<?=$reverse?"blockquote-reverse":null?>">
      <p><?= $item['content']?></p>
      <footer>
        <?= $item['app_user_id'] ?>
        <i>- <?= $item['date_created'] ?></i>
      </footer>
    </blockquote>
	
	<?php $reverse = !$reverse; ?>
	    
	<?php } ?>
    
</div>
