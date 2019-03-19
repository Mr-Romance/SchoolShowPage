<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2019/3/15
 * Time: 14:52
 */

namespace app\index\controller;


use app\index\model\Categories;
use think\Controller;
use think\Request;
use think\Session;
use think\Validate;

class Resource extends Controller
{
    protected function _initialize() {
        if (!Session::get('login_user_id')) {
            $this->success('请先登录', 'Common/showLogin');
        }
        $user=$this->getLoginUser();
        $this->assign('user',$user);
    }

    /**
     *  显示添加分类页面
     *
     * @return mixed
     */
    public function showAddCategory(){
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
            'type' => 'require|between:1,2',
            'name' => 'require',
            'sort' => 'require'
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
     */
    public function showAddResource(){
        return $this->fetch();
    }

    public function addResource(Request $request){

    }

    /**
     *  展示用户已经发布资源的列表
     *
     * @return mixed
     */
    public function showUserResourceList(){
        return $this->fetch();
    }

}