<?php

namespace app\index\controller;

use app\index\model\Categories;
use app\index\model\Resources;
use app\index\model\Users;
use think\File;
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
            $this->success('请先登录', '/login');
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

        Session::set('menu_name', 'category_list');
        $this->assign('menu_name', Session::get('menu_name'));

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
        $validate = new Validate(['name' => 'require', 'sort' => 'require', 'parent_id' => 'require|number']);
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

        // 获取主题文件目录树
        $tree = $this->generateTree2();
        $this->assign('tree', json_encode($tree));

        Session::set('menu_name', 'show_add_resource');
        $this->assign('menu_name', Session::get('menu_name'));
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
        $params['res_cat_id'] = $request->param('res_cat_id');
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
        $validate = new Validate(['title' => 'require', 'type' => 'require|number', 'cat_first' => 'require|number']);

        if (!$validate->check($params)) {
            $errors = $validate->getError();
            return $this->errorResponse(200, $errors);
        }

        // 保存文件
        $thumbnail = request()->file('thumbnail');

        // 视频音频需要单独处理
        if (4 == $request->param('type') || 5 == $request->param('type')) {
            if (!isset($_FILES['src'])) {
                $scr = [];
            } else {
                $src = $_FILES['src'];
            }
        } else {
            $src = request()->file('src');
        }

        if (empty($thumbnail)) {
            return $this->errorResponse(200, '缩略图为空');
        }

        $thumbnail_res = $thumbnail->validate([
            'size' => 900000,
            'ext' => 'jpg,png,gif'
        ])->move(Config::get('src_thumbnail_move_path'));

        if ($thumbnail_res) {
            $thumbnail_save_name = $thumbnail_res->getSaveName();
            $thumbnail_path = Config::get('src_thumbnail_save_path') . $thumbnail_save_name;
        } else {
            return $this->errorResponse(200, '保存缩略图失败');
        }

        if (empty($src)) {
            $source_name = $request->param('source_name');
            if (empty($source_name)) {
                return $this->errorResponse(200, '请上传资源或者填写资源名称');
            }
            $full_source_name = explode('.', $source_name);
            if (count($full_source_name) < 2) {
                return $this->errorResponse(200, '请填写文件的后缀');
            }

            $src_path = Config::get('upd_src_save_path') . trim($source_name);

        } else {
            if (4 == $request->param('type') || 5 == $request->param('type')) {
                $file_full_path = Config::get('src_source_move_path') . date('Ymd') . DS;
                if (!file_exists($file_full_path)) {
                    mkdir($file_full_path);
                }
                move_uploaded_file($src['tmp_name'], $file_full_path . $src['name']);
                $src_path = Config::get('src_source_save_path') . date('Ymd') . DS . $src['name'];

            } else {
                $scr_res = $src->validate(['size' => 322122547,])->move(Config::get('src_source_move_path'));

                if ($scr_res) {
                    $scr_save_name = $scr_res->getSaveName();
                    $src_path = Config::get('src_source_save_path') . $scr_save_name;
                } else {
                    return $this->errorResponse(200, '上传资料失败');
                }
            }
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
        $data['category_2'] = empty($params['res_cat_id']) ? 0 : $params['res_cat_id'];
        $data['status'] = 1;
        $data['type'] = $params['type'];
        $data['subject'] = $params['subject'];

        try {
            Resources::saveResources($data);
        } catch (Exception $exception) {
            return $this->errorResponse(200, $exception->getMessage());
        }

        if (!empty($src)) {
            return $this->successResponse(100, '上传成功');
        } else {
            return $this->successResponse(100, '信息保存成功，记得上传文件，文件名为：' . $request->param('source_name'));
        }
    }

    /**
     *  展示用户已经发布资源的列表
     *
     * @param Request $request
     * @return mixed
     * @throws Exception
     * @throws \think\exception\DbException
     */
    public function showUserResourceList(Request $request) {
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

        $user_id = $request->param('id');
        if (empty($user_id)) {
            $user = $this->getLoginUser();
        } else {
            $user = Users::getUserById($user_id);
        }

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
        if (!empty($search_param['res_title'])) {
            $this->assign('search_title', $search_param['res_title']);
        }
        $this->assign('list', $list);

        Session::set('menu_name', 'user_resource_list');
        $this->assign('menu_name', Session::get('menu_name'));

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
    public function deleteResource(Request $request) {
        $id = $request->param('resource_id');
        if (empty($id) || $id <= 0) {
            return $this->errorResponse(200, '请求参数不合法');
        }

        $del_id = Resources::destroy($id);
        if (!is_int($del_id)) {
            return $this->errorResponse(200, '删除失败');
        }

        return $this->successResponse(100, '删除成功');
    }

    /**
     * @throws \think\exception\DbException
     */
    public function categoryList() {
        $category_list = Categories::getCategoriesGroup();
        $this->assign('category_list', $category_list);

        Session::set('menu_name', 'category_list');
        $this->assign('menu_name', Session::get('menu_name'));
        return $this->fetch();
    }

    /**
     * @param Request $request
     */
    public function deleteCategory(Request $request) {
        $category_id_str = $request->param('category_ids');
        if (empty($category_id_str)) {
            return $this->errorResponse('请选择具体的分类');
        }

        $category_arr = explode(',', $category_id_str);

        // 执行删除
        try {
            Categories::deleteCategory($category_arr);
        } catch (Exception $exception) {
            return $this->errorResponse(200, $exception->getMessage());
        }

        return $this->successResponse(100, '删除成功');

    }

    /**
     *  显示个人主页的文章详情
     *
     * @param Request $request
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function showUserResourceDetail(Request $request) {
        $res_id = $request->param('id');

        $resource = Resources::get($res_id);
        $this->assign('resource', $resource);

        Session::set('menu_name', 'manage_resource_list');
        $this->assign('menu_name', Session::get('menu_name'));
        Resources::addShowCount($res_id);

        return $this->fetch();
    }

    /**
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function manageResourceList() {
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

        $list = Resources::getResourcesList($search_param, 0);

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
        if (!empty($search_param['res_title'])) {
            $this->assign('search_title', $search_param['res_title']);
        }
        $this->assign('list', $list);

        Session::set('menu_name', 'manage_resource_list');
        $this->assign('menu_name', Session::get('menu_name'));

        return $this->fetch();
    }

    /**
     *  显示编辑资源页面
     *
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function showEditResource(Request $request) {
        $id = $request->param('res_id');
        $resource = Resources::get($id);
        $this->assign('resource', $resource);

        // 获取所有的一级分类
        $first_categories = Categories::getAllFirstCategories();
        $this->assign('first_categories', $first_categories);

        // 获取所有的主题配置
        $subject = Config::get('resource_subject');
        $this->assign('subject', $subject);

        $cat_two_model = Categories::get($resource->category);
        $cat_one_model = Categories::get($cat_two_model->parent_id);

        if (!empty($cat_one_model) && !empty($cat_two_model)) {
            $this->assign('cat_one', $cat_one_model->name);
            $this->assign('cat_two', $cat_two_model->name);
        }

        // 获取已经添加文件目录信息
        if(!empty($resource->category_2)){
            $cat2Model=Categories::get($resource->category_2);
            $this->assign('cat2_name',$cat2Model->name);
        }

        // 获取主题文件目录树
        $tree = $this->generateTree2();
        $this->assign('tree', json_encode($tree));

        Session::set('menu_name', 'user_resource_list');
        $this->assign('menu_name', Session::get('menu_name'));

        return $this->fetch();
    }

    /**
     *  编辑资源
     * @param Request $request
     * @return \think\response\Json
     * @throws \Exception
     */
    public function editResource(Request $request) {
        // 这里有问题，不能统一获取参数
        $params['id'] = $request->param('id');
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
        $validate = new Validate(['title' => 'require', 'type' => 'require|number']);

        if (!$validate->check($params)) {
            $errors = $validate->getError();
            return $this->errorResponse(200, $errors);
        }

        // 保存文件
        $thumbnail = request()->file('thumbnail');

        // 视频音频需要单独处理
        if (4 == $request->param('type') || 5 == $request->param('type')) {
            if (!isset($_FILES['src'])) {
                $scr = [];
            } else {
                $src = $_FILES['src'];
            }
        } else {
            $src = request()->file('src');
        }

        if (!empty($thumbnail)) {
            $thumbnail_res = $thumbnail->validate([
                'size' => 900000,
                'ext' => 'jpg,png,gif'
            ])->move(Config::get('src_thumbnail_move_path'));

            if ($thumbnail_res) {
                $thumbnail_save_name = $thumbnail_res->getSaveName();
                $thumbnail_path = Config::get('src_thumbnail_save_path') . $thumbnail_save_name;
            } else {
                return $this->errorResponse(200, '保存缩略图失败');
            }
        }

        $source_name = $request->param('source_name');
        if (!empty($src) || !empty($source_name)) {
            if (empty($src)) {
                $source_name = $request->param('source_name');
                if (empty($source_name)) {
                    return $this->errorResponse(200, '请上传资源或者填写资源名称');
                }
                $full_source_name = explode('.', $source_name);
                if (count($full_source_name) < 2) {
                    return $this->errorResponse(200, '请填写文件的后缀');
                }

                $src_path = Config::get('upd_src_save_path') . trim($source_name);

            } else {
                if (4 == $request->param('type') || 5 == $request->param('type')) {
                    $file_full_path = Config::get('src_source_move_path') . date('Ymd') . DS;
                    if (!file_exists($file_full_path)) {
                        mkdir($file_full_path);
                    }
                    move_uploaded_file($src['tmp_name'], $file_full_path . $src['name']);
                    $src_path = Config::get('src_source_save_path') . date('Ymd') . DS . $src['name'];
                } else {
                    $scr_res = $src->validate(['size' => 322122547,])->move(Config::get('src_source_move_path'));

                    if ($scr_res) {
                        $scr_save_name = $scr_res->getSaveName();
                        $src_path = Config::get('src_source_save_path') . $scr_save_name;
                    } else {
                        return $this->errorResponse(200, '上传资料失败');
                    }
                }
            }
        }

        // 入库
        $data = [];
        $data['id'] = $params['id'];
        $data['title'] = $params['title'];
        $data['introduction'] = empty($params['introduction']) ? '暂无描述' : $params['introduction'];
        $data['thumbnail'] = $thumbnail_path;
        $data['src'] = $src_path;
        $data['user_id'] = $user_id;

        if (isset($params['cat_first']) && !empty($params['cat_first'])) {
            if (isset($params['cat_second']) && !empty($params['cat_second'])) {
                $data['category'] = $params['cat_second'];
            } else {
                $data['category'] = $params['cat_first'];
            }
        }
        $data['category_2'] = empty($params['res_cat_id']) ? 0 : $params['res_cat_id'];
        $data['status'] = 1;
        $data['type'] = $params['type'];
        $data['subject'] = $params['subject'];

        $data = array_filter($data);

        try {
            Resources::updResource($data);
        } catch (Exception $exception) {
            return $this->errorResponse(200, $exception->getMessage());
        }

        return $this->successResponse(100, '编辑成功');
    }

    /**
     *  展示主题目录树页面
     *
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function showResourceCat() {
        Session::set('menu_name', 'resource_cat_list');
        $this->assign('menu_name', Session::get('menu_name'));

        // 获取所有已经添加的目录树
        $tree=$this->generateTree();

        // 返回目录树json
        $this->assign('tree',json_encode($tree));

        // 返回主题信息
        $subjects = Config::get('resource_subject');
        if (!empty($subjects)) {
            foreach ($subjects as $key => $subject) {
                if (in_array($subject['id'], [1, 2, 6])) {
                    unset($subjects[$key]);
                }
            }

            $this->assign('subjects',$subjects);
        }

        return $this->fetch();
    }

    /**
     *  添加主题目录
     *
     * @param Request $request
     * @return \think\response\Json
     */
    public function addResourceCat(Request $request) {
        $params = $request->param();
        if (empty($params)) {
            return $this->errorResponse(200, '保存数据为空');
        }

        if (empty($params['name'])) {
            return $this->errorResponse(200, '请输入名称信息');
        }

        if(!empty($params['cat_ids'])){
            if(strlen($params['cat_ids'])>2){
                return $this->errorResponse(200,'添加目录只能选择一个父级');
            }
            $params['parent_id']=$params['cat_ids'];
        }

        unset($params['cat_ids']);

        $catModel = new Categories();
        $params['type']=2;
        if (!$catModel->save($params)) {
            return $this->errorResponse(200, '保存数据失败');
        }

        return $this->successResponse(100, '添加成功');
    }

    /**
     *  返回目录树指定的数据结构(第二个版本--添加、编辑资源)
     *
     * @return array
     * @throws \think\exception\DbException
     */
    private function generateTree2() {
        $tree_arr=[];
        $tree_datas=Categories::all(['type'=>2]);
        if(!empty($tree_datas)){
            $tree_arr=[];
            foreach ($tree_datas as $tree){
                $tree_arr[]=$tree->toArray();
            }
        }

        $items = [];
        // 先构造数据结构
        foreach ($tree_arr as $value) {
            $items[$value['id']] = $value;
            $items[$value['id']]['label'] = $value['name'];
            $items[$value['id']]['children'] = [];
        }


        // 遍历，添加节点数据
        $tree = array();
        foreach ($items as $key => $value) {
            if (!empty($items[$value['parent_id']])) {
                $items[$value['parent_id']]['children'][] = &$items[$key];
            } else {
                $tree[] = &$items[$key];
            }
        }
        return $tree;
    }

    /**
     *  返回目录树指定的数据结构(第一个版本--添加资源分类目录哪里)
     *
     * @return array
     * @throws \think\exception\DbException
     */
    private function generateTree() {
        $tree_arr=[];
        $tree_datas=Categories::all(['type'=>2]);
        if(!empty($tree_datas)){
            $tree_arr=[];
            foreach ($tree_datas as $tree){
                $tree_arr[]=$tree->toArray();
            }
        }

        $items = [];
        // 先构造数据结构
        foreach ($tree_arr as $value) {
            $items[$value['id']] = $value;
            $items[$value['id']]['text'] = $value['name'];
            $items[$value['id']]['nodes'] = [];
        }


        // 遍历，添加节点数据
        $tree = array();
        foreach ($items as $key => $value) {
            if (!empty($items[$value['parent_id']])) {
                $items[$value['parent_id']]['nodes'][] = &$items[$key];
            } else {
                $tree[] = &$items[$key];
            }
        }
        return $tree;
    }

    /**
     *  删除目录节点

     * @param Request $request
     * @return \think\response\Json
     * @throws \think\exception\DbException
     */
    public function delResourceCat(Request $request){
        $cat_str=$request->param('cat_str');

        if(empty($cat_str)){
            return $this->errorResponse(200,'请选择要删除的目录');
        }

        $cats_arr=explode(',',$cat_str);

        // 降序排序
        rsort($cats_arr);

        // 从最后一个节点开始删除，如果没有子节点就删除
        foreach($cats_arr as $cat_id){
            $catModel=Categories::get(['parent_id'=>$cat_id]);
            if(empty($catModel)){
                $delModel=Categories::get($cat_id);
                $delModel->delete();
            }
        }

        return $this->successResponse(100,'删除成功');
    }
}