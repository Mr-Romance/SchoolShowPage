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
    protected $table = 'categories';

    public static function saveCategories($data) {
        if (empty($data)) {
            return '要保存数据为空';
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

    /**
     *  获取所有的一级分类
     *
     * @return Categories|array
     * @throws \think\exception\DbException
     */
    public static function getAllFirstCategories() {
        $categories = Categories::all(['parent_id' => 0]);

        if (!$categories) {
            return [];
        }

        return $categories;
    }


    /**
     *  获取一级分类下的所有二级分类
     *
     * @param $parent_id
     * @return Categories[]|array|false
     * @throws \think\exception\DbException
     */
    public static function getAllSecondCategories($parent_id) {
        $categories = Categories::all(['parent_id' => $parent_id]);

        if (!$categories) {
            return [];
        }

        return $categories;
    }

    /**
     * 获取所有的分类信息
     *
     * @return array
     * @throws \think\exception\DbException
     */
    public static function getCategoriesGroup(){
        // 此处有坑，费解
        $first_cats=self::getAllFirstCategories();
        $res_data=[];
        if(!empty($first_cats)){
            foreach($first_cats as $category){
                $second_arr=[];
                $second_cats=self::getAllSecondCategories($category->data['id']);
                foreach($second_cats as $s_category){
                    $second_arr[]=[
                        'name'=>$s_category->data['name'],
                        'id'=>$s_category->data['id']
                        ];
                }
                $res_data[]=[
                    'first_cat_name'=>$category->data['name'],
                    'second_cat_arr'=>$second_arr
                    ];
            }
        }

        return $res_data;
    }

}