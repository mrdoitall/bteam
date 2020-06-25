<?php

namespace backend\controllers;


use common\models\PublicUser;
use common\models\User;
use common\models\UserAccessToken;
use Firebase\JWT\JWT;

class AuthController extends BaseController
{
    public $isPublic = true;

    function actionAuth()
    {
        $loginInfo = \Yii::$app->request->post('loginForm');
//        return $this->response(true, $loginInfo);
        $user = User::findOne(['username' => strtolower($loginInfo['username'])]);

        if (empty($user)) {
            return $this->response(false, null, 'Login information is incorrect, please try again');
        }

        try {
            if (!$user->validatePassword($loginInfo['password'])) {
                return $this->response(false, null, 'Login information is incorrect, please try again');
            }
        } catch (\Exception $exception) {
            return $this->response(false, null, 'Login information is incorrect, please try again');
        }

        if ($user->status != User::status_active) {
            return $this->response(false, null, 'The account has been locked.');
        }

        $publicUser = $user->getPublicUser();

        $publicUser->auth_key = JWT::encode(
            [
                'id' => $user->id,
                'username' => $user->username,
                'password_hash' => $user->password_hash,
                'user_agent' => \Yii::$app->request->getUserAgent(),
                'expired_at' => (time() + 86400),
                'refresh_token' => \Yii::$app->security->generateRandomString(32)
            ],
            \Yii::$app->params['backendAuthKey']
        );


        return $this->response(true, $publicUser, '', '', 200, null, ['access_assigns' => $publicUser->getAssignedActions()]);
    }

    function actionAuthor()
    {
        $accessTokenKey = \Yii::$app->request->post('access_token');
        if (empty($accessTokenKey)) {
            return $this->response(false);
        }

        $user = User::getUserByToken($accessTokenKey);

        if ($user == false) {
            return $this->response(false, null, 'Please login again');
        }

        $publicUser = $user->getPublicUser();
        $publicUser->auth_key = $accessTokenKey;
        return $this->response(true, $publicUser, '', '', 200, null, ['access_assigns' => $publicUser->getAssignedActions()]);

    }
}