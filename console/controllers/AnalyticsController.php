<?php


namespace console\controllers;


use common\components\Spectrum;
use common\models\CustomerService;
use common\models\db\CustomerServiceLog;
use common\models\db\Domain;
use yii\console\Controller;

class AnalyticsController extends Controller
{
    function actionUpdate()
    {
        date_default_timezone_set('UTC');

        $time = time();
        $customerServices = CustomerService::find()
            ->where(['status' => CustomerService::status_active])
            ->andWhere([
                'OR',
                ['<=', 'last_check_at', $time - 300],
                ['<=', 'last_log_at', $time - 1800]
            ])
            ->limit(500)
            ->all();

        $ids = [];

        $domains = Domain::find()->indexBy('id')->all();

        foreach ($customerServices as $customerService) {
            $ids[] = $customerService->id;
        }

        CustomerService::updateAll(['last_check_at' => time()], ['id' => $ids]);

        $spectrumClient = Spectrum::getSpectrumClient();
        foreach ($customerServices as $customerService) {
            $domain = $domains[$customerService->domain_id];

            $thisH = new \DateTime('now');
            $thisH->setTimezone(new \DateTimeZone('UTC'));
            $thisH->setTime($thisH->format('H'), 0, 0);
            $from = $thisH->getTimestamp();
            $preFrom = $from - 3600;
            $nextH = $from + 3600;

            $preData = $spectrumClient->getAnalytics($domain->zone_id, $customerService->ref_id, $preFrom, $preFrom + 3599);
            $nextData = $spectrumClient->getAnalytics($domain->zone_id, $customerService->ref_id, $nextH, $nextH + 3599);
            $data = $spectrumClient->getAnalytics($domain->zone_id, $customerService->ref_id, $from, $from + 3599);

            $save = false;
            if (isset($preData->totals)) {
                static::processAnalyticsData($customerService, $preData, $preFrom);
                $save = true;
            }
            if (isset($data->totals)) {
                static::processAnalyticsData($customerService, $data, $from);
                $save = true;
            }

            if (isset($nextData->totals)) {
                static::processAnalyticsData($customerService, $nextData, $nextH);
                $save = true;
            }

            if ($save) {
                $customerService->last_log_at = time();
                $customerService->save(false);
            }

        }
    }

    static function processAnalyticsData(\common\models\db\CustomerService $customerService, $data, $time)
    {
        $logRow = CustomerServiceLog::findOne(['customer_service_id' => $customerService->id, 'log_time' => $time]);

        if (empty($logRow)) {
            $logRow = new CustomerServiceLog();
            $logRow->customer_service_id = $customerService->id;
            $logRow->log_time = $time;
            $logRow->service_id = $customerService->service_id;
            $logRow->customer_id = $customerService->customer_id;
            $logRow->subscription_id = $customerService->subscription_id;
        }

        $logRow->value = $data->totals->bytesEgress;
        $logRow->value2 = $data->totals->bytesIngress;
        $logRow->value3 = $data->totals->count;

        $logRow->save(false);
        return $logRow;
    }
}