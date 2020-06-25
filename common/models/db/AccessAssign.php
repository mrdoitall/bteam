<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "access_assign".
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $access_role_id
 * @property string|null $access_action_id
 *
 * @property User $user
 * @property AccessRole $accessRole
 * @property AccessAction $accessAction
 */
class AccessAssign extends \common\models\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'access_assign';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'access_role_id'], 'integer'],
            [['access_action_id'], 'string', 'max' => 200],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['access_role_id'], 'exist', 'skipOnError' => true, 'targetClass' => AccessRole::className(), 'targetAttribute' => ['access_role_id' => 'id']],
            [['access_action_id'], 'exist', 'skipOnError' => true, 'targetClass' => AccessAction::className(), 'targetAttribute' => ['access_action_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'access_role_id' => 'Access Role ID',
            'access_action_id' => 'Access Action ID',
        ];
    }

    /**
     * Gets query for [[User]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    /**
     * Gets query for [[AccessRole]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAccessRole()
    {
        return $this->hasOne(AccessRole::className(), ['id' => 'access_role_id']);
    }

    /**
     * Gets query for [[AccessAction]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAccessAction()
    {
        return $this->hasOne(AccessAction::className(), ['id' => 'access_action_id']);
    }
}
