<?php


namespace common\models;


class SubDomain extends \common\models\db\SubDomain
{
    static function getSubDomain()
    {
        $user = \Yii::$app->user->getIdentity();
        $subDomainName = $user->uuid . '.lectron.cloud';
        $subDomain = SubDomain::findOne($subDomainName);
        if (empty($subDomain)) {
            $subDomain = new SubDomain();
            $subDomain->id = $subDomainName;
            $subDomain->domain_id = 'lectron.cloud';
            $subDomain->customer_id = $user->id;
            $subDomain->save(false);
        }
        return $subDomain;
    }
}