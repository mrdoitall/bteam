<?php
/**
 * Created by PhpStorm.
 * User: quannd
 * Date: 1/21/18
 * Time: 00:28
 */

namespace common\utils;


class QUrlParser
{
    public $keyName = 'name', $keyValue = 'value', $objectMode = true;

    /**
     * @param string $keyName
     * @param string $keyValue
     * @return QUrlParser
     */
    static function getInstance($keyName = 'name', $keyValue = 'value')
    {
        $instance = new QUrlParser();
        $instance->keyName = $keyName;
        $instance->keyValue = $keyValue;
        return $instance;
    }

    function newObject($nameValue, $valueValue = [])
    {
        $object = new \stdClass();
        $object->{$this->keyName} = $nameValue;
        $object->{$this->keyValue} = $valueValue;
        return $object;

    }

    function decode($str)
    {
        $outPut = [];
        $firstDecode = urldecode($str);
        $isArray = substr_count($firstDecode, ';') > 0;
        $firstClass = explode(';', $firstDecode);
        foreach ($firstClass as $fistObject) {
            if (empty($fistObject)) {
                continue;
            }
            $fistObjectEx = explode(':', $fistObject);
            $keyKey = $fistObjectEx[0];
            $keyValue = $fistObjectEx[1];
            $keyValueIsArray = substr_count($keyValue, ',') > 0;
            $stdClassOne = new \stdClass();
            $stdClassOne->{$this->keyName} = urldecode($keyKey);
            if ($keyValueIsArray) {
                $keyValueDataTmp = [];
                $keyValues = explode(',', $keyValue);
                foreach ($keyValues as $keyValue) {
                    if (empty($keyValue)) {
                        continue;
                    }
                    $keyValueDataTmp[] = urldecode($keyValue);
                }
                $stdClassOne->{$this->keyValue} = $keyValueDataTmp;
            } else {
                $stdClassOne->{$this->keyValue} = urldecode($keyValue);
            }
            if ($isArray) {
                $outPut[] = $stdClassOne;
            } else {
                return $stdClassOne;
            }
        }
        return $outPut;
    }

    function encode($objects)
    {
//        $isArray = is_array($objects);
        if (!is_array($objects)) {
            $objects = [$objects];
        }
        $outputStr = [];
        foreach ($objects as $object) {
            $key = $object->{$this->keyName};
            $value = $object->{$this->keyValue};
            if (is_array($value)) {
                $valueTmp = [];
                foreach ($value as $sValue) {
                    $valueTmp[] = urlencode($sValue);
                }
                $subObjValueOut = implode(',', $valueTmp) . ',';
            } else {
                $subObjValueOut = urlencode($value);
            }
            $outputStr[] = (urlencode($key) . ':' . $subObjValueOut);
        }
        $outputStr = implode(';', $outputStr);
        return urlencode($outputStr);
    }
}