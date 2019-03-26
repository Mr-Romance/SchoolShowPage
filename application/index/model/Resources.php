<?php

namespace app\index\model;


use Exception;
use think\Db;
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
        $resource = new Resources($data);
        $save_res = $resource->save();

        if (!$save_res) {
            throw new Exception('保存资源信息失败');
        }
    }

    /**
     *  获取资源列表：所有|按条件
     *
     * @param array $search_data
     * @param int $user_id
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public static function getResourcesList($search_data = [], $user_id = 0) {
        $base_query = Db::table('resources');
        if (!empty($search_data['res_title']) && isset($search_data['res_title'])) {
            $base_query = $base_query->where('title', 'like', $search_data['res_title']);
        }
        if (!empty($search_data['res_category']) && isset($search_data['res_category'])) {
            $base_query = $base_query->where('category', 'in', $search_data['res_category']);
        }
        if (!empty($search_data['res_type']) && isset($search_data['res_type'])) {
            $base_query = $base_query->where('type', 'in', $search_data['res_type']);
        }
        if (!empty($user_id)) {
            $base_query = $base_query->where('user_id', $user_id);
        }

        // 分页
        $list = $base_query->paginate(8);

        return $list;
    }
}