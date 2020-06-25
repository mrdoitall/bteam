<?php


namespace common\models\cloudflare;


use Cloudflare\API\Adapter\Adapter;
use Cloudflare\API\Endpoints\API;
use Cloudflare\API\Traits\BodyAccessorTrait;

class Spectrum implements API
{
    use BodyAccessorTrait;

    private $adapter;

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
     *
     * @param string $zoneID
     * @param string $type
     * @param string $name
     * @param string $content
     * @param int $ttl
     * @param bool $proxied
     * @param string $priority
     * @param array $data
     * @return bool
     */
    public function addRecord(
        string $zoneID,
        string $origin,
        string $target,
        string $targetPort
    )
    {
        $options = [
            'origin_direct' => [
                "tcp://{$origin}"
            ],
            'dns' => [
                "type" => "CNAME",
                "name" => $target
            ],
            'protocol' => "tcp/{$targetPort}",
            'direct' => true,
            'ip_firewall' => true,
        ];

        $user = $this->adapter->post('zones/' . $zoneID . '/spectrum/apps', $options);

        $this->body = json_decode($user->getBody());
//        returen
        if (isset($this->body->result->id)) {
            return $this->body->result;
        }

        return false;
    }

    public
    function listRecords(
        string $zoneID,
        int $page = 1,
        int $perPage = 20,
        string $order = 'created_on',
        string $direction = 'desc'
    ): \stdClass
    {
        $query = [
            'page' => $page,
            'per_page' => $perPage,
            'direction' => $direction,
            'order' => $order
        ];

        $user = $this->adapter->get('zones/' . $zoneID . '/spectrum/apps', $query);
        $this->body = json_decode($user->getBody());

        return (object)['result' => $this->body->result, 'result_info' => $this->body->result_info];
    }

    public
    function getAnalytics(string $zoneID, string $recordID = '', $fromTime = null, $toTime = null): \stdClass
    {
        $data = [
            'dimensions' => 'event,appID',
            'metrics' => 'bytesIngress,bytesEgress,count',
            'sort' => '+count,-bytesIngress'
        ];

        if (empty($fromTime)) {
            $today = new \DateTime('now');
            $today->setTime(0, 0, 0);
            $fromTime = $today->getTimestamp();
        }

        if (empty($toTime)) {
            $toTime = time();
        }

        $data['since'] = date("Y-m-d\TH:i:s.000\Z", $fromTime);
        $data['until'] = date("Y-m-d\TH:i:s.000\Z", $toTime);

        if (!empty($recordID)) {
            $data['filters'] = "appID=={$recordID}";
        }
        $user = $this->adapter->get('zones/' . $zoneID . '/spectrum/analytics/events/summary', $data);
        $this->body = json_decode($user->getBody());
        return $this->body->result;
    }

    public
    function getRealtimeAnalytic(string $zoneID, string $recordID = ''): array
    {
        $data = [
            'coloName' => '',
            'appID' => $recordID
        ];

        $user = $this->adapter->get('zones/' . $zoneID . '/spectrum/analytics/aggregate/current', $data);
        $this->body = json_decode($user->getBody());
        return $this->body->result;
    }

    public
    function getRecordDetails(string $zoneID, string $recordID): \stdClass
    {
        $user = $this->adapter->get('zones/' . $zoneID . '/spectrum/apps/' . $recordID);
        $this->body = json_decode($user->getBody());
        return $this->body->result;
    }

    public
    function updateRecordDetails(string $zoneID, string $recordID, array $details)
    {
        $response = $this->adapter->put('zones/' . $zoneID . '/spectrum/apps/' . $recordID, $details);
        $this->body = json_decode($response->getBody());
        if ($this->body->success) {
            return $this->body->result;
        }
        return false;
    }

    public
    function deleteRecord(string $zoneID, string $recordID): bool
    {
        $user = $this->adapter->delete('zones/' . $zoneID . '/spectrum/apps/' . $recordID);

        $this->body = json_decode($user->getBody());

        if (isset($this->body->result->id)) {
            return true;
        }

        return false;
    }
}