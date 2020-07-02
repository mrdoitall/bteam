<?php


namespace backend\controllers;


use common\models\Paging;
use common\models\User;
use common\models\UserLog;
use Firebase\JWT\JWT;

class UserController extends BaseController
{
    public $protectActions = ['change-pass', 'login-history'];

    function actionAdvanceLoginHistory()
    {
        $sort = \Yii::$app->request->get('sort');
        $sortBy = \Yii::$app->request->get('sort_by');
        $ip = \Yii::$app->request->get('ip');

        $userId = \Yii::$app->request->get('user_id');
        $pagingData = new Paging();
        $pagingData->handleByData(\Yii::$app->request->get());
        $userCond = UserLog::find()->where(['type' => UserLog::type_login]);
        if (!empty($userId)) {
            $userCond->andWhere(['user_id' => $userId]);
        }

        if (!empty($ip)) {
            $userCond->andWhere(['LIKE', 'ip', $ip]);
        }

        $pagingData->setByQuery($userCond);

        if (!empty($sort) && !empty($sortBy)) {
            if (in_array($sortBy, ['id'])) {
                $sort = $sort == 'ASC' ? 'ASC' : 'DESC';
            }
            $userCond->orderBy("$sortBy $sort");
        }

        $logs = $userCond->with('user')->asArray()->all();
        return $this->response(true, $logs, null, null, 200, $pagingData);

    }

    function actionApiToken()
    {
        $password = \Yii::$app->request->post('password');
        $user = User::findOne(\Yii::$app->user->getId());
        if (!$user->validatePassword($password)) {
            return $this->responseMessage(false, 'Password is incorrect');
        }

        $authKey = JWT::encode(
            [
                'id' => $user->id,
                'username' => $user->username,
                'password_hash' => $user->password_hash,
                'user_agent' => \Yii::$app->request->getUserAgent(),
                'expired_at' => (time() + (86400 * 365)),
                'refresh_token' => \Yii::$app->security->generateRandomString(32)
            ],
            \Yii::$app->params['backendAuthKey']
        );

        return $this->response(true, ['token' => $authKey], 'Get Token Successfully');
    }

    function actionLoginHistory()
    {
        $pagingData = new Paging();
        $pagingData->handleByData(\Yii::$app->request->get());
        $userCond = UserLog::find()->where(['type' => UserLog::type_login]);
        $pagingData->setByQuery($userCond);
        $logs = $userCond->asArray()->orderBy('id DESC')->all();
        return $this->response(true, $logs, null, null, 200, $pagingData);

    }

    function actionChangePass()
    {
        $changePassForm = \Yii::$app->request->post('changePassForm');
        $user = \Yii::$app->user->getIdentity();
        if (!$user->validatePassword($changePassForm['currentPass'])) {
            return $this->response(false, null, 'The old password is incorrect');
        }
        $user->setPassword($changePassForm['newPass']);
        $user->save(false);
        return $this->response(true, null, 'Password successfully changed, please login again');
    }

    function actionUpdateAdminInfo()
    {
        return $this->success();
    }

    function actionUpdate()
    {
        $user = \Yii::$app->request->post('user');
        $user['username'] = trim($user['username']);
        $user['email'] = trim($user['email']);
        $user['phone'] = trim($user['phone']);

        if (empty($user['fullname'])) {
            return $this->responseMessage(false, 'Please enter full name');
        }
        if (empty($user['username'])) {
            return $this->responseMessage(false, 'Please enter Username');
        }
        if (empty($user['email'])) {
            return $this->responseMessage(false, 'Please enter Email');
        }
//        if (empty($user['phone'])) {
//            return $this->responseMessage(false, 'Vui lòng nhập số điện thoại');
//        }
        if (empty($user['status'])) {
            return $this->responseMessage(false, 'Please choose Status');
        }

        $dbUser = new User();

        if (!empty($user['id'])) {
            $dbUser = User::findOne($user['id']);
            if (empty($dbUser)) {
                return $this->responseMessage(false, 'Account does not exist');
            }
        }

        $checkUser = User::findOne(['username' => $user['username']]);
        if (!empty($checkUser) && $dbUser->id != $checkUser->id) {
            return $this->responseMessage(false, 'Username has been taken');
        }
        $checkUser = User::findOne(['email' => $user['email']]);
        if (!empty($checkUser) && $dbUser->id != $checkUser->id) {
            return $this->responseMessage(false, 'Email is already taken');
        }
//        $checkUser = User::findOne(['phone' => $user['phone']]);
//        if (!empty($checkUser) && $dbUser->id != $checkUser->id) {
//            return $this->responseMessage(false, 'Số điện thoại đã được sử dụng');
//        }

        $dbUser->fullname = $user['fullname'];
        $dbUser->username = $user['username'];
        $dbUser->base_role = $user['base_role'];
        $dbUser->email = $user['email'];
//        $dbUser->phone = $user['phone'];
        $dbUser->status = $user['status'] == User::status_active ? User::status_active : User::status_suspended;

        if ($dbUser->base_role == 'admin' && !$this->roleCheck('user/update-admin-info')) {
            return $this->responseMessage(false, 'You do not have permission to update admin information');
        }

        if ((isset($user['changePassword']) && $user['changePassword'] == true) || empty($user['id'])) {

            if (!isset($user['newPassword']) || !isset($user['confirmNewPassword'])) {
                return $this->responseMessage(false, 'Please enter a password');
            }

            if (strlen($user['newPassword']) < 8) {
                return $this->responseMessage(false, 'Password is at least 8 characters');
            }
            if ($user['newPassword'] != $user['confirmNewPassword']) {
                return $this->responseMessage(false, 'Confirmation password does not match');
            }
            $dbUser->setPassword($user['newPassword']);
        }

        return $this->response($dbUser->save(false));

    }

    function actionSearch()
    {
        $pagingData = new Paging();
        $sort = \Yii::$app->request->get('sort');
        $sortBy = \Yii::$app->request->get('sort_by');
        $status = \Yii::$app->request->get('status');
        $role = \Yii::$app->request->get('role');

        $keyword = \Yii::$app->request->get('keyword');
        $pagingData->handleByData(\Yii::$app->request->get());
        $userCond = User::find()->where(['!=', 'status', User::status_deleted]);


        if (!empty($keyword)) {
            $keyword = strtolower(trim($keyword));
            $userCond->andWhere([
                'OR',
                ['username' => $keyword],
                ['phone' => $keyword],
                ['like', 'email', $keyword]
            ]);
        }

        if (!empty($status)) {
            $userCond->andWhere(['status' => $status]);
        }

        if (trim($role) != '') {
            $userCond->andWhere(['base_role' => $role]);
        }

        $pagingData->setByQuery($userCond);

        $userCond->orderBy('id desc');

        if (!empty($sort) && !empty($sortBy)) {
            if (in_array($sortBy, ['id'])) {
                $sort = $sort == 'ASC' ? 'ASC' : 'DESC';
            }
            $userCond->orderBy("$sortBy $sort");
        }

        $users = $userCond->asArray()->all();

        User::bindArrayObjectStatusLabel($users);

        return $this->response(true, $users, null, null, 200, $pagingData);
    }

}
