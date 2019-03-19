<?php

/**
 *  不需要登录的操作
 */
namespace app\index\controller;

use app\index\model\Users;
use think\Controller;
use think\Exception;
use think\Request;
use think\Session;
use think\Validate;

class Common extends Controller
{
    /**
     *  显示用户登录页面
     *
     * @return mixed
     */
    public function showLogin() {
        return $this->fetch();
    }

    /**
     *  用户登录验证
     *
     * @param Request $request
     * @return \think\response\Json
     */
    public function login(Request $request) {
        $post_data = $request->param();
        if (empty($post_data) || !is_array($post_data)) {
            return $this->errorResponse(200, '非法的请求数据');
        }

        // 这里使用独立验证器
        $validate = new Validate([
            'name' => 'require|min:3|max:20',
            'password' => 'require|min:6|max:20',
        ]);
        if (!$validate->check($post_data)) {
            $errors = $validate->getError();
            return $this->errorResponse(200, $errors);
        }

        try {
            $user = Users::checkLogin($post_data);
            // session中保存登录的用户
            Session::set('login_user_id',$user->id);

            return $this->successResponse(100, '登录成功');
        } catch (Exception $exception) {
            return $this->errorResponse(200, $exception->getMessage());
        }
    }

    /**
     *  用户推出
     */
    public function logout(){
        Session::delete('login_user_id');
        $this->redirect('/login');
    }

    /**
     *  显示用户注册页面
     *
     * @return mixed
     */
    public function showRegister() {
        return $this->fetch();
    }

    /**
     *  用户注册方法
     *
     * @param Request $request
     * @return \think\response\Json
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function register(Request $request) {
        $post_data = $request->param();
        if (empty($post_data) || !is_array($post_data)) {
            return $this->errorResponse(200, '非法的请求数据');
        }

        // 这里使用独立验证器
        $validate = new Validate([
            'name' => 'require|min:3|max:20',
            'email' => 'require|email',
            'password' => 'require|min:6|max:20',
            're_password' => 'require|confirm:password'
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

        return $this->successResponse(100, '注册成功');

    }

}