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

Route::get('login','index/common/showLogin');
Route::post('login','index/common/login');
Route::get('register','index/common/showRegister');
Route::post('register','index/common/register');
Route::get('/','index/index/index');
Route::get('resource_index','index/index/resourceIndex');
Route::get('resource_category','index/index/resourceCategory');


Route::post('add_category','index/resource/addCategory');


