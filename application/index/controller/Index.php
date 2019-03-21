<?php

namespace app\index\controller;

use app\index\model\Users;
use think\Controller;
use think\Request;
use think\Session;

class Index extends Controller
{
    /**
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    protected function _initialize() {
        $user_id = Session::get('login_user_id');
        if (!empty($user_id)) {
            $user = Users::getUserById($user_id);
            $this->assign('user', $user);
        }
    }

    /**
     *  门户首页
     *
     * @return mixed
     */
    public function index() {
        return $this->fetch();
    }

    /**
     *  资源展示首页
     *
     * * @param Request $request
     * @return mixed
     */
    public function resourceIndex(Request $request) {

        return $this->fetch();
    }

    /**
     *  带分类的资源首页搜索页面
     *
     * @return mixed
     * @param Request $request
     */
    public function resourceCategory(Request $request) {
        return $this->fetch();
    }
}
