<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/3/15
 * Time: 14:55
 */

namespace app\index\model;


use think\Model;

class Categories extends Model
{
    protected $table ='categories';

    public static function saveCategories($data){
        if (empty($data)) {
            return '要保存数据为空';
        }

        // 增加二级分类
        if(2==$data['type']){
            if(empty($data['parent_id'])){
                return '一级分类id为空';
            }
        }

        $category = new Categories();
        // 检查是否有重复
        $name_exists = $category->where('name', $data['name'])->find();
        if ($name_exists) {
            return '该分类名已经存在';
        }

        unset($data['type']);
        if (empty($data['sort']) || !isset($data['sort'])) {
            $data['sort'] = 1;
        }

        // 执行保存
        $save_res = $category->save($data);
        if (!$save_res) {
            return '添加分类失败';
        }
    }
}