<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string|null $uuid
 * @property string|null $base_role
 * @property string|null $status
 * @property string|null $phone
 * @property string $email
 * @property string|null $auth_key
 * @property string|null $fullname
 * @property string $username
 * @property string|null $user_groups
 * @property string|null $password_hash
 * @property string|null $password_salt
 * @property string|null $password_reset_token
 * @property string|null $verification_token
 * @property int|null $last_get_account_at
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property AccessAssign[] $accessAssigns
 * @property Account[] $accounts
 * @property Account[] $accounts0
 * @property AccountCookie[] $accountCookies
 * @property AccountProcessLog[] $accountProcessLogs
 * @property UserAccessRole[] $userAccessRoles
 * @property UserLog[] $userLogs
 */
class User extends \common\models\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['email', 'username'], 'required'],
            [['last_get_account_at', 'created_at', 'updated_at'], 'integer'],
            [['uuid', 'base_role', 'status', 'username', 'user_groups'], 'string', 'max' => 50],
            [['phone'], 'string', 'max' => 11],
            [['email', 'fullname', 'password_hash', 'password_reset_token', 'verification_token'], 'string', 'max' => 255],
            [['auth_key'], 'string', 'max' => 200],
            [['password_salt'], 'string', 'max' => 10],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'uuid' => 'Uuid',
            'base_role' => 'Base Role',
            'status' => 'Status',
            'phone' => 'Phone',
            'email' => 'Email',
            'auth_key' => 'Auth Key',
            'fullname' => 'Fullname',
            'username' => 'Username',
            'user_groups' => 'User Groups',
            'password_hash' => 'Password Hash',
            'password_salt' => 'Password Salt',
            'password_reset_token' => 'Password Reset Token',
            'verification_token' => 'Verification Token',
            'last_get_account_at' => 'Last Get Account At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[AccessAssigns]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAccessAssigns()
    {
        return $this->hasMany(AccessAssign::className(), ['user_id' => 'id']);
    }

    /**
     * Gets query for [[Accounts]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAccounts()
    {
        return $this->hasMany(Account::className(), ['process_user_id' => 'id']);
    }

    /**
     * Gets query for [[Accounts0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAccounts0()
    {
        return $this->hasMany(Account::className(), ['created_user_id' => 'id']);
    }

    /**
     * Gets query for [[AccountCookies]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAccountCookies()
    {
        return $this->hasMany(AccountCookie::className(), ['user_id' => 'id']);
    }

    /**
     * Gets query for [[AccountProcessLogs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAccountProcessLogs()
    {
        return $this->hasMany(AccountProcessLog::className(), ['process_user_id' => 'id']);
    }

    /**
     * Gets query for [[UserAccessRoles]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserAccessRoles()
    {
        return $this->hasMany(UserAccessRole::className(), ['user_id' => 'id']);
    }

    /**
     * Gets query for [[UserLogs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserLogs()
    {
        return $this->hasMany(UserLog::className(), ['user_id' => 'id']);
    }
}
