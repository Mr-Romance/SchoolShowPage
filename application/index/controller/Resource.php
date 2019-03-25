<?php

namespace app\index\controller;

use app\index\model\Categories;
use app\index\model\Resources;
use think\Session;
use think\Config;
use think\Controller;
use think\Exception;
use think\Request;
use think\Validate;

class Resource extends Controller
{
    protected function _initialize() {
        if (!Session::get('login_user_id')) {
            $this->success('请先登录', 'Common/showLogin');
        }
        $user = $this->getLoginUser();
        $this->assign('user', $user);
    }

    /**
     *  显示添加分类页面
     *
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function showAddCategory() {
        // 获取所有的一级分类信息
        $first_categories = Categories::getAllFirstCategories();
        $this->assign('first_categories', $first_categories);

        return $this->fetch();
    }

    /**
     *  创建分类
     *
     * @param Request $request
     * @return \think\response\Json
     */
    public function addCategory(Request $request) {
        $post_data = $request->param();
        if (empty($post_data) || !is_array($post_data)) {
            return $this->errorResponse(200, '参数格式不合法');
        }

        // 参数校验
        $validate = new Validate([
            'name' => 'require',
            'sort' => 'require',
            'parent_id' => 'require|number'
        ]);
        if (!$validate->check($post_data)) {
            $errors = $validate->getError();
            return $this->errorResponse(200, $errors);
        }

        // 保存分类
        $add_res = Categories::saveCategories($post_data);
        if ($add_res) {
            return $this->errorResponse(200, $add_res);
        } else {
            return $this->successResponse(100, '添加分类成功');
        }
    }

    /**
     *  显示添加资源页面
     *
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function showAddResource() {
        // 获取所有的一级分类
        $first_categories = Categories::getAllFirstCategories();
        $this->assign('first_categories', $first_categories);

        // 获取所有的主题配置
        $subject = Config::get('resource_subject');
        $this->assign('subject', $subject);

        return $this->fetch();
    }

    /**
     *  添加资源
     *
     * @param Request $request
     * @return \think\response\Json
     * @throws \Exception
     */
    public function addResource(Request $request) {
        // 这里有问题，不能统一获取参数
        $params['title'] = $request->param('title');
        $params['type'] = $request->param('type');
        $params['cat_first'] = $request->param('cat_first');
        $params['cat_second'] = $request->param('cat_second');
        $params['introduction'] = $request->param('introduction');
        $params['subject'] = $request->param('subject');

        $thumbnail_path = '';
        $src_path = '';
        $user = $this->getLoginUser();
        $user_id = $user->id;

        if (empty($params)) {
            return $this->errorResponse(200, '参数格式不合法');
        }

        // 参数校验
        $validate = new Validate([
            'title' => 'require',
            'type' => 'require|number',
            'cat_first' => 'require|number'
        ]);

        if (!$validate->check($params)) {
            $errors = $validate->getError();
            return $this->errorResponse(200, $errors);
        }

        // 保存文件
        $thumbnail = request()->file('thumbnail');
        $src = request()->file('src');

        if (empty($thumbnail)) {
            return $this->errorResponse(200, '缩略图为空');
        }
        if (empty($src)) {
            return $this->errorResponse(200, '资源为空');
        }

        $thumbnail_res = $thumbnail->validate([
            'size' => 900000,
            'ext' => 'jpg,png,gif'
        ])->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'thumbnail');

