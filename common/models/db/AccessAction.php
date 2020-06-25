<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "access_action".
 *
 * @property string $id
 * @property string|null $name
 * @property string|null $description
 * @property string|null $access_action_group_id
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property AccessActionGroup $accessActionGroup
 * @property AccessAssign[] $accessAssigns
 */
class AccessAction extends \common\models\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'access_action';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['created_at', 'updated_at'], 'integer'],
            [['id'], 'string', 'max' => 200],
            [['name', 'description'], 'string', 'max' => 255],
            [['access_action_group_id'], 'string', 'max' => 100],
            [['id'], 'unique'],
            [['access_action_group_id'], 'exist', 'skipOnError' => true, 'targetClass' => AccessActionGroup::className(), 'targetAttribute' => ['access_action_group_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'description' => 'Description',
            'access_action_group_id' => 'Access Action Group ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[AccessActionGroup]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAccessActionGroup()
    {
        return $this->hasOne(AccessActionGroup::className(), ['id' => 'access_action_group_id']);
    }

    /**
     * Gets query for [[AccessAssigns]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAccessAssigns()
    {
        return $this->hasMany(AccessAssign::className(), ['access_action_id' => 'id']);
    }
}
