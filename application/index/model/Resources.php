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
     * @param $data
     * @throws \think\exception\DbException
     */
    public static function updResource($data){
        $model=Resources::get($data['id']);

        if(empty($model)){
            throw new Exception('未找到要更新的资源');
        }

        if(!$model->save($data,['id'=>$data['id']])){
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
            $base_query = $base_query->where('title', 'like', '%'.$search_data['res_title'].'%');
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
        if (!empty($search_data['order_type'])) {
            if (2 == $search_data['order_type']) {
                $base_query = $base_query->order('id', 'asc');
            }
        } else {
            $base_query = $base_query->order('id', 'desc');
        }


        // 分页
        $list = $base_query->paginate(8);

        return $list;
    }

    /**
     *  首页根据主题进行搜索的数据
     *
     * @param $subject_id
     * @param $limit_end
     * @param $limit_start
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getDataBySubject($subject_id,$limit_end,$limit_start=0) {
        $resource = new Resources();
        $data = $resource->where('subject', $subject_id)->limit($limit_start,$limit_end)->order('id', 'desc')->select();

        return $data;
//        $ret_data = [];
//        // 把集合转换成数组
//        foreach ($data as $item) {
//            $tem = [];
//            $tem['id'] = $item->id;
//            $tem['title'] = $item->title;
//            $ret_data[] = $tem;
//        }
//
//        return $ret_data;
    }

    /**
     * @param $limit
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getTopResources($limit) {
        $resource = new Resources();
        $data = $resource->limit($limit)->order('id', 'desc')->select();

        return $data;

//        $ret_data = [];
//        // 把集合转换成数组
//        foreach ($data as $item) {
//            $tem = [];
//            $tem['id'] = $item->id;
//            $tem['subject'] = $item->subject;
//            $tem['title'] = $item->title;
//            $tem['thumb'] = $item->thumbnail;
//            $ret_data[] = $tem;
//        }
//
//        return $ret_data;
    }

    public static function addShowCount($id)
    {
        $model = Resources::get($id);
        if (!empty($model)) {
            $old_show_times = $model->show_times;
            $model->show_times = $old_show_times + 1;
            $model->save();
        }
    }


}