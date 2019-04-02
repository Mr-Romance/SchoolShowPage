<?php

namespace app\index\controller;

use app\index\model\Categories;
use app\index\model\Resources;
use app\index\model\Users;
use think\Config;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;

class Index extends Controller
{
    /**
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    protected function _initialize()
    {
        $user_id = Session::get('login_user_id');
        if (!empty($user_id)) {
            $user = Users::getUserById($user_id);
            $this->assign('user', $user);
        }

        // 获取所有的主题信息
        $subject = Config::get('resource_subject');
        $this->assign('subject', $subject);
    }

    /**
     *  门户首页
     *
     * @return mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $dong_tai_info = Resources::getDataBySubject(2, 4);
        $dong_tai_info2 = Resources::getDataBySubject(2, 4, 4);
        $this->assign('dong_tai_info', $dong_tai_info);
        $this->assign('dong_tai_info2', $dong_tai_info2);
        $this->assign('dt_subject_id', 2);

        $zheng_ce_info = Resources::getDataBySubject(1, 3);
        $this->assign('zheng_ce_info', $zheng_ce_info);
        $this->assign('zc_subject_id', 1);

        $dian_xing_info = Resources::getDataBySubject(3, 3);
        $this->assign('dian_xing_info', $dian_xing_info);
        $this->assign('dx_subject_id', 3);

        $zong_jie_info = Resources::getDataBySubject(4, 4);
        $this->assign('zong_jie_info', $zong_jie_info);
        $this->assign('zj_subject_id', 4);


        $jian_she_info = Resources::getDataBySubject(5, 3);
        $jian_she_info2 = Resources::getDataBySubject(5, 3, 3);

        $this->assign('jian_she_info', $jian_she_info);
        $this->assign('jian_she_info2', $jian_she_info2);
        $this->assign('js_subject_id', 5);

        $tui_jian_resource = Resources::getTopResources(4);
        $this->assign('tui_jian_resource', $tui_jian_resource);

        return $this->fetch();
    }

    /**
     *  资源展示首页
     *
     * *
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function resourceIndex()
    {
        $recomend_resource_info = Resources::where('status', 1)->order('sort', 'desc')->paginate(4);
        $this->assign('recomend_resource_info', $recomend_resource_info);

        $new_resource_info = Resources::where('status', 1)->order('id', 'desc')->paginate(6);
        $this->assign('new_resource_info', $new_resource_info);

        $start_resource_info = Resources::where('status', 1)->order('user_id', 'asc')->paginate(4);
        $this->assign('start_resource_info', $start_resource_info);

        // 按照分类获取资源，获取前4名分类
        $top_category = Categories::order('sort', 'desc')->paginate(4);
        $category_resource_info = [];

        $cat_one = Resources::where('category', $top_category[0]->id)->order('id', 'desc')->paginate(4);
        $this->assign('cat_one', $cat_one);
        $this->assign('cat_one_name', $top_category[0]->name);

        $cat_two = Resources::where('category', $top_category[1]->id)->order('id', 'desc')->paginate(4);
        $this->assign('cat_two', $cat_two);
        $this->assign('cat_two_name', $top_category[1]->name);

        $cat_three = Resources::where('category', $top_category[2]->id)->order('id', 'desc')->paginate(4);
        $this->assign('cat_three', $cat_three);
        $this->assign('cat_three_name', $top_category[2]->name);

        $cat_four = Resources::where('category', $top_category[3]->id)->order('id', 'desc')->paginate(4);
        $this->assign('cat_four', $cat_four);
        $this->assign('cat_four_name', $top_category[3]->name);

        $top_four_teacher = Users::where('status', 1)->order('id', 'asc')->paginate(4);
        $this->assign('top_four_teacher', $top_four_teacher);

        // 统计信息 此处巨坑，再也不用tp
        // 没找到group_by，这里先使用原生查询
        $top_five_user = Db::query('select count(*) as total_count ,user_id from resources group by user_id order by total_count desc limit 5');
        $users_arr = [];
        if (!empty($top_five_user)) {
            foreach ($top_five_user as $top) {
                $user = Users::get($top['user_id']);
                $name = $user['name'];
                $count = $top['total_count'];
                $users_arr[] = ['name' => $name, 'count' => $count];
            }
        }
        $this->assign('counter_users', $users_arr);

        $top_five_category = Db::query('select count(*) as total_count ,category from resources group by category order by total_count desc limit 5');
        $categories_arr = [];
        if (!empty($top_five_category)) {
            foreach ($top_five_category as $top) {
                $categorie = Categories::get($top['category']);
                $count = $top['total_count'];
                $name = $categorie['name'];
                $categories_arr[] = ['name' => $name, 'count' => $count];
            }
        }
        $this->assign('counter_category', $categories_arr);

        $top_five_subject = Db::query('select count(*) as total_count ,subject from resources group by category order by total_count desc limit 5');
        $subjects_arr = [];
        if (!empty($top_five_subject)) {
            foreach ($top_five_subject as $top) {
                $subjects_arr[] = ['name' => Config::get('subject_info')[$top['subject']], 'count'=>$top['total_count']];
            }
        }
        $this->assign('counter_subject', $subjects_arr);

        return $this->fetch();
    }

    /**
     *  带分类的资源首页搜索页面
     *
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function resourceCategory()
    {
        $search_param = [];

        if (Session::has('param.search_category')) {
            $search_param['res_category'] = explode(',', Session::get('param.search_category'));
        }
        if (Session::has('param.search_type')) {
            $search_param['res_type'] = explode(',', Session::get('param.search_type'));
        }
        if (Session::has('search_title')) {
            $search_param['res_title'] = Session::get('search_title');
        }
        if (Session::has('order_type')) {
            $search_param['order_type'] = Session::get('order_type');
        }

        // 显示所有
        $list = Resources::getResourcesList($search_param, 0);

        // 获取所有的一级分类
        $cat_groups = Categories::getCategoriesGroup();
        if ($cat_groups) {
            $this->assign('cat_groups', $cat_groups);
        }

        // 资源类型信息
        $res_type = Config::get('resource_type');

        // 渲染赋值
        if (!empty($res_type)) {
            $this->assign('res_type', $res_type);
        }
        if (!empty($search_param['res_type'])) {
            $this->assign('type_checked', $search_param['res_type']);
        }
        if (!empty($search_param['res_category'])) {
            $this->assign('category_checked', $search_param['res_category']);
        }
        $this->assign('list', $list);

        $page = $list->render();
        $this->assign('page', $page);

        return $this->fetch();
    }

    /**
     *  根据搜索条件，保存用户的查询信息到session
     *
     * @param Request $request
     * @return mixed
     */
    public function searchUserResourceList(Request $request)
    {
        $param = $request->param();
        $search_param = [];

        if (!empty($param['search_cat_ids'])) {
            // 把字符串打散为数组
            $search_param['res_category'] = explode(',', $param['search_cat_ids']);
            // 这里存入的，其实是一个字符串，tp——session不支持3维数组
            Session::set('param.search_category', $param['search_cat_ids']);
        } else {
            Session::delete('param.search_category');
        }

        if (!empty($param['search_type_ids'])) {
            $search_param['res_type'] = explode(',', $param['search_type_ids']);
            Session::set('param.search_type', $param['search_type_ids']);
        } else {
            Session::delete('param.search_type');
        }
        if (!empty($param['search_title'])) {
            $search_param['res_title'] = $param['search_title'];
            Session::set('search_title', $param['search_title']);
        } else {
            Session::delete('search_title');
        }
        if (!empty($param['order_type'])) {
            $search_param['order_type'] = $param['order_type'];
            Session::set('order_type', $param['order_type']);
        } else {
            Session::delete('order_type');
        }
    }

