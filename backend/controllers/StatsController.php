<?php


namespace backend\controllers;


use common\models\AccountProcessLog;

class StatsController extends BaseController
{
    public $protectActions = ['stats'];

    function actionStats()
    {
        date_default_timezone_set("Asia/Ho_Chi_Minh");
//        date_default_timezone_set('UTC');

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

        $counts = [];

        $userId = \Yii::$app->user->getId();

        foreach ($separates as $separate) {

            if ($separate['start'] > time()) {
                break;
            }

            $cond = AccountProcessLog::find()->where(
                [
                    'AND',
                    ['>=', 'created_at', $separate['start']],
                    ['<', 'created_at', $separate['end']]
                ]
            )->select('account_id')->andWhere(['process_user_id' => $userId]);

            $count = $cond->distinct()->count('account_id');

            $counts[] = [
                'name' => $separate['label'],
                'value' => $count > 0 ? intval($count) : 0
            ];

        }

        return $this->response(true, [
            'counts' => $counts,
        ]);

    }

    function actionAdvanceStats()
    {
        date_default_timezone_set("Asia/Ho_Chi_Minh");
//        date_default_timezone_set('UTC');
        $userId = \Yii::$app->request->get('user_id');
        $accountId = \Yii::$app->request->get('account_id');

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

        $counts = [];

//        $userId = \Yii::$app->user->getId();

        foreach ($separates as $separate) {

            if ($separate['start'] > time()) {
                break;
            }

            $cond = AccountProcessLog::find()->where(
                [
                    'AND',
                    ['>=', 'created_at', $separate['start']],
                    ['<', 'created_at', $separate['end']]
                ]
            )->select('account_id');

            if (!empty($userId)) {
                $cond->andWhere(['process_user_id' => $userId]);
            }

            if (!empty($accountId)) {
                $cond->andWhere(['account_id' => $accountId]);
            }

            $count = $cond->distinct()->count('account_id');

            $counts[] = [
                'name' => $separate['label'],
                'value' => $count > 0 ? intval($count) : 0
            ];

        }

        return $this->response(true, [
            'counts' => $counts,
        ]);

    }
}