<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "user_log".
 *
 * @property int $id
 * @property string|null $ip
 * @property string|null $type
 * @property int|null $user_id
 * @property string|null $content
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property User $user
 */
class UserLog extends \common\models\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_id', 'created_at', 'updated_at'], 'integer'],
            [['ip', 'type'], 'string', 'max' => 50],
            [['content'], 'string', 'max' => 500],
            [['user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'ip' => 'Ip',
            'type' => 'Type',
            'user_id' => 'User ID',
            'content' => 'Content',
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
}
