<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "sub_domain".
 *
 * @property string $id
 * @property string|null $status
 * @property string|null $domain_id
 * @property int|null $customer_id
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property CustomerService[] $customerServices
 * @property Domain $domain
 * @property User $customer
 */
class SubDomain extends \common\models\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sub_domain';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['customer_id', 'created_at', 'updated_at'], 'integer'],
            [['id'], 'string', 'max' => 200],
            [['status'], 'string', 'max' => 50],
            [['domain_id'], 'string', 'max' => 100],
            [['id'], 'unique'],
            [['domain_id'], 'exist', 'skipOnError' => true, 'targetClass' => Domain::className(), 'targetAttribute' => ['domain_id' => 'id']],
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
            'status' => 'Status',
            'domain_id' => 'Domain ID',
            'customer_id' => 'Customer ID',
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
        return $this->hasMany(CustomerService::className(), ['sub_domain_id' => 'id']);
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
     * Gets query for [[Customer]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCustomer()
    {
        return $this->hasOne(User::className(), ['id' => 'customer_id']);
    }
}
