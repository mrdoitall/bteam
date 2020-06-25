<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "customer_service_log".
 *
 * @property int $id
 * @property string|null $tag_id
 * @property int|null $value
 * @property int|null $value2
 * @property int|null $value3
 * @property int|null $service_id
 * @property int|null $customer_id
 * @property int|null $subscription_id
 * @property int|null $customer_service_id
 * @property int|null $log_time
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property Service $service
 * @property User $customer
 * @property Subscription $subscription
 * @property CustomerService $customerService
 */
class CustomerServiceLog extends \common\models\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'customer_service_log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['value', 'value2', 'value3', 'service_id', 'customer_id', 'subscription_id', 'customer_service_id', 'log_time', 'created_at', 'updated_at'], 'integer'],
            [['tag_id'], 'string', 'max' => 50],
            [['service_id'], 'exist', 'skipOnError' => true, 'targetClass' => Service::className(), 'targetAttribute' => ['service_id' => 'id']],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['customer_id' => 'id']],
            [['subscription_id'], 'exist', 'skipOnError' => true, 'targetClass' => Subscription::className(), 'targetAttribute' => ['subscription_id' => 'id']],
            [['customer_service_id'], 'exist', 'skipOnError' => true, 'targetClass' => CustomerService::className(), 'targetAttribute' => ['customer_service_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tag_id' => 'Tag ID',
            'value' => 'Value',
            'value2' => 'Value2',
            'value3' => 'Value3',
            'service_id' => 'Service ID',
            'customer_id' => 'Customer ID',
            'subscription_id' => 'Subscription ID',
            'customer_service_id' => 'Customer Service ID',
            'log_time' => 'Log Time',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Service]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(Service::className(), ['id' => 'service_id']);
    }

    /**
     * Gets query for [[Customer]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(User::className(), ['id' => 'customer_id']);
    }

    /**
     * Gets query for [[Subscription]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSubscription()
    {
        return $this->hasOne(Subscription::className(), ['id' => 'subscription_id']);
    }

    /**
     * Gets query for [[CustomerService]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomerService()
    {
        return $this->hasOne(CustomerService::className(), ['id' => 'customer_service_id']);
    }
}
