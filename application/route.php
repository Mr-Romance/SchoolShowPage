<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
use think\Route;

//return [
//    '__pattern__' => [
//        'name' => '\w+',
//    ],
//    '[hello]'     => [
//        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
//        ':name' => ['index/hello', ['method' => 'post']],
//    ],
//];

/**
 *  基础及首页路由
 */
Route::get('login','index/common/showLogin');
Route::post('login','index/common/login');
Route::get('logout','index/common/logout');
Route::get('register','index/common/showRegister');
Route::post('register','index/common/register');
Route::get('/','index/index/index');
Route::get('resource_index','index/index/resourceIndex');
Route::get('resource_category','index/index/resourceCategory');

/**
 * 用户相关操作的路由
 */
Route::get('user_page','index/admin/userPage');
Route::post('user_edit','index/admin/editUser');
Route::post('upd_head_portrait','index/admin/updHeadPortrait');

Route::post('add_category','index/resource/addCategory');


