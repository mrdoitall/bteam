<?php


namespace common\utils;


class Url extends \yii\helpers\Url
{
    public static function current(array $params = [], $scheme = false)
    {
        $currentParams = \Yii::$app->getRequest()->getQueryParams();
        $currentParams[0] = '/' . \Yii::$app->controller->getRoute();
//        $currentParams[0] = '/ao-thun-nam';
        $url = \Yii::$app->request->getUrl();
        $currentParams[0] = explode('?', $url)[0];

        foreach ($params as $paramName => $paramValue) {
            if (isset($currentParams[$paramName])) {
                unset($currentParams[$paramName]);
            }
            if ($paramValue === null || $paramValue === []) {
                unset($params[$paramName]);
            }
        }
        $route = array_replace_recursive($currentParams, $params);
        return static::toRoute($route, $scheme);
    }
}