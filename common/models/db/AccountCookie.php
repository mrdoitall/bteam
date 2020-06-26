<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "account_cookie".
 *
 * @property int $id
 * @property string|null $ip
 * @property string|null $cookie
 * @property int|null $account_id
 * @property int|null $user_id
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property Account $account
 * @property User $user
 */
class AccountCookie extends \common\models\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'account_cookie';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cookie'], 'string'],
            [['account_id', 'user_id', 'created_at', 'updated_at'], 'integer'],
            [['ip'], 'string', 'max' => 50],
            [['account_id'], 'exist', 'skipOnError' => true, 'targetClass' => Account::className(), 'targetAttribute' => ['account_id' => 'id']],
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
            'cookie' => 'Cookie',
            'account_id' => 'Account ID',
            'user_id' => 'User ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Account]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(Account::className(), ['id' => 'account_id']);
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
