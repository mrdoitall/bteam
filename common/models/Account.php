<?php


namespace common\models;


class Account extends \common\models\db\Account
{
    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $cookie = $this->getAccountCookies()->orderBy('id DESC')->one();
        if ($cookie == null || $cookie->cookie != $this->cookie) {
            $newCookie = new AccountCookie();
            $newCookie->cookie = $this->cookie;
            $newCookie->user_id = \Yii::$app->user->getId();
            $newCookie->account_id = $this->id;
            $newCookie->save(false);
        }
    }
}