<?php

namespace app\index\model;


use Exception;
use think\Model;

class Resources extends Model
{
    protected $table = 'resources';

    /**
     * 保存资源信息
     *
     * @param $data
     * @throws Exception
     */
    public static function saveResources($data) {
        $resource=new Resources($data);
        $save_res=$resource->save();

        if(!$save_res){
            throw new Exception('保存资源信息失败');
        }
    }
}