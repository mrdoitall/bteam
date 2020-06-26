<?php

namespace common\models\db;

use Yii;

/**
 * This is the model class for table "account_process_log".
 *
 * @property int $id
 * @property bool|null $success
 * @property string|null $process_ip
 * @property int|null $process_user_id
 * @property int|null $ads_count
 * @property int|null $bm_count
 * @property int|null $page_count
 * @property string|null $note
 * @property int|null $account_id
 * @property int|null $created_at
 * @property int|null $updated_at
 *
 * @property Account $account
 * @property User $processUser
 */
class AccountProcessLog extends \common\models\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'account_process_log';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['success'], 'boolean'],
            [['process_user_id', 'ads_count', 'bm_count', 'page_count', 'account_id', 'created_at', 'updated_at'], 'integer'],
            [['process_ip'], 'string', 'max' => 50],
            [['note'], 'string', 'max' => 200],
            [['account_id'], 'exist', 'skipOnError' => true, 'targetClass' => Account::className(), 'targetAttribute' => ['account_id' => 'id']],
            [['process_user_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['process_user_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'success' => 'Success',
            'process_ip' => 'Process Ip',
            'process_user_id' => 'Process User ID',
            'ads_count' => 'Ads Count',
            'bm_count' => 'Bm Count',
            'page_count' => 'Page Count',
            'note' => 'Note',
            'account_id' => 'Account ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[Account]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAccount()
    {
        return $this->hasOne(Account::className(), ['id' => 'account_id']);
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
}
