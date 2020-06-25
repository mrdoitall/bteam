<?php


namespace common\models;


use yii\data\Pagination;
use yii\db\Query;

class Paging
{
    public $page = 1;
    public $per_page = 48;
    public $total = 0;
    public $total_page = 1;

    function handleByData($data)
    {
        $this->page = isset($data['page']) ? $data['page'] : $this->page;
        $this->per_page = isset($data['per_page']) ? $data['per_page'] : $this->per_page;
        if ($this->page < 1) {
            $this->page = 1;
        }
        if ($this->per_page < 1) {
            $this->per_page = 1;
        }
    }

    function getOffSet()
    {
        return $this->page * $this->per_page - $this->per_page;
    }

    function getLimit()
    {
        return $this->per_page;
    }

    function setTotal($total)
    {
        $this->total = $total;
        $this->total_page = ceil($this->total / $this->per_page);
    }

    function setByQuery(Query &$query)
    {
        $query->limit($this->getLimit());
        $query->offset($this->getOffSet());
        $this->setTotal($query->count('0'));
    }

    /**
     * @return Pagination
     */
    function getYiiPaging()
    {
        $pages = new Pagination(['totalCount' => $this->total]);
        $pages->pageSize = $this->per_page;
        $pages->page = $this->page;
        $pages->pageSizeParam = 'per_page';
        return $pages;
    }
}
