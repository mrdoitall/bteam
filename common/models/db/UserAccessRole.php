<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "user_access_role".
 *
 * @property int $id
 * @property int|null $user_id
 * @property int|null $access_role_id
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property User $user
 * @property AccessRole $accessRole
 */
class UserAccessRole extends \common\models\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_access_role';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'access_role_id', 'created_at', 'updated_at'], 'integer'],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
            [['access_role_id'], 'exist', 'skipOnError' => true, 'targetClass' => AccessRole::className(), 'targetAttribute' => ['access_role_id' => 'id']],
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
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
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
}
