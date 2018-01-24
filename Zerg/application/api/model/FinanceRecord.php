<?php

namespace app\api\model;

use think\Model;

class FinanceRecord extends BaseModel
{
    protected $table = 'osc_finance_record';

    protected $hidden = [
        'uid','bank', 'editor', 'del', 'itemid'
    ];
    /**
     * 根据用户id分页获取用户财务流水
     */
    public static function getAccountRecordByPage($uid, $page = 1, $size = 20)
    {
        $pagingData = self::where(['uid' => $uid])->order('itemid desc')
            ->paginate($size, false, ['page' => $page]);

        if ($pagingData) {
            foreach ($pagingData as $key => $v) {
                $pagingData[$key]['addtime'] = date('Y-m-d H:i:s',$v['addtime']);
            }
        }

        return $pagingData;
    }
}
