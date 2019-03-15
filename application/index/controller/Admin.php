<?php
/**
 *  用户相关的操作
 */

namespace app\index\controller;


use app\index\model\Users;
use think\Controller;
use think\Request;
use think\Validate;

class Admin extends Controller
{
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

}