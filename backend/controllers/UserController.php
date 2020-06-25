<?php


namespace backend\controllers;


use common\models\Paging;
use common\models\User;

class UserController extends BaseController
{
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
        $dbUser->email = $user['email'];
//        $dbUser->phone = $user['phone'];
        $dbUser->status = $user['status'] == User::status_active ? User::status_active : User::status_suspended;

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
        $status = \Yii::$app->request->get('status');
        $sortBy = \Yii::$app->request->get('sort_by');

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
