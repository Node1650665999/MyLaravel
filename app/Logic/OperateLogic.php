<?php
/**
 * Logic
 * @Author: tcl
 */
namespace App\Logic;
use App\Models\PmsOperateModel;
use Illuminate\Http\Request;
/**
 * Class OperateLogic
 * @package App\Logic
 */
class OperateLogic extends BaseLogic {
    /**
     * @var null
     */
    private $model               = null;

    /**
     * PerMissionLogic constructor.
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function __construct()
    {
        parent::__construct();
        $this->model               = new PmsOperateModel();
    }

    /**
     * 新增
     * @param $request
     * @return bool
     */
    public function create(Request $request)
    {
        $param = $request->input();
        $data = [
            'uri'       => $request->fullUrl(),
            'params'    => $param ? toJsonUnicode($param) : '',
            'admin_id'  => $this->adminId(),
            'admin_name'=> $this->adminName(),
            'method'    => $request->method(),
            'ip'        => $request->ip()
        ];

        if(false == $this->model->insertSingle($data))
        {
            return $this->setReturn('失败');
        }

        return $this->setReturn('成功', true);
    }

    /**
     * 列表
     * @param $input
     * @return bool
     * @throws \think\exception\DbException
     */
    public function list($input)
    {
        $pageSize = $input['page_size'];
        $where    = $this->getWhere($input);
        $data     = $this->model->getPageList('*', $where, ['id' => 'desc'], $pageSize);
        return $this->setReturn('成功', true,  $data);
    }

    /**
     * 构造 where
     * @param $input
     * @return array
     */
    private function getWhere($input)
    {
        $where    = [];

        if($input['admin_name'])
        {
            $where[] = ['admin_name', 'like', "%{$input['admin_name']}%"];
        }

        if($input['uri'])
        {
            $where[] = ['uri', 'like', "%{$input['uri']}%"];
        }

        if($input['ip'])
        {
            $where[] = ['ip', '=', $input['ip']];
        }

        if($input['star_at'])
        {
            $where[] = ['create_time' ,'>', $input['star_at']];
        }

        if($input['end_at'])
        {
            $where[] = ['create_time' ,'<', $input['end_at']];
        }

        return $where;
    }
}
