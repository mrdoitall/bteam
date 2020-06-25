<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "customer_service".
 *
 * @property int $id
 * @property string|null $status
 * @property string|null $ref_id
 * @property int|null $customer_id
 * @property int|null $service_id
 * @property int|null $subscription_id
 * @property string|null $sub_domain_id
 * @property string|null $domain_id
 * @property int|null $port
 * @property string|null $origin_ip
 * @property int|null $origin_port
 * @property int|null $last_check_at
 * @property int|null $last_log_at
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property User $customer
 * @property Service $service
 * @property Subscription $subscription
 * @property SubDomain $subDomain
 * @property Domain $domain
 * @property CustomerServiceLog[] $customerServiceLogs
 */
class CustomerService extends \common\models\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'customer_service';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['customer_id', 'service_id', 'subscription_id', 'port', 'origin_port', 'last_check_at', 'last_log_at', 'created_at', 'updated_at'], 'integer'],
            [['status'], 'string', 'max' => 50],
            [['ref_id', 'sub_domain_id'], 'string', 'max' => 200],
            [['domain_id'], 'string', 'max' => 100],
            [['origin_ip'], 'string', 'max' => 20],
            [['customer_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['customer_id' => 'id']],
            [['service_id'], 'exist', 'skipOnError' => true, 'targetClass' => Service::className(), 'targetAttribute' => ['service_id' => 'id']],
            [['subscription_id'], 'exist', 'skipOnError' => true, 'targetClass' => Subscription::className(), 'targetAttribute' => ['subscription_id' => 'id']],
            [['sub_domain_id'], 'exist', 'skipOnError' => true, 'targetClass' => SubDomain::className(), 'targetAttribute' => ['sub_domain_id' => 'id']],
            [['domain_id'], 'exist', 'skipOnError' => true, 'targetClass' => Domain::className(), 'targetAttribute' => ['domain_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'status' => 'Status',
            'ref_id' => 'Ref ID',
            'customer_id' => 'Customer ID',
            'service_id' => 'Service ID',
            'subscription_id' => 'Subscription ID',
            'sub_domain_id' => 'Sub Domain ID',
            'domain_id' => 'Domain ID',
            'port' => 'Port',
            'origin_ip' => 'Origin Ip',
            'origin_port' => 'Origin Port',
            'last_check_at' => 'Last Check At',
            'last_log_at' => 'Last Log At',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
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
     * Gets query for [[Service]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getService()
    {
        return $this->hasOne(Service::className(), ['id' => 'service_id']);
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
     * Gets query for [[SubDomain]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSubDomain()
    {
        return $this->hasOne(SubDomain::className(), ['id' => 'sub_domain_id']);
    }

    /**
     * Gets query for [[Domain]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDomain()
    {
        return $this->hasOne(Domain::className(), ['id' => 'domain_id']);
    }

    /**
     * Gets query for [[CustomerServiceLogs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomerServiceLogs()
    {
        return $this->hasMany(CustomerServiceLog::className(), ['customer_service_id' => 'id']);
    }
}
