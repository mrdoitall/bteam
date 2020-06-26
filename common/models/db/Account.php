<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "account".
 *
 * @property int $id
 * @property string|null $name
 * @property string|null $status
 * @property string|null $uid
 * @property string|null $cookie
 * @property int|null $page_count
 * @property int|null $bm_count
 * @property int|null $ads_count
 * @property string|null $ip
 * @property string|null $country
 * @property string|null $city
 * @property string|null $zipcode
 * @property string|null $note
 * @property int|null $process_user_id
 * @property int|null $last_process_at
 * @property bool|null $last_process_success
 * @property int|null $last_process_success_at
 * @property int|null $created_user_id
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property User $processUser
 * @property User $createdUser
 * @property AccountCookie[] $accountCookies
 * @property AccountProcessLog[] $accountProcessLogs
 */
class Account extends \common\models\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'account';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['cookie'], 'string'],
            [['page_count', 'bm_count', 'ads_count', 'process_user_id', 'last_process_at', 'last_process_success_at', 'created_user_id', 'created_at', 'updated_at'], 'integer'],
            [['last_process_success'], 'boolean'],
            [['name'], 'string', 'max' => 255],
            [['status', 'uid', 'ip', 'country', 'zipcode'], 'string', 'max' => 50],
            [['city'], 'string', 'max' => 100],
            [['note'], 'string', 'max' => 200],
            [['process_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['process_user_id' => 'id']],
            [['created_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['created_user_id' => 'id']],
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
            'status' => 'Status',
            'uid' => 'Uid',
            'cookie' => 'Cookie',
            'page_count' => 'Page Count',
            'bm_count' => 'Bm Count',
            'ads_count' => 'Ads Count',
            'ip' => 'Ip',
            'country' => 'Country',
            'city' => 'City',
            'zipcode' => 'Zipcode',
            'note' => 'Note',
            'process_user_id' => 'Process User ID',
            'last_process_at' => 'Last Process At',
            'last_process_success' => 'Last Process Success',
            'last_process_success_at' => 'Last Process Success At',
            'created_user_id' => 'Created User ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[ProcessUser]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProcessUser()
    {
        return $this->hasOne(User::className(), ['id' => 'process_user_id']);
    }

    /**
     * Gets query for [[CreatedUser]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedUser()
    {
        return $this->hasOne(User::className(), ['id' => 'created_user_id']);
    }

    /**
     * Gets query for [[AccountCookies]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAccountCookies()
    {
        return $this->hasMany(AccountCookie::className(), ['account_id' => 'id']);
    }

    /**
     * Gets query for [[AccountProcessLogs]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAccountProcessLogs()
    {
        return $this->hasMany(AccountProcessLog::className(), ['account_id' => 'id']);
    }
}
