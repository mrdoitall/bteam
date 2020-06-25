<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "domain".
 *
 * @property string $id
 * @property string|null $zone_id
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property CustomerService[] $customerServices
 * @property SubDomain[] $subDomains
 */
class Domain extends \common\models\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'domain';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['created_at', 'updated_at'], 'integer'],
            [['id'], 'string', 'max' => 100],
            [['zone_id'], 'string', 'max' => 50],
            [['id'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'zone_id' => 'Zone ID',
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
        return $this->hasMany(CustomerService::className(), ['domain_id' => 'id']);
    }

    /**
     * Gets query for [[SubDomains]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSubDomains()
    {
        return $this->hasMany(SubDomain::className(), ['domain_id' => 'id']);
    }
}
