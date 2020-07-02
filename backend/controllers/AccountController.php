<?php


namespace backend\controllers;


use common\jobs\UpdateCountryJob;
use common\models\Account;
use common\models\AccountProcessLog;
use common\models\Paging;
use yii\helpers\ArrayHelper;

class AccountController extends BaseController
{
    public $protectActions = ['history', 'search', 'update-status', 'get-account'];

    function actionAdvanceHistory()
    {
        $sort = \Yii::$app->request->get('sort');
        $success = \Yii::$app->request->get('success');
        $sortBy = \Yii::$app->request->get('sort_by', 'id');

        $accountId = \Yii::$app->request->get('account_id');
        $userId = \Yii::$app->request->get('user_id');
        $pagingData = new Paging();
        $pagingData->handleByData(\Yii::$app->request->get());
        $userCond = AccountProcessLog::find();

        if (!empty($accountId)) {
            $userCond->where(['account_id' => $accountId]);
        }
        if (!empty($userId)) {
            $userCond->where(['process_user_id' => $userId]);
        }

        if (trim($success) != '') {
            $userCond->where(['success' => $success]);
        }

        $pagingData->setByQuery($userCond);

//        $userCond->orderBy('id desc');

        if (!empty($sort) && !empty($sortBy)) {
            if (in_array($sortBy, ['id'])) {
                $sort = $sort == 'ASC' ? 'ASC' : 'DESC';
            }
            $userCond->orderBy("$sortBy $sort");
        }

        $logs = $userCond->with('processUser', 'account')->asArray()->all();
        return $this->response(true, $logs, null, null, 200, $pagingData);

    }

    function actionViewLockAccount()
    {
        return $this->success();
    }

    function actionGetAccount()
    {
        $accountId = \Yii::$app->request->post('account_id');
        $account = Account::findOne($accountId);
        if (!empty($account->process_user_id)) {
            return $this->responseMessage(false, 'This account is already assigned to another user.');
        }
        $account->process_user_id = \Yii::$app->user->getId();
        $account->save(false);
        return $this->success();
    }

    function actionChangeStatus()
    {
        $accountId = \Yii::$app->request->post('account_id');

        $account = Account::findOne($accountId);
        if ($account->status == Account::status_active) {
            $account->status = Account::status_inactive;
        } else {
            $account->status = Account::status_active;
        }
        $account->save(false);

        return $this->success();

    }

