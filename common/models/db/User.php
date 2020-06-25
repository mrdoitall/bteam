<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property string|null $uuid
 * @property string|null $status
 * @property string|null $phone
 * @property string $email
 * @property string|null $base_role
 * @property string|null $auth_key
 * @property string|null $fullname
 * @property string $username
 * @property string|null $user_groups
 * @property string|null $password_hash
 * @property string|null $password_salt
 * @property string|null $password_reset_token
 * @property string|null $verification_token
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property AccessAssign[] $accessAssigns
 * @property CustomerService[] $customerServices
 * @property CustomerServiceLog[] $customerServiceLogs
 * @property SubDomain[] $subDomains
 * @property Subscription[] $subscriptions
 * @property UserAccessRole[] $userAccessRoles
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
            [['created_at', 'updated_at'], 'integer'],
            [['uuid', 'status', 'base_role', 'username', 'user_groups'], 'string', 'max' => 50],
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
            'status' => 'Status',
            'phone' => 'Phone',
            'email' => 'Email',
            'base_role' => 'Base Role',
            'auth_key' => 'Auth Key',
            'fullname' => 'Fullname',
            'username' => 'Username',
            'user_groups' => 'User Groups',
            'password_hash' => 'Password Hash',
            'password_salt' => 'Password Salt',
            'password_reset_token' => 'Password Reset Token',
            'verification_token' => 'Verification Token',
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
     * Gets query for [[CustomerServices]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomerServices()
    {
        return $this->hasMany(CustomerService::className(), ['customer_id' => 'id']);
    }

    /**
     * Gets query for [[CustomerServiceLogs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomerServiceLogs()
    {
        return $this->hasMany(CustomerServiceLog::className(), ['customer_id' => 'id']);
    }

    /**
     * Gets query for [[SubDomains]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSubDomains()
    {
        return $this->hasMany(SubDomain::className(), ['customer_id' => 'id']);
    }

    /**
     * Gets query for [[Subscriptions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSubscriptions()
    {
        return $this->hasMany(Subscription::className(), ['customer_id' => 'id']);
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
}