    /**
     *  按照主题进行展示
     *
     * @param Request $request
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function subjectIndex(Request $request)
    {
        $subject_id = $request->param('subject_id');
        // 获取所有的主题信息
        $subject = Config::get('resource_subject');
        $this->assign('subject', $subject);

        if (empty($subject_id)) {
            return $this->fetch();
        }

        $subject_info = Config::get('subject_info');

        /**
         * @var array $subject_info
         */
        $subject_name = $subject_info[$subject_id];
        $this->assign('subject_name', empty($subject_name) ? '无主题' : $subject_name);

        $this->assign("subject_id", $subject_id);

        $list = Resources::where('subject', $subject_id)->paginate(10);
        $this->assign('list', $list);
        return $this->fetch();
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function indexShowResource(Request $request)
    {
        $id = $request->param('id');
        $subject_id = $request->param('subject_id');
        $subject_info = Config::get('subject_info');

        /**
         * @var array $subject_info
         */
        $subject_name = $subject_info[$subject_id];
        $this->assign('subject_name', empty($subject_name) ? '无主题' : $subject_name);
        $this->assign('subject_id', $subject_id);


        $resource = Resources::get($id);
        $this->assign('resource', $resource);
        return $this->fetch();
    }

    /**
     *  资源库首页显示详情
     *
     * @param Request $request
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function zykResourceShow(Request $request)
    {
        $res_id = $request->param('id');

        $resource = Resources::get($res_id);
        $this->assign('resource', $resource);

        $user = Users::get($resource->user_id);
        $this->assign('user', $user);

        return $this->fetch();
    }

    /**
     *  展示该老师的资源列表
     *
     * @param Request $request
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function teacherResourceList(Request $request)
    {
        $user_id = $request->param('user_id');

        $list = Resources::where('user_id', $user_id)->paginate(10);
        $this->assign('list', $list);

        // 给分页连接加参数
        $list->appends(request()->param());

        $page = $list->render();
        $this->assign('page', $page);

        $user = Users::get($user_id);
        $this->assign('user', $user);

        return $this->fetch();
    }
}
