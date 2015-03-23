<?php

namespace common\models;

use Yii;
use \yii\db\ActiveRecord;
use yii\db\Query;

/**
 * This is the model class for table "alarm".
 *
 * @property int $id
 * @property string $uuid
 * @property string $title
 * @property string $description
 * @property double $latitude
 * @property double $longitude
 * @property string $last_sync 
 * @property string $app_user_id 
 * @property int $row_status 
 *
 * @property AlarmAttachedFile[] $alarmAttachedFiles
 * @property Comment[] $comments
 */
class Alarm extends ActiveRecord
{
	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'alarm';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['title', 'app_user_id', 'uuid'], 'required'],
			[['description'], 'string'],
			[['latitude', 'longitude'], 'number'],
			[['last_sync', 'app_user_id', 'row_status'], 'integer'],
			[['title'], 'string', 'max' => 100],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'id' => 'ID',
			'uuid' => 'UUID',
			'title' => 'Title',
			'description' => 'Description',
			'latitude' => 'Latitude',
			'longitude' => 'Longitude',
			'last_sync' => 'Last Syncronization',
			'app_user_id' => 'App User ID',
			'row_status' => 'Row Status',
		];
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getAlarmAttachedFiles()
	{
		return $this->hasMany(AlarmAttachedFile::className(), ['alarm_id' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getAppUser()
	{
		return $this->hasOne(AppUser::className(), ['id' => 'app_user_id']);
	}

	public function fields()
	{
		$fields = array_merge(parent::fields(), [
			'attached_files' => function ($model) {
				if($model->scenario == 'synchronize') {
					return $model->alarmAttachedFiles;
				} else {
					return array_map(function ($file){
						return $file->filename;
					}, $model->alarmAttachedFiles);
				}
			},
			'comments' => function ($model) {
				return array_map(function ($comment){
					return $comment;
				}, $this->comments);
			},
		]);

		unset($fields['last_sync']);

		return $fields;
	}

	/**
	 * Genera el identificador de última sincronización.
	 */
	public function generateLastSync()
	{
		$max_last_sync = (new Query())
			->select('MAX(last_sync) as max_last_sync')
			->from('alarm')
			->one();
		$this->last_sync = $max_last_sync['max_last_sync'] + 1;
	}
		
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getComments()
    {
        return $this->hasMany(Comment::className(), ['alarm_id' => 'id']);
    }
}
