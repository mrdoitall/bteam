<?php


namespace common\models;


use yii\db\Query;

class Pagination extends \yii\data\Pagination
{
    public $pageSizeParam = 'per_page';

    public $page = 1;
    public $pageSize = 24;

    function handleByData($data)
    {
        $this->page = isset($data['page']) && $data['page'] > 0 ? $data['page'] : $this->page;
        $this->pageSize = isset($data['per_page']) ? $data['per_page'] : $this->pageSize;


        function setByQuery(Query &$query)
        {
            $query->limit($this->getLimit());
            $query->offset($this->getOffSet());
            $this->totalCount = $query->count('0');
        }
    }

    function setTotalCount($total = 0)
    {
        $this->totalCount = $total > 0 ? $total : 0;
    }
}