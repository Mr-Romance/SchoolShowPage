<?php
/**
 *  用户相关的操作
 */

namespace app\index\controller;


use app\index\model\Resources;
use app\index\model\Users;
use think\Config;
use think\Controller;
use think\Exception;
use think\Request;
use think\Session;
use think\Validate;

class Admin extends Controller
{
    protected function _initialize()
    {
        if (!Session::get('login_user_id')) {
            $this->success('请先登录', '/login');
        }
        $user = $this->getLoginUser();
        $this->assign('user', $user);
    }

    /**
     *  展示增加用户的界面
     *
     * @return mixed
     */
    public function showAddUser()
    {
        Session::set('menu_name','user_list');
        $this->assign('menu_name',Session::get('menu_name'));

        return $this->fetch();
    }

    /**
     *  添加用户
     *
     * @param Request $request
     * @return \think\response\Json
     */
    public function addUser(Request $request)
    {
        // 这里有问题，不能统一获取参数
        $params['name'] = $request->param('name');
        $params['sex'] = $request->param('sex');
        $params['email'] = $request->param('email');
        $params['password'] = $request->param('password');
        $params['introduction'] = $request->param('introduction');

        if (empty($params)) {
            return $this->errorResponse(200, '参数为空');
        }

        // 参数校验
        $validate = new Validate(['name' => 'require', 'sex' => 'require|number', 'email' => 'require|email', 'password' => 'require']);

        if (!$validate->check($params)) {
            $errors = $validate->getError();
            return $this->errorResponse(200, $errors);
        }

        // 保存文件
        $head_portrait = request()->file('head_portrait');

        if (empty($head_portrait)) {
            return $this->errorResponse(200, '头像为空');
        }

        $thumbnail_res = $head_portrait->validate(['size' => 900000, 'ext' => 'jpg,png,gif'])->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'head_portrait');

        if ($thumbnail_res) {
            $thumbnail_save_name = $thumbnail_res->getSaveName();
            $thumbnail_path = DS . 'uploads' . DS . 'head_portrait' . DS . $thumbnail_save_name;
        } else {
            return $this->errorResponse(200, '保存缩略图失败');
        }

        // 入库
        $data = [];
        $data['name'] = $params['name'];
        $data['email'] = $params['email'];
        $data['introduction'] = empty($params['introduction']) ? '暂无描述' : $params['introduction'];
        $data['head_portrait'] = $thumbnail_path;
        $data['sex'] = $params['sex'];
        $data['password'] = think_encrypt($params['password']);
        $data['status'] = 1;

        try {
            Users::saveUser($data);
        } catch (Exception $exception) {
            return $this->errorResponse(200, $exception->getMessage());
        }

