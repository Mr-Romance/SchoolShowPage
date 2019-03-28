<?php

namespace app\index\model;

use think\db\exception\DataNotFoundException;
use think\db\exception\ModelNotFoundException;
use think\Exception;
use think\exception\DbException;
use think\Model;

class Users extends Model
{
    protected $table = 'users';

    /**
     *  保存用户的方法
     *
     * @param $data
     * @return string
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Exception
     * @throws ModelNotFoundException
     */
    public static function saveUser($data)
    {
        if (empty($data)) {
            throw new Exception('要保存数据为空');
        }
        $user = new Users();
        // 检查是否有重复
        $name_exists = $user->where('name', $data['name'])->find();
        if ($name_exists) {
            throw new Exception('该用户名已经存在');
        }

        $email_exists = $user->where('email', $data['email'])->find();
        if ($email_exists) {
            throw new Exception('该邮箱已经存在');
        }

        // 执行保存
        $save_res = $user->save($data);
        if (!$save_res) {
            return '添加人员失败';
        }
    }

    /**
     *  用户登录验证
     *
     * @param $data
     * @return array|false|\PDOStatement|string|Model
     * @throws DataNotFoundException
     * @throws DbException
     * @throws Exception
     * @throws ModelNotFoundException
     */
    public static function checkLogin($data)
    {
        $user = new Users();
        $name_exists = $user->where('name', $data['name'])->find();
        if (!$name_exists) {
            throw new Exception('用户名不存在');
        }

        $pwd_correct = $user->where('password', think_encrypt($data['password']))->where('name', $data['name'])->find();
        if (!$name_exists) {
            throw new Exception('密码错误');
        }

        return $pwd_correct;
    }

    /**
     * 根据主键获取用户模型
     *
     * @param $user_id
     * @return Users
     * @throws DbException
     * @throws Exception
     */
    public static function getUserById($user_id)
    {
        $user = Users::get($user_id);
        if (!$user) {
            throw new Exception('获取用户失败');
        }

        return $user;
    }

    /**
     *  根据ID跟新用户信息
     *
     * @param $data
     * @param $user_id
     * @throws DbException
     * @throws Exception
     */
    public static function updUser($data, $user_id)
    {
        $user = Users::get($user_id);
        if (!$user) {
            throw new Exception('获取用户信息失败');
        }

        $upd_res = $user->save($data, ['id' => $user_id]);
        if (!$upd_res) {
            throw new Exception('更新用户失败');
        }
    }
}