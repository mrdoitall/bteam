<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "plan".
 *
 * @property int $id
 * @property string|null $name
 * @property float|null $price
 * @property int|null $service_id
 * @property int|null $value
 * @property string|null $unit_name
 * @property int|null $limit_time
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property Service $service
 * @property Subscription[] $subscriptions
 */
class Plan extends \common\models\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'plan';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'required'],
            [['id', 'service_id', 'value', 'limit_time', 'created_at', 'updated_at'], 'integer'],
            [['price'], 'number'],
            [['name'], 'string', 'max' => 200],
            [['unit_name'], 'string', 'max' => 50],
            [['id'], 'unique'],
            [['service_id'], 'exist', 'skipOnError' => true, 'targetClass' => Service::className(), 'targetAttribute' => ['service_id' => 'id']],
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
            'price' => 'Price',
            'service_id' => 'Service ID',
            'value' => 'Value',
            'unit_name' => 'Unit Name',
            'limit_time' => 'Limit Time',
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
     * Gets query for [[Subscriptions]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSubscriptions()
    {
        return $this->hasMany(Subscription::className(), ['plan_id' => 'id']);
    }
}