        return $this->successResponse(100, '添加成功');
    }

    /**
     *  更新用户
     *
     * @param Request $request
     * @return \think\response\Json
     */
    public function updUser(Request $request){
        // 这里有问题，不能统一获取参数
        $params['name'] = $request->param('name');
        $params['sex'] = $request->param('sex');
        $params['email'] = $request->param('email');
        $params['password'] = $request->param('password');
        $params['introduction'] = $request->param('introduction');
        $params['user_id'] = $request->param('user_id');

        if (empty($params)) {
            return $this->errorResponse(200, '参数为空');
        }

        // 参数校验
        $validate = new Validate(['user_id'=>'require','name' => 'require', 'sex' => 'require|number', 'email' => 'require|email', 'password' => 'require']);

        if (!$validate->check($params)) {
            $errors = $validate->getError();
            return $this->errorResponse(200, $errors);
        }

        // 保存文件
        $head_portrait = request()->file('head_portrait');
        $thumbnail_path = '';
        if (!empty($head_portrait)) {
            $thumbnail_res = $head_portrait->validate(['size' => 900000, 'ext' => 'jpg,png,gif'])->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'head_portrait');

            if ($thumbnail_res) {
                $thumbnail_save_name = $thumbnail_res->getSaveName();
                $thumbnail_path = DS . 'uploads' . DS . 'head_portrait' . DS . $thumbnail_save_name;
            } else {
                return $this->errorResponse(200, '保存缩略图失败');
            }
        }

        // 入库
        $data = [];
        $data['name'] = $params['name'];
        $data['email'] = $params['email'];
        $data['introduction'] = empty($params['introduction']) ? '暂无描述' : $params['introduction'];
        if(!empty($thumbnail_path)){ // 更新头像
            $data['head_portrait'] = $thumbnail_path;
        }
        $data['sex'] = $params['sex'];
        $data['password'] = think_encrypt($params['password']);
        $data['status'] = 1;

        try {
            Users::saveUser($data,$params['user_id']);
        } catch (Exception $exception) {
            return $this->errorResponse(200, $exception->getMessage());
        }

        return $this->successResponse(100, '修改成功');
    }

    /**
     * 显示用户列表
     *
     * @param Request $request
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function userListSearch(Request $request)
    {
        $param = $request->param();
        $ids_arr = empty($param['ids_arr']) ? '' : $param['ids_arr'];
        $search_name = empty($param['search_name']) ? '' : $param['search_name'];
        if (!empty($ids_arr)) {
            $ids_arr = explode(',', $ids_arr);
            Session::set('search_user_ids', $ids_arr);
        } else {
            Session::delete('search_user_ids');
        }
        if (!empty($search_name)) {
            Session::set('search_user_name', $search_name);
        } else {
            Session::delete('search_user_name');
        }
    }

    public function userList()
    {
        $ids_arr = Session::get('search_user_ids');
        $search_name = Session::get('search_user_name');

        if (!empty($ids_arr)) {
            $ids_arr = explode(',', $ids_arr);
            Session::set('search_user_ids', $ids_arr);
        }
        if (!empty($search_name)) {
            Session::set('search_user_name', $search_name);
        }

        $list = Users::userList(['ids_arr' => $ids_arr, 'search_name' => $search_name]);
        $this->assign('list', $list);

        $this->assign('search_user_ids', $ids_arr);
        $this->assign('search_user_name', $search_name);

        Session::set('menu_name','user_list');
        $this->assign('menu_name',Session::get('menu_name'));

        return $this->fetch('user_list');
    }

    /**
     *  显示用户信息页面
     *
     * @return mixed
     */
    public function userPage()
    {
        Session::set('menu_name','user_page');
        $this->assign('menu_name',Session::get('menu_name'));
        return $this->fetch();
    }

    /**
     *  更新用户信息
     *
     * @param Request $request
     * @return \think\response\Json
     */
    public function editUser(Request $request)
    {
        $params = $request->param();

        try {
            $user_id = $params['id'];
            $params['password'] = think_encrypt($params['password']);
            unset($params['id']);
            Users::updUser($params, $user_id);
        } catch (Exception $exception) {
            return $this->errorResponse(200, $exception->getMessage());
        }

        return $this->successResponse(100, '更新成功');
    }

    /**
     *  显示编辑用户页面
     * @param Request $request
     * @return \think\response\Json
     */
    public function showEditUser(Request $request) {
        $params = $request->param();

        try {
            $user = Users::getUserById($params['user_id']);
        } catch (Exception $exception) {
            return $this->errorResponse(200, $exception->getMessage());
        }

        $this->assign('user', $user);

        Session::set('menu_name','user_list');
        $this->assign('menu_name',Session::get('menu_name'));
        return $this->fetch();
    }

    /**
     *  更新用户头像
     *
     * @param Request $request
     * @return \think\response\Json
     */
    public function updHeadPortrait(Request $request)
    {
        $user_id = $request->param('user_id');
        if (empty($user_id)) {
            return $this->errorResponse('用户ID为空');
        }

        $file = request()->file('head_portrait');

        // 图片验证
        if (empty($file)) {
            return $this->errorResponse(200, '图片为空');
        }

        $info = $file->validate(['size' => 900000, 'ext' => 'jpg,png,gif'])->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'head_portrait');

        if ($info) {
            $save_name = $info->getSaveName();
            $full_path = DS . 'uploads' . DS . 'head_portrait' . DS . $save_name;
            try {
                Users::updUser(['head_portrait' => $full_path], $user_id);
            } catch (Exception $exception) {
                return $this->errorResponse(200, $exception->getMessage());
            }
        } else {
            // 上传失败获取错误信息
            return $this->errorResponse(200, $file->getError());
        }

        return $this->successResponse(100, '更新头像成功');
    }

    /**
     *  删除用户
     *
     * @param Request $request
     * @return \think\response\Json
     */
    public function deleteUser(Request $request)
    {
        $user_id = $request->param('user_id');
        if (empty($user_id) || (int)$user_id < 0) {
            return $this->errorResponse(200, '用户id不合法');
        }

        try {
            Users::delUserById($user_id);
        } catch (Exception $exception) {
            return $this->errorResponse(200, $exception->getMessage());
        }

        return $this->successResponse(100, '删除成功');
    }

    /**
     *  展示系统配置页面
     *
     * @return mixed
     */
    public function showSysConfig(){
        Session::set('menu_name','show_sys_config');
        $this->assign('menu_name',Session::get('menu_name'));
        return $this->fetch();
    }

    /**
     *  上传首页静态图片
     *
     * @return \think\response\Json
     */
    public function sysStaticConfig(){
        $upd_file = $_FILES;
        $top_lb_arr = ['top1', 'top2', 'top3','search_index_bg'];
        $btm_lb_arr = ['btm1', 'btm2', 'btm3', 'btm4', 'btm5', 'btm6', 'btm7', 'btm8'];

        // 上传顶部的轮播图
        foreach ($top_lb_arr as $lb) {
            if (!empty($upd_file[$lb])) {
                $upd_file[$lb]['name'] = $lb;
                $move_res = move_uploaded_file($upd_file[$lb]['tmp_name'], Config::get('index_static_imgs') . $upd_file[$lb]['name'].'.jpg');
            }
        }

        foreach($btm_lb_arr as $lb){
            if(!empty($upd_file[$lb])){
                $upd_file[$lb]['name'] = $lb;
                $move_res = move_uploaded_file($upd_file[$lb]['tmp_name'], Config::get('index_static_imgs') . $upd_file[$lb]['name'].'.png');
            }
        }

        if(empty($move_res)){
            return $this->errorResponse(200,'上传图片失败');
        }

        return $this->successResponse(100,'上传成功');
    }
}