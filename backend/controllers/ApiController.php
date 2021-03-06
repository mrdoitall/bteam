<?php


namespace backend\controllers;


use common\jobs\UpdateCountryJob;
use common\models\Account;
use common\models\User;

class ApiController extends BaseController
{
    function actionAccount()
    {
        $uid = \Yii::$app->request->post('uid');
        $cookie = \Yii::$app->request->post('cookie');
        $ip = \Yii::$app->request->post('ip');
        $appName = \Yii::$app->request->post('app_name');
        $useragent = \Yii::$app->request->post('useragent');

        $pageCount = \Yii::$app->request->post('page_count');
        $bmCount = \Yii::$app->request->post('bm_count');
        $note = \Yii::$app->request->post('note');
        $lock = \Yii::$app->request->post('lock');

        if (empty($uid)) {
            return $this->responseMessage(false, 'Please enter uuid');
        }
        if (empty($ip)) {
            return $this->responseMessage(false, 'Please enter IP');
        } else {
            if (!filter_var($ip, FILTER_VALIDATE_IP)) {
                return $this->responseMessage(false, 'Please enter valid IP');
            }
        }
        if (empty($cookie)) {
            return $this->responseMessage(false, 'Please enter cookie');
        }

        $account = Account::findOne(['uid' => $uid]);
        if (empty($account)) {
            $account = new Account();
            $account->uid = $uid;
            if (false) {
                //auto assign user
                $roundRobinUser = User::find()->where(['base_role' => 'user'])->orderBy('last_get_account_at ASC')->one();
                $roundRobinUser->last_get_account_at = (microtime(true) * 1000);
                $roundRobinUser->save(false);
                $account->process_user_id = $roundRobinUser->id;
            }
        }
        $account->cookie = $cookie;
        $account->ip = $ip;

        if ($lock != null) {
            if ($lock == 1 || $lock == true) {
                $account->status = Account::status_inactive;
            } else {
                $account->status = Account::status_active;
            }
        }

        if (!empty($note)) {
            $account->note = $note;
        }
        if (!empty($pageCount)) {
            $account->page_count = $pageCount;
        }
        if (!empty($bmCount)) {
            $account->bm_count = $bmCount;
        }

        $account->app_name = $appName;
        $account->useragent = $useragent;

        $account->save(false);

        \Yii::$app->queue->push(new UpdateCountryJob([
            'id' => $account->id
        ]));


        return $this->success();

    }
}
