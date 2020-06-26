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

        $pageCount = \Yii::$app->request->post('page_count');
        $bmCount = \Yii::$app->request->post('bm_count');
        $note = \Yii::$app->request->post('note');

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
            $roundRobinUser = User::find()->where(['base_role' => 'user'])->orderBy('last_get_account_at ASC')->one();
            $roundRobinUser->last_get_account_at = (microtime(true) * 1000);
            $roundRobinUser->save(false);
            $account->process_user_id = $roundRobinUser->id;
        }
        $account->cookie = $cookie;
        $account->ip = $ip;
        if (!empty($note)) {
            $account->note = $note;
        }
        if (!empty($pageCount)) {
            $account->page_count = $pageCount;
        }
        if (!empty($bmCount)) {
            $account->bm_count = $bmCount;
        }

        $account->save(false);

        \Yii::$app->queue->push(new UpdateCountryJob([
            'id' => $account->id
        ]));


        return $this->success();

    }
}