<?php
/**
 *  用户相关的操作
 */

namespace app\index\controller;


use app\index\model\Users;
use think\Controller;
use think\Exception;
use think\Request;
use think\Session;
use think\Validate;

class Admin extends Controller
{
    protected function _initialize() {
        if (!Session::get('login_user_id')) {
            $this->success('请先登录', 'Common/showLogin');
        }
        $user = $this->getLoginUser();
        $this->assign('user', $user);
    }

    /**
     *  展示增加用户的界面
     *
     * @return mixed
     */
    public function showAddUser() {
        return $this->fetch();
    }

    /**
     *  添加用户
     *
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function addUser(Request $request) {
        $post_data = $request->param();
        if (empty($post_data) || !is_array($post_data)) {
            return $this->errorResponse(200, '非法的请求数据');
        }

        // 参数校验
        $validate = new Validate([
            'name' => 'require|min:3|max:20',
            'email' => 'require|email',
            'password' => 'require|min:6|max:20',
            'sex' => 'number|between:1,2'
        ]);

        if (!$validate->check($post_data)) {
            $errors = $validate->getError();
            return $this->errorResponse(200, $errors);
        }

        unset($post_data['re_password']);
        $post_data['password'] = think_encrypt($post_data['password']);
        $save_res = Users::saveUser($post_data);
        if ($save_res) {
            return $this->errorResponse(200, $save_res);
        }

        $post_data['password'] = think_encrypt($post_data['password']);
        $save_res = Users::saveUser($post_data);
        if ($save_res) {
            return $this->errorResponse(200, $save_res);
        }

        return $this->successResponse(100, '添加用户成功');
    }

    /**
     * 显示用户列表
     */
    public function usersList() {

    }

    /**
     *  显示用户信息页面
     *
     * @return mixed
     */
    public function userPage() {
        return $this->fetch();
    }

    /**
     *  更新用户信息
     *
     * @param Request $request
     * @return \think\response\Json
     */
    public function editUser(Request $request) {
        $params = $request->param();

        try {
            $user_id = $params['id'];
            $params['password']= think_encrypt($params['password']);
            unset($params['id']);
            Users::updUser($params, $user_id);
        } catch (Exception $exception) {
            return $this->errorResponse(200, $exception->getMessage());
        }

        return $this->successResponse(100, '更新成功');
    }

    /**
     *  更新用户头像
     *
     * @param Request $request
     * @return \think\response\Json
     */
    public function updHeadPortrait(Request $request) {
        $user_id = $request->param('user_id');
        if (empty($user_id)) {
            return $this->errorResponse('用户ID为空');
        }

        $file = request()->file('head_portrait');

        // 图片验证
        if (empty($file)) {
            return $this->errorResponse(200, '图片为空');
        }

        $info = $file->validate([
            'size' => 900000,
            'ext' => 'jpg,png,gif'
        ])->move(ROOT_PATH . 'public' . DS . 'uploads' . DS . 'head_portrait');

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
}