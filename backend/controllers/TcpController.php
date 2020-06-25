<?php


namespace backend\controllers;


use common\components\Spectrum;
use common\models\CustomerService;
use common\models\Paging;
use common\models\Service;
use common\models\SubDomain;
use yii\base\Exception;
use yii\helpers\ArrayHelper;

class TcpController extends BaseController
{
    public $isProtect = true;

    function actionSubdomainSelect2()
    {
        SubDomain::getSubDomain();
        $subDomains = SubDomain::find()
            ->where(['customer_id' => \Yii::$app->user->getId(), 'status' => SubDomain::status_active])
            ->select('id')
            ->all();

        foreach ($subDomains as $k => $subDomain) {
            $subDomains[$k] = ['id' => $subDomain['id'], 'text' => $subDomain['id']];
        }

        return $this->response(true, $subDomains);
    }

    function actionSearch()
    {
        $pagingData = new Paging();
        $keyword = \Yii::$app->request->get('keyword');
        $pagingData->handleByData(\Yii::$app->request->get());

        $customerCond = CustomerService::find()
            ->andWhere([
                'service_id' => CustomerService::id_tcp_proxy,
                'customer_id' => \Yii::$app->user->getId(),
                'status' => CustomerService::status_active,
            ]);

        $customers = $customerCond->orderBy('id DESC')->asArray()->all();
        $pagingData->setByQuery($customerCond);
        return $this->response(true, $customers, null, null, 200, $pagingData);
    }

    function actionCurrentConnection()
    {
        $proxyId = \Yii::$app->request->post('proxyId');
        $proxy = CustomerService::findOne($proxyId);
        $spectrum = Spectrum::getSpectrumClient();
        $domain = $proxy->domain;
        if ($proxy->customer_id != \Yii::$app->user->getId()) {
            return $this->responseMessage(false, 'You not have permission to access this Proxy');
        }
        $data = $spectrum->getRealtimeAnalytic($domain->zone_id, $proxy->ref_id);

        if (empty($data)) {
            return $this->responseMessage(true, 'No connection is being processed');
        } else {
            $data[0]->bytesIngress = $data[0]->bytesIngress / 1000000;
            $data[0]->bytesEgress = $data[0]->bytesEgress / 1000000;
            return $this->responseMessage(true, "{$data[0]->connections} connections, Ingress: {$data[0]->bytesIngress} megabyte, Egress: {$data[0]->bytesEgress} megabyte");
        }

        return $this->response(true, $data);
    }

    function actionDelete()
    {
        $proxyId = \Yii::$app->request->post('proxyId');
        $proxy = CustomerService::findOne($proxyId);

        if ($proxy->customer_id != \Yii::$app->user->getId()) {
            return $this->responseMessage(false, 'You not have permission to access this Proxy');
        }

        $domain = $proxy->domain;
        $spectrum = Spectrum::getSpectrumClient();

        try {
            $spectrumRecord = $spectrum->deleteRecord($domain->zone_id, $proxy->ref_id);
            if ($spectrumRecord === false) {
                throw new Exception('error');
            }

            $proxy->status = CustomerService::status_deleted;
            $proxy->save(false);

            return $this->success();

        } catch (\Exception $exception) {
            return $this->responseMessage(false, 'There was an error deleting the Proxy, please try again later');
        }

    }

    function actionSave()
    {
        $proxy = \Yii::$app->request->post('proxy');
        $id = ArrayHelper::getValue($proxy, 'id');
        $port = ArrayHelper::getValue($proxy, 'port');
        $originPort = ArrayHelper::getValue($proxy, 'origin_port');
        $originIp = ArrayHelper::getValue($proxy, 'origin_ip');
        $subDomainId = ArrayHelper::getValue($proxy, 'sub_domain_id');

        if (!filter_var($originIp, FILTER_VALIDATE_IP)) {
            return $this->responseMessage(false, 'Please enter valid Origin IP');
        }

        if (empty($originPort) || $originPort < 0 || $originPort > 65535) {
            return $this->responseMessage(false, 'Please enter valid Origin Port');
        }

        if (empty($port) || $port < 0 || $port > 65535) {
            return $this->responseMessage(false, 'Please enter valid Edge Port');
        }

//        $subDomain = SubDomain::getSubDomain();
        $subDomain = SubDomain::findOne($subDomainId);
        if ($subDomain->customer_id != \Yii::$app->user->getId()) {
            return $this->responseMessage(false, 'You not have permission to access this Domain');
        }

        if (empty($id)) {
            $dbProxy = new CustomerService();
            $dbProxy->service_id = 1;
        } else {
            $dbProxy = CustomerService::findOne($id);

            if ($dbProxy->customer_id != \Yii::$app->user->getId()) {
                return $this->responseMessage(false, 'You not have permission to access this Proxy');
            }
        }

        if ($dbProxy->isNewRecord) {
            $dbProxy->customer_id = \Yii::$app->user->getId();
            $dbProxy->status = CustomerService::status_active;
            $dbProxy->sub_domain_id = $subDomain->id;
            $dbProxy->domain_id = $subDomain->domain_id;
        } else {

            if ($port == $dbProxy->getAttribute('port') && $originPort == $dbProxy->getAttribute('origin_port') && $originIp == $dbProxy->getAttribute('origin_ip')) {
                return $this->success();
            }

        }

        $dbProxy->port = $port;
        $dbProxy->origin_ip = $originIp;
        $dbProxy->origin_port = $originPort;


        $checkPort = CustomerService::findOne(['status' => CustomerService::status_active, 'port' => $port, 'sub_domain_id' => $subDomain->id]);

        if (!empty($checkPort)) {
            if ($checkPort->id != $dbProxy->id) {
                return $this->responseMessage(false, 'This port is already in use');
            }
        }

        $domain = $subDomain->domain;
        $spectrum = Spectrum::getSpectrumClient();

        if ($dbProxy->isNewRecord) {
            try {
                $spectrumRecord = $spectrum->addRecord($domain->zone_id, $originIp . ':' . $originPort, $subDomain->id, $port);
                if ($spectrumRecord === false) {
                    throw new Exception('error');
                }
                $dbProxy->ref_id = $spectrumRecord->id;
                $dbProxy->save(false);
                return $this->success();
            } catch (\Exception $exception) {
                if (!empty($dbProxy->id)) {
                    $dbProxy->delete();
                }
                return $this->responseMessage(false, 'There was an error creating the Proxy, please try again later');
            }
        } else {
            try {

                $options = [
                    'origin_direct' => [
                        "tcp://" . $originIp . ':' . $originPort
                    ],
                    'dns' => [
                        "type" => "CNAME",
                        "name" => $subDomain->id
                    ],
                    'protocol' => "tcp/" . $port,
                    'direct' => true,
                    'ip_firewall' => true,
                ];


                $spectrumRecord = $spectrum->updateRecordDetails($domain->zone_id, $dbProxy->ref_id, $options);
                if ($spectrumRecord === false) {
                    throw new Exception('error');
                }
                $dbProxy->ref_id = $spectrumRecord->id;
                $dbProxy->save(false);
                return $this->success();
            } catch (\Exception $exception) {
                return $this->responseMessage(false, 'There was an error updating the Proxy, please try again later');
            }
        }

        return $this->responseMessage(false, 'An error occurred, please try again later');
    }
}