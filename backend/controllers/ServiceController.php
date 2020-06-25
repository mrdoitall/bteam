<?php


namespace backend\controllers;


use common\models\CustomerService;
use common\models\db\CustomerServiceLog;

class ServiceController extends BaseController
{
    public $protectActions = ['my-services-select-2', 'stats'];

    function actionMyServicesSelect2()
    {
        $customerServices = CustomerService::find()
            ->where(['status' => CustomerService::status_active, 'customer_id' => \Yii::$app->user->getId()])
            ->select('id, sub_domain_id, port')
            ->asArray()
            ->all();

        $data = [
            ['id' => 'all', 'text' => 'All']
        ];
        foreach ($customerServices as $customerService) {
            $data[] = ['id' => $customerService['id'], 'text' => $customerService['sub_domain_id'] . ':' . $customerService['port']];
        }

        return $this->response(true, $data);
    }

    function actionStats()
    {
//        date_default_timezone_set("Asia/Ho_Chi_Minh");
        date_default_timezone_set('UTC');

        $breakBy = \Yii::$app->request->get('view_by', 'day');
        $quickView = \Yii::$app->request->get('quick_view', 'this_month');
        $dateRange = \Yii::$app->request->get('date_range');
        $service_id = \Yii::$app->request->get('service', 'all');

        if ($service_id == 'all') {
            $service_id = null;
        }

        $today = new \DateTime();
        $today->setTime(23, 50, 59);

        if ($quickView == 'this_month') {

            $firstDay = new \DateTime('first day of this month');
            $firstDay->setTime(0, 0, 0);
            $lastDay = new \DateTime('last day of this month');
            $lastDay->setTime(23, 59, 59);

        } else if ($quickView == 'this_week') {
            $firstDay = new \DateTime('monday this week');
            $firstDay->setTime(0, 0, 0);
            $lastDay = new \DateTime('sunday this week');
            $lastDay->setTime(23, 59, 59);

        } elseif ($quickView == 'last_week') {
            $firstDay = new \DateTime('monday last week');
            $firstDay->setTime(0, 0, 0);
            $lastDay = new \DateTime('sunday last week');
            $lastDay->setTime(23, 59, 59);

        } else if ($quickView == 'today') {
            $firstDay = new \DateTime('today');
            $firstDay->setTime(0, 0, 0);
            $lastDay = new \DateTime('today');
            $lastDay->setTime(23, 59, 59);
            $breakBy = 'hour';
        } else if ($quickView == 'previous_month') {
            $firstDay = new \DateTime('first day of previous month');
            $firstDay->setTime(0, 0, 0);
            $lastDay = new \DateTime('last day of previous month');
            $lastDay->setTime(23, 59, 59);
        } else if ($quickView == 'custom') {
            $rangeData = explode(' ', $dateRange);
            $firstDay = \DateTime::createFromFormat('d/m/Y H:i:s', $rangeData[0] . ' 00:00:00');
            $lastDay = \DateTime::createFromFormat('d/m/Y H:i:s', $rangeData[1] . ' 23:59:59');
        }

        $processTime = $firstDay->getTimestamp();

        $separates = [];

        while (true) {
            $outData = [
                'start' => $processTime,
                'end' => 0,
                'label' => ''
            ];
            $dateObject = new \DateTime();
            $dateObject->setTimestamp($processTime);

            if ($breakBy == 'hour') {
                $outData['end'] = $outData['start'] + 3600;
                $outData['label'] = $dateObject->format('H:i-d/m');
            } else if ($breakBy == 'day') {
                $outData['end'] = $outData['start'] + 86400;
                $outData['label'] = $dateObject->format('d/m');
            } else {
                $outData['label'] = $dateObject->format('m/Y');
                $lastDayThisMonth = new \DateTime($dateObject->format('Y-m-t'));
                $lastDayThisMonth->setTime(23, 59, 59);
                $outData['end'] = $lastDayThisMonth->getTimestamp() + 1;
            }
            $separates[] = $outData;
            if ($outData['end'] >= $lastDay->getTimestamp() + 1) {
                break;
            }
            $processTime = $outData['end'];
        }

        $bars = [];
        $lineIn = [];
        $lineOut = [];

        $customerId = \Yii::$app->user->getId();

        foreach ($separates as $separate) {

            if ($separate['start'] > time()) {
                break;
            }

            $cond = CustomerServiceLog::find()->where(
                [
                    'AND',
                    ['>=', 'log_time', $separate['start']],
                    ['<', 'log_time', $separate['end']]
                ]
            )->andWhere(['customer_id' => $customerId]);

            if (!empty($service_id)) {
                $cond->andWhere(['customer_service_id' => $service_id]);
            }


            $bwOut = $cond->sum('value');
            $bwIn = $cond->sum('value2');
            $connections = $cond->sum('value3');

            $lineOut[] = [
                'name' => $separate['label'],
                'value' => ($bwOut > 0 ? intval($bwOut) : 0) / 1048576
            ];

            $lineIn[] = [
                'name' => $separate['label'],
                'value' => ($bwIn > 0 ? intval($bwIn) : 0) / 1048576
            ];

            $bars[] = [
                'name' => $separate['label'],
                'value' => $connections > 0 ? intval($connections) : 0
            ];

        }

        return $this->response(true, [
            'connections' => $bars,
            'bw' => [
                [
                    'series' => $lineOut,
                    'name' => 'Out'
                ],
                [
                    'series' => $lineIn,
                    'name' => 'In'
                ]
            ]
        ]);

    }
}