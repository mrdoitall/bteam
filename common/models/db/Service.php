<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "service".
 *
 * @property int $id
 * @property string|null $name
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property CustomerService[] $customerServices
 * @property CustomerServiceLog[] $customerServiceLogs
 * @property Plan[] $plans
 * @property Subscription[] $subscriptions
 */
class Service extends \common\models\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'service';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 200],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
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
        return $this->hasMany(CustomerService::className(), ['service_id' => 'id']);
    }

    /**
     * Gets query for [[CustomerServiceLogs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomerServiceLogs()
    {
        return $this->hasMany(CustomerServiceLog::className(), ['service_id' => 'id']);
    }

    /**
     * Gets query for [[Plans]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPlans()
    {
        return $this->hasMany(Plan::className(), ['service_id' => 'id']);
    }

    /**
     * Gets query for [[Subscriptions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSubscriptions()
    {
        return $this->hasMany(Subscription::className(), ['service_id' => 'id']);
    }
}
