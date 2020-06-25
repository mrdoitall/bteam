<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "access_action_group".
 *
 * @property string $id
 * @property string|null $name
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property AccessAction[] $accessActions
 */
class AccessActionGroup extends \common\models\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'access_action_group';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['created_at', 'updated_at'], 'integer'],
            [['id'], 'string', 'max' => 100],
            [['name'], 'string', 'max' => 200],
            [['id'], 'unique'],
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
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[AccessActions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAccessActions()
    {
        return $this->hasMany(AccessAction::className(), ['access_action_group_id' => 'id']);
    }
}
