<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "customer".
 *
 * @property int $id
 * @property string|null $uuid
 * @property string|null $email
 * @property string|null $username
 * @property float|null $balance_amount
 * @property float|null $credit_amount
 * @property string|null $password_hash
 * @property string|null $password_salt
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property CustomerService[] $customerServices
 * @property CustomerServiceLog[] $customerServiceLogs
 * @property SubDomain[] $subDomains
 * @property Subscription[] $subscriptions
 */
class Customer extends \common\models\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'customer';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['balance_amount', 'credit_amount'], 'number'],
            [['created_at', 'updated_at'], 'integer'],
            [['uuid', 'email'], 'string', 'max' => 100],
            [['username', 'password_hash'], 'string', 'max' => 50],
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
            'email' => 'Email',
            'username' => 'Username',
            'balance_amount' => 'Balance Amount',
            'credit_amount' => 'Credit Amount',
            'password_hash' => 'Password Hash',
            'password_salt' => 'Password Salt',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
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
}
