<?php


namespace common\components;


class Spectrum
{
    /**
     * @return \common\models\cloudflare\Spectrum(
     */
    static function getSpectrumClient()
    {
        $key = new \Cloudflare\API\Auth\APIKey('ducquan135@live.com', '02cb2defff95e090737b451b19978958c3862');
        $adapter = new \Cloudflare\API\Adapter\Guzzle($key);
        return new \common\models\cloudflare\Spectrum($adapter);
    }
}