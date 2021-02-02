<?php

namespace App\Models;

use Illuminate\Support\Facades\DB;

use Illuminate\Database\Eloquent\Model;


class BaseModel extends Model
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
    protected $conn         = null;

    /**
     * @var array
     */
    protected $connPool     = [];

    /**
     * 表明模型是否应该被打上时间戳
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * BaseModel constructor.
     */
    public function __construct()
    {
        parent::__construct();

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
        $this->conn   = $conn;
    }

    /**
     * 原生查询
     * @param $sql
     * @return array
     */
    public function querySql($sql)
    {
        return $this->conn->select($sql);
    }

    /**
     * 汇总求和
     * @param $field
     * @param array $where
     * @return float|int
     */
    public function columnSum(string $field, array $where=[])
    {
       return $this->getBuilder($where)->sum($field);
    }

    /**
     * 统计数量
     * @param $where
     * @param string $field
     * @return float|int|string
     */
    public function rowCount(array $where=[], string $field='*')
    {
        return $this->getBuilder($where)->count($field);
    }

    /**
     * 更新数据
     * @param $where
     * @param $data
     * @return int
     */
    public function updateRows($where, $data)
    {
        return $this->getBuilder($where)->update($data);
    }

    /**
     * 硬删除
     * @param $where
     * @return int
     */
    public function deleteRows($where)
    {
        return $this->getBuilder($where)->delete();
    }

    /**
     * 插入数据
     * @param $data
     * @return int
     */
    public function insertSingle($data)
    {
        return $this->getBuilder()->insertGetId($data);
    }

    /**
     * 插入多行数据
     * @param $data
     * @return bool
     */
    public function insertMultiple($data)
    {
        return $this->getBuilder()->insert($data);
    }

    /**
     * 获取多行数据
     * @param $where
     * @param array $column
     * @param array $order
     * @return array
     */
    public function getMultiple($where, $column=[], $order=[], $limit=null)
    {
        $builder = $this->getBuilder($where, $order);
        $column  = empty($column) ? ['*'] : $column;
        $data    = $builder->limit($limit)->get($column);
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
        $builder = $this->conn->table($this->table);
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
     * 分页数据
     * @param array $where
     * @param array $order
     * @param array $column
     * @param int $pageSize
     * @return array
     */
    public function getPageList($where=[], $order=[], $column=[], $pageSize=15)
    {
        $builder = $this->getBuilder($where, $order);
        $column  = empty($column) ? ['*'] : $column;
        $data    =  $builder->paginate($pageSize, $column)->toArray();
        return   $this->formatPage($this->objectToArray($data));
    }

    /**
     * 分页数据格式化
     * @param $pageData
     * @return array
     */
    private function formatPage($pageData)
    {
        return [
            'has_more_page' => $pageData['current_page'] >= $pageData['last_page'] ? 0 : 1,
            'page'          => $pageData['current_page'],
            'pages'         => $pageData['last_page'],
            'total'         => $pageData['total'],
            'per_page'      => $pageData['per_page'],
            'list'          => $pageData['data'] ?: []
        ];
    }

    /**
     * 获取执行SQL
     * @return array
     */
    public function getQuerySql()
    {
        return $this->conn->getQueryLog();
    }

    /**
     * 开启事务
     */
    public function transStart()
    {
        $this->conn->beginTransaction();
    }

    /**
     * 回滚事务
     */
    public function transRollBack()
    {
        $this->conn->rollBack();
    }

    /**
     * 提交事务
     */
    public function transCommit()
    {
        $this->conn->commit();
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