        $scr_res = $src->validate([
            'size' => 322122547,
        ])->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'src');

        if ($thumbnail_res) {
            $thumbnail_save_name = $thumbnail_res->getSaveName();
            $thumbnail_path = DS . 'uploads' . DS . 'thumbnail' . DS . $thumbnail_save_name;
        } else {
            return $this->errorResponse(200, '保存缩略图失败');
        }

        if ($scr_res) {
            $scr_save_name = $scr_res->getSaveName();
            $src_path = DS . 'uploads' . DS . 'src' . DS . $scr_save_name;
        } else {
            return $this->errorResponse(200, '上传资料失败');
        }

        // 入库
        $data = [];
        $data['title'] = $params['title'];
        $data['introduction'] = empty($params['introduction']) ? '暂无描述' : $params['introduction'];
        $data['thumbnail'] = $thumbnail_path;
        $data['src'] = $src_path;
        $data['user_id'] = $user_id;
        if (empty($params['cat_second'])) {
            $data['category'] = $params['cat_first'];
        } else {
            $data['category'] = $params['cat_second'];
        }
        $data['status'] = 1;
        $data['type'] = $params['type'];
        $data['subject'] = $params['subject'];

        try {
            Resources::saveResources($data);
        } catch (Exception $exception) {
            return $this->errorResponse(200, $exception->getMessage());
        }

        return $this->successResponse(100, '上传成功');
    }

    /**
     *  展示用户已经发布资源的列表
     *
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function showUserResourceList() {
        $search_param = [];

        if (Session::has('param.search_category')) {
            $search_param['res_category'] = explode(',', Session::get('param.search_category'));
        }
        if (Session::has('param.search_type')) {
            $search_param['res_type'] = explode(',', Session::get('param.search_type'));
        }
        if (Session::has('search_title')) {
            $search_param['res_title'] = Session::get('search_title');
        }

        $user = $this->getLoginUser();

        $list = Resources::getResourcesList($search_param, $user->id);

        // 获取所有的一级分类
        $cat_groups = Categories::getCategoriesGroup();
        if ($cat_groups) {
            $this->assign('cat_groups', $cat_groups);
        }

        // 资源类型信息
        $res_type = Config::get('resource_type');

        // 渲染赋值
        if (!empty($res_type)) {
            $this->assign('res_type', $res_type);
        }
        if (!empty($search_param['res_type'])) {
            $this->assign('type_checked', $search_param['res_type']);
        }
        if (!empty($search_param['res_category'])) {
            $this->assign('category_checked', $search_param['res_category']);
        }
        $this->assign('list', $list);

        return $this->fetch('show_user_resource_list');
    }


    /**
     *  获取一级分类下的所有二级分类
     *
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function getSecondCategories(Request $request) {
        $params = $request->param();
        if (empty($params)) {
            return $this->errorResponse(200, '父级分类参数不合法');
        }

        $res_data = [];
        $second_categories = Categories::getAllSecondCategories($params['parent_id']);
        if (!empty($second_categories)) {
            foreach ($second_categories as $category) {
                /**
                 * @var Categories $category
                 */
                $res_data[] = [$category->id, $category->name];
            }
        }

        return $this->successResponse(100, '获取分类成功', $res_data);
    }

    /**
     *  根据搜索条件，显示用户资源信息
     *
     * @param Request $request
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function searchUserResourceList(Request $request) {
        $param = $request->param();

        $search_param = [];

        if (!empty($param['search_cat_ids'])) {
            // 把字符串打散为数组
            $search_param['res_category'] = explode(',', $param['search_cat_ids']);
            // 这里存入的，其实是一个字符串，tp——session不支持3维数组
            Session::set('param.search_category', $param['search_cat_ids']);
        } else {
            Session::delete('param.search_category');
        }

        if (!empty($param['res_type'])) {
            $search_param['res_type'] = explode(',', $param['search_type_ids']);
            Session::set('param.search_type', $param['search_type_ids']);
        } else {
            Session::delete('param.search_type');
        }
        if (!empty($param['search_title'])) {
            $search_param['res_title'] = $param['search_title'];
            Session::set('search_title', $param['search_title']);
        } else {
            Session::delete('search_title');
        }
    }

    /**
     *  清除查询缓存
     */
    public function deleteSearchSession() {
        Session::delete('param.search_category');
        Session::delete('param.search_type');
    }

    /**
     * @param Request $request
     * @return \think\response\Json
     */
    public function deleteResource(Request $request){
        $id=$request->param('resource_id');
        if(empty($id) || $id<=0){
            return $this->errorResponse('请求参数不合法');
        }

    }
}