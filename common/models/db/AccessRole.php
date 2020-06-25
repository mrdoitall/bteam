<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "access_role".
 *
 * @property int $id
 * @property string|null $name
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property AccessAssign[] $accessAssigns
 * @property UserAccessRole[] $userAccessRoles
 */
class AccessRole extends \common\models\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'access_role';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['created_at', 'updated_at'], 'integer'],
            [['name'], 'string', 'max' => 100],
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
     * Gets query for [[AccessAssigns]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAccessAssigns()
    {
        return $this->hasMany(AccessAssign::className(), ['access_role_id' => 'id']);
    }

    /**
     * Gets query for [[UserAccessRoles]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserAccessRoles()
    {
        return $this->hasMany(UserAccessRole::className(), ['access_role_id' => 'id']);
    }
}
