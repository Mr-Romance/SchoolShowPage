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
Route::get('pdf_demo','index/index/pdfDemo');
Route::get('login', 'index/common/showLogin');
Route::post('login', 'index/common/login');
Route::get('logout', 'index/common/logout');
Route::get('register', 'index/common/showRegister');
Route::post('register', 'index/common/register');
Route::get('/', 'index/index/index');
Route::get('resource_index', 'index/index/resourceIndex');
Route::get('resource_category', 'index/index/resourceCategory');
Route::get('subject_index','index/index/subjectIndex');
Route::get('index_resource_show','index/index/indexShowResource');
Route::get('zyk_resource_show','index/index/zykResourceShow');
Route::get('teacher_res_list','index/index/teacherResourceList');

/**
 * 用户相关操作的路由
 */
Route::get('user_page', 'index/admin/userPage');
Route::post('add_user', 'index/admin/addUser');
Route::post('upd_user', 'index/admin/updUser');
Route::get('show_add_user', 'index/admin/showAddUser');
Route::post('user_edit', 'index/admin/editUser');
Route::post('upd_head_portrait', 'index/admin/updHeadPortrait');
Route::get('user_list','index/admin/userList');
Route::post('user_list_search','index/admin/userListSearch');
Route::post('delete_user','index/admin/deleteUser');
Route::get('show_edit_user','index/admin/showEditUser');

/**
 *  资源相关的操作
 */
Route::get('show_add_resource', 'index/resource/showAddResource');
Route::get('user_resource_list', 'index/resource/showUserResourceList');
Route::get('show_add_category', 'index/resource/showAddCategory');
Route::post('add_category', 'index/resource/addCategory');
Route::post('get_second_categories', 'index/resource/getSecondCategories');
Route::post('add_resource', 'index/resource/addResource');
Route::get('user_resource_list','index/resource/showUserResourceList');
Route::post('search_user_resource_list','index/index/searchUserResourceList');
Route::get('delete_search_session','index/resource/deleteSearchSession');
Route::get('show_user_resource_detail','index/resource/showUserResourceDetail');
Route::get('manage_resource_list','index/resource/manageResourceList');
Route::post('delete_resource','index/resource/deleteResource');
Route::post('delete_category','index/resource/deleteCategory');
Route::get('category_list','index/resource/categoryList');

Route::post('add_category', 'index/resource/addCategory');


