<?php


namespace common\jobs;


use common\models\Account;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;
use yii\httpclient\Client;

class UpdateCountryJob extends BaseObject implements \yii\queue\JobInterface
{
    public $id;

    public function execute($queue)
    {
        $account = Account::findOne($this->id);

        if (!empty($account)) {
            if (filter_var($account->ip, FILTER_VALIDATE_IP)) {

                $client = new Client();
                $response = $client->createRequest()
                    ->setMethod('GET')
                    ->setUrl('https://freegeoip.app/json/' . $account->ip)
                    ->send();
                $data = $response->getData();
                $account->country = ArrayHelper::getValue($data, 'country_code');
                $account->city = ArrayHelper::getValue($data, 'city');
                $account->zipcode = ArrayHelper::getValue($data, 'zip_code');
                $account->save(false);
            }
        }

    }
}