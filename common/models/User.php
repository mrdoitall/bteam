<?php

namespace common\models;

use Firebase\JWT\JWT;
use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * User model
 *
 * @property integer $id
 * @property string $username
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $verification_token
 * @property string $email
 * @property string $auth_key
 * @property integer $status
 * @property integer $created_at
 * @property integer $updated_at
 * @property string $password write-only password
 */
class User extends \common\models\db\User implements IdentityInterface
{
    const status_deleted = 'deleted';
    const status_inactive = 'inactive';
    const status_active = 'active';
    const status_suspended = 'suspended';

    public static $statusLabels = [
        'status' => [
            User::status_active => 'Kích hoạt',
            User::status_suspended => 'Chặn',
        ]
    ];

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            ['status', 'default', 'value' => self::status_inactive],
            ['status', 'in', 'range' => [self::status_active, self::status_inactive, self::status_deleted]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($id)
    {
        return static::findOne(['id' => $id, 'status' => self::status_active]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username, 'status' => self::status_active]);
    }

    /**
     * Finds user by password reset token
     *
     * @param string $token password reset token
     * @return static|null
     */
    public static function findByPasswordResetToken($token)
    {
        if (!static::isPasswordResetTokenValid($token)) {
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            'status' => self::status_active,
        ]);
    }

    /**
     * Finds user by verification email token
     *
     * @param string $token verify email token
     * @return static|null
     */
    public static function findByVerificationToken($token)
    {
        return static::findOne([
            'verification_token' => $token,
            'status' => self::status_inactive
        ]);
    }

    /**
     * Finds out if password reset token is valid
     *
     * @param string $token password reset token
     * @return bool
     */
    public static function isPasswordResetTokenValid($token)
    {
        if (empty($token)) {
            return false;
        }

        $timestamp = (int)substr($token, strrpos($token, '_') + 1);
        $expire = Yii::$app->params['user.passwordResetTokenExpire'];
        return $timestamp + $expire >= time();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($this->password_salt . $password, $this->password_hash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_salt = Yii::$app->security->generateRandomString(8);
        $this->password_hash = Yii::$app->security->generatePasswordHash($this->password_salt . $password);
    }

    /**
     * Generates "remember me" authentication key
     */
    public function generateAuthKey()
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    /**
     * Generates new password reset token
     */
    public function generatePasswordResetToken()
    {
        $this->password_reset_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Generates new token for email verification
     */
    public function generateEmailVerificationToken()
    {
        $this->verification_token = Yii::$app->security->generateRandomString() . '_' . time();
    }

    /**
     * Removes password reset token
     */
    public function removePasswordResetToken()
    {
        $this->password_reset_token = null;
    }

    /**
     * @return PublicUser|null
     */
    function getPublicUser()
    {
        return PublicUser::findOne($this->id);
    }

    static function getUserByToken($token)
    {
        try {
            $data = JWT::decode($token, \Yii::$app->params['backendAuthKey'], array('HS256'));
        } catch (\Exception $exception) {
            return false;
        }
        if ($data->expired_at < time()) {
            return false;
        }
        $user = User::findOne($data->id);
        if ($user->password_hash != $data->password_hash || $user->username != $data->username) {
            return false;
        }
        return $user;
    }

    static function authByToken($token)
    {
        $user = static::getUserByToken($token);
        if ($user == false) {
            return false;
        }
        Yii::$app->user->login($user);
        return true;
    }

    /**
     * @return bool|User|null
     */
    static function getAuthUserByHeader()
    {
        $token = str_replace('Bearer ', '', \Yii::$app->request->headers->get('Authorization'));
        return User::getUserByToken($token);
    }

    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            if (empty($this->uuid)) {
                $this->uuid = uniqid($this->id . '-');
                $this->save(false);
            }
        }
        parent::afterSave($insert, $changedAttributes);
    }

    function getAssignedActions()
    {
        $groups = $this->getUserAccessRoles()->select('access_role_id')->indexBy('access_role_id')->asArray()->all();
        $groups = array_keys($groups);
        $groups[] = -1;

        $actions = AccessAssign::find()
            ->where([
                'OR',
                ['user_id' => $this->id],
                ['access_role_id' => $groups],
            ])
            ->select('access_action_id')
            ->indexBy('access_action_id')
            ->asArray()
            ->all();

        $actions = array_keys($actions);

        return $actions;

    }
}