    function actionAdvanceSearch()
    {
        $pagingData = new Paging();
        $sort = \Yii::$app->request->get('sort');
        $userId = \Yii::$app->request->get('user_id');
        $sortBy = \Yii::$app->request->get('sort_by');
        $lastStatus = \Yii::$app->request->get('last_process_success');
        $status = \Yii::$app->request->get('status');

        $keyword = \Yii::$app->request->get('keyword');
        $pagingData->handleByData(\Yii::$app->request->get());
        $userCond = Account::find();

        if (!$this->roleCheck('account/view-lock-account')) {
            $userCond->andWhere(['status' => Account::status_active]);
        }

        if (!empty($userId)) {
            $userCond->andWhere(['process_user_id' => $userId]);
        }

        if (!empty($keyword)) {
            $keyword = strtolower(trim($keyword));
            $userCond->andWhere([
                'OR',
                ['LIKE', 'uid', $keyword],
                ['LIKE', 'ip', $keyword],
                ['LIKE', 'note', $keyword],
            ]);
        }

        if (trim($lastStatus) != '') {
            $userCond->andWhere(['last_process_success' => $lastStatus]);
        }

        if (trim($status) != '') {
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

        $accounts = $userCond->with('processUser')->asArray()->all();

//        User::bindArrayObjectStatusLabel($users);

        return $this->response(true, $accounts, null, null, 200, $pagingData);
    }

    function actionHistory()
    {
        $sort = \Yii::$app->request->get('sort');
        $success = \Yii::$app->request->get('success');
        $sortBy = \Yii::$app->request->get('sort_by', 'id');

        $accountId = \Yii::$app->request->get('account_id');
        $pagingData = new Paging();
        $pagingData->handleByData(\Yii::$app->request->get());
        $userCond = AccountProcessLog::find()->where(['process_user_id' => \Yii::$app->user->getId()]);
        if (!empty($accountId)) {
            $userCond->where(['account_id' => $accountId]);
        }

        if (trim($success) != '') {
            $userCond->where(['success' => $success]);
        }

        $pagingData->setByQuery($userCond);

//        $userCond->orderBy('id desc');

        if (!empty($sort) && !empty($sortBy)) {
            if (in_array($sortBy, ['id'])) {
                $sort = $sort == 'ASC' ? 'ASC' : 'DESC';
            }
            $userCond->orderBy("$sortBy $sort");
        }

        $logs = $userCond->with('processUser', 'account')->asArray()->all();
        return $this->response(true, $logs, null, null, 200, $pagingData);

    }

    function actionSearch()
    {
        $pagingData = new Paging();
        $sort = \Yii::$app->request->get('sort');
        $sortBy = \Yii::$app->request->get('sort_by');
        $status = \Yii::$app->request->get('last_process_success');

        $keyword = \Yii::$app->request->get('keyword');
        $pagingData->handleByData(\Yii::$app->request->get());
        $userCond = Account::find()->where(['status' => Account::status_active, 'process_user_id' => [\Yii::$app->user->getId(), null]]);

        if (!empty($keyword)) {
            $keyword = strtolower(trim($keyword));
            $userCond->andWhere([
                'OR',
                ['LIKE', 'uid', $keyword],
                ['LIKE', 'ip', $keyword],
                ['LIKE', 'note', $keyword],
            ]);
        }

        if (trim($status) != '') {
            $userCond->andWhere(['last_process_success' => $status]);
        }

        $pagingData->setByQuery($userCond);

        $userCond->orderBy('id desc');

        if (!empty($sort) && !empty($sortBy)) {
            if (in_array($sortBy, ['id'])) {
                $sort = $sort == 'ASC' ? 'ASC' : 'DESC';
            }
            $userCond->orderBy("$sortBy $sort");
        }

        $accounts = $userCond->asArray()->all();

//        User::bindArrayObjectStatusLabel($users);

        return $this->response(true, $accounts, null, null, 200, $pagingData);
    }

    function actionUpdateStatus()
    {
        $postAccount = \Yii::$app->request->post('account');
        $success = ArrayHelper::getValue($postAccount, 'new_process_success');
        $newNote = ArrayHelper::getValue($postAccount, 'new_note');
        $account = Account::findOne($postAccount['id']);

        $isSuccess = $success == 1;

        if ($account->process_user_id != \Yii::$app->user->getId()) {
            return $this->responseMessage(false, 'You not have permission to access this account');
        }

        $adsCount = trim(ArrayHelper::getValue($postAccount, 'ads_count'));
        $pageCount = trim(ArrayHelper::getValue($postAccount, 'page_count'));
        $bmCount = trim(ArrayHelper::getValue($postAccount, 'bm_count'));

        if ($adsCount < 0 || $pageCount < 0 || $bmCount < 0) {
            return $this->responseMessage(false, 'Please enter valid numbers');
        }

        $log = new AccountProcessLog();
        $log->success = $isSuccess;
        $log->note = $newNote;
        $log->process_user_id = \Yii::$app->user->getId();
        $log->process_ip = \Yii::$app->request->getUserIP();
        $log->account_id = $account->id;

        if ($isSuccess) {
            $account->bm_count = $bmCount;
            $account->ads_count = $adsCount;
            $account->page_count = $pageCount;

            $log->bm_count = $bmCount;
            $log->ads_count = $adsCount;
            $log->page_count = $pageCount;
            $account->last_process_success_at = time();
        }

        $log->save(false);
        $account->last_process_at = time();
        $account->last_process_success = $isSuccess;

        if (!empty($newNote)) {
            $account->note = $newNote;
        }

        $account->save(false);

        return $this->success();
    }
}