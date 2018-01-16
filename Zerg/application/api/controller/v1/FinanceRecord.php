<?php

namespace app\api\controller\v1;

use app\api\controller\BaseController;
use app\api\model\FinanceRecord as FinanceRecordModel;
use app\api\service\Token;
use app\api\validate\PagingParameter;
use think\Controller;

class FinanceRecord extends BaseController
{
    /**
     * 财务流水（分页）
     * @param int $page
     * @param int $size
     * @return array
     * @throws \app\lib\exception\ParameterException
     */
    public function getSummary($page = 1, $size = 20)
    {
        $uid = Token::getCurrentUid();
        (new PagingParameter())->goCheck();
//        $uid = Token::getCurrentUid();
        $pagingOrders = FinanceRecordModel::getSummaryByPage($uid, $page, $size);
        if ($pagingOrders->isEmpty()) {
            return [
                'current_page' => $pagingOrders->currentPage(),
                'data' => []
            ];
        }
        $data = $pagingOrders->toArray();
        return [
            'current_page' => $pagingOrders->currentPage(),
            'data' => $data
        ];
    }
}






















