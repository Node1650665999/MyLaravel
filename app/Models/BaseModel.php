<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

class BaseModel
{
    /**
     * @var string
     */
    protected $connectName = 'mysql';

    /**
     * @var string
     */
    protected $table         = '';


    /**
     * @var \Illuminate\Database\ConnectionInterface|mixed|null
     */
    private $connection         = null;

    /**
     * @var \Illuminate\Database\Query\Builder|null
     */
    private $queryBuilder       = null;

    /**
     * @var array
     */
    private $connPool     = [];

    /**
     * @var array
     */
    private static $connectPool  = [];

    /**
     * BaseModel constructor.
     */
    public function __construct()
    {
        if (! $this->connPool[$this->connectName])
        {
            $conn = DB::connection($this->connectName);
            $this->connPool[$this->connectName] = $conn;
        }
        else
        {
            $conn = $this->connPool[$this->connectName];
        }

        $conn->enableQueryLog();
        $this->connection   = $conn;

        $this->setQueryBuilder($this->table);
    }

    /**
     * @return \Illuminate\Database\ConnectionInterface|mixed|null
     */
    protected function getConnection()
    {
        return $this->connection;
    }


    /**
     * @param $table
     */
    protected function setQueryBuilder($table)
    {
        $this->queryBuilder = $this->connection->table($table);
    }

    /**
     * 原生查询
     * @param $sql
     * @return array
     */
    public function querySql($sql)
    {
        return $this->connection->select($sql);
    }

    /**
     * 更新数据
     * @param $where
     * @param $data
     * @return int
     */
    public function update($where, $data)
    {
        return $this->getBuilder($where)->update($data);
    }

    /**
     * 软删除
     * @param $where
     * @return int
     */
    public function deleteSoft($where)
    {
        $data = ['is_del' => 1, 'updated_at' => currentTime()];
        return $this->update($where, $data);
    }

    /**
     * 硬删除
     * @param $where
     * @return int
     */
    public function delete($where)
    {
        return $this->getBuilder($where)->delete();
    }

    /**
     * 插入数据
     * @param $data
     * @return int
     */
    public function insert($data)
    {
        return $this->getBuilder()->insertGetId($data);
    }

    /**
     * 获取多行数据
     * @param $where
     * @param array $column
     * @param array $order
     * @return array
     */
    public function getMultiple($where, $column=[], $order=[])
    {
        $builder = $this->getBuilder($where, $order);
        $column  = empty($column) ? ['*'] : $column;
        $data    = $builder->get($column);
        return $data ? $this->objectToArray($data) : [];
    }

    /**
     * 获取单行数据
     * @param array $where
     * @param array $column
     * @param array $order
     * @return array
     */
    public function getSingle($where=[], $column=[], $order=[])
    {
        $builder = $this->getBuilder($where, $order);
        $column  = empty($column) ? ['*'] : $column;
        $data    =  $builder->first($column);
        return $data ? $this->objectToArray($data) : [];
    }

    /**
     * 获取 Builder
     * @param array $where
     * @param array $order
     * @return array|\Illuminate\Database\Query\Builder|null
     */
    private function getBuilder($where=[], $order=[])
    {
        $builder = $this->queryBuilder;
        $builder = $this->getWhere($where, $builder);
        $builder = $this->getOrder($order, $builder);
        return   $builder;
    }

    /**
     * 构造 where 条件
     * @param array $where
     * @param \Illuminate\Database\Query\Builder $builder
     * @return array|\Illuminate\Database\Query\Builder|null
     */
    private function getWhere($where, $builder)
    {
        if(! $where || ! is_array($where))
        {
            return $builder;
        }

        if(! isTwoDimensional($where))
        {
            $where = [$where];
        }

        foreach ($where as $val)
        {
            if(trim($val[1]) == 'in')
            {
                $builder = $builder->whereIn($val[0], $val[2]);
            }
            else
            {
                $builder = $builder->where($val[0], $val[1], $val[2]);
            }
        }

        return $builder;
    }

    /**
     * 构造排序
     * @param array $order
     * @param \Illuminate\Database\Query\Builder $builder
     * @return array|\Illuminate\Database\Query\Builder|null
     */
    private function getOrder($order, $builder)
    {
        $order = array_filter($order);
        if(! $order)
        {
            return $builder;
        }

        foreach ($order as $column => $direction)
        {
            $builder = $builder->orderBy($column, $direction);
        }

        return $builder;
    }

    /**
     * @param $where
     * @param $order
     * @param array $column
     * @param $pageSize
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPage($where, $order, $column=[], $pageSize)
    {
        $builder = $this->getBuilder($where, $order);
        $column  = empty($column) ? ['*'] : $column;
        return $builder->paginate($pageSize, $column);
    }


    /**
     * 获取执行SQL
     * @return array
     */
    public function getQuerySql()
    {
        return $this->connection->getQueryLog();
    }

    /**
     * 开启事务
     */
    public function transStart()
    {
        $this->connection->beginTransaction();
    }

    /**
     * 回滚事务
     */
    public function transRollBack()
    {
        $this->connection->rollBack();
    }

    /**
     * 提交事务
     */
    public function transCommit()
    {
        $this->connection->commit();
    }

    /**
     * 对象转数据
     * @param null $object
     * @return array
     */
    protected function objectToArray($object = null)
    {
        return $object ? json_decode(json_encode($object), true) : [];
    }
}
