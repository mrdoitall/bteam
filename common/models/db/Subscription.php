<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "subscription".
 *
 * @property int $id
 * @property int|null $value
 * @property int|null $residual_value
 * @property string|null $unit_name
 * @property int|null $plan_id
 * @property int|null $service_id
 * @property int|null $customer_id
 * @property int|null $expired_at
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property CustomerService[] $customerServices
 * @property CustomerServiceLog[] $customerServiceLogs
 * @property Plan $plan
 * @property Service $service
 * @property User $customer
 */
class Subscription extends \common\models\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'subscription';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['value', 'residual_value', 'plan_id', 'service_id', 'customer_id', 'expired_at', 'created_at', 'updated_at'], 'integer'],
            [['unit_name'], 'string', 'max' => 50],
            [['plan_id'], 'exist', 'skipOnError' => true, 'targetClass' => Plan::className(), 'targetAttribute' => ['plan_id' => 'id']],
            [['service_id'], 'exist', 'skipOnError' => true, 'targetClass' => Service::className(), 'targetAttribute' => ['service_id' => 'id']],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['customer_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'value' => 'Value',
            'residual_value' => 'Residual Value',
            'unit_name' => 'Unit Name',
            'plan_id' => 'Plan ID',
            'service_id' => 'Service ID',
            'customer_id' => 'Customer ID',
            'expired_at' => 'Expired At',
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
        return $this->hasMany(CustomerService::className(), ['subscription_id' => 'id']);
    }

    /**
     * Gets query for [[CustomerServiceLogs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomerServiceLogs()
    {
        return $this->hasMany(CustomerServiceLog::className(), ['subscription_id' => 'id']);
    }

    /**
     * Gets query for [[Plan]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlan()
    {
        return $this->hasOne(Plan::className(), ['id' => 'plan_id']);
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
}
