<?php

namespace app\api\model;

use think\Model;

class FinanceRecord extends BaseModel
{
    protected $table = 'osc_finance_record';
    protected $autoWriteTimestamp = true;

    /**
     * 根据用户id获取财务流水列表
     */
    public static function getSummaryByPage($uid, $page = 1, $size = 20)
    {
        $pagingData = self::where(['uid' => $uid])->order('itemid desc')
            ->paginate($size, true, ['page' => $page]);
        return $pagingData;
    }
}
