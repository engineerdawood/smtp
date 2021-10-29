<?php

namespace App\Custom\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

trait CustomAdminHelper {

    private $admin_url = '';

    function __construct()
    {
        $this->admin_url = $this->http_protocol . env('ADMIN_SUBDOMAIN') . str_replace($this->http_protocol, '', Config::get('app.url'));
    }

    public function is_admin(){
        return is_int(strpos(request()->root(), env('ADMIN_SUBDOMAIN')));
    }

    public function admin_url(){
        return $this->admin_url;
    }

    public function get_pagination()
    {
        return [10, 25, 50, 100];
    }

    public function get_admin_menus_by_type()
    {
//        switch(Auth::user()->role){
//            case "superadmin":
                $menu = ['main' => [
                    'admin::dashboard' => '<i class="fa fa-dashboard"></i> Dashboard',
                    'admin::campaigns.index' => '<i class="glyphicon glyphicon-th-list"></i> &nbsp;&nbsp;&nbsp;Campaigns',
                    [
                        '<i class="fa fa-sticky-note"></i> Templates',
                        'admin::templates.index' => '<i class="glyphicon glyphicon-th-list"></i> All Templates',
                        'admin::templates.create' => '<i class="glyphicon glyphicon-plus"></i> Add New',
                    ],
                    'admin::settings.index' => '<i class="fa fa-cog"></i> Settings',
                    'irb-lm::irb-store' => '<i class="fa fa-briefcase"></i> IRB Store',
                ]];
//                break;
//            default:
//                $menu = [];
//                break;
//        }
        return $menu;
    }

    public function get_menu_html($menu = 'main')
    {
        return $this->generate_navigation($this->get_admin_menus_by_type()[$menu]);
    }

    public function generate_navigation($navArr, $menuLevel=1, $listClass = '')
    {
        $ulClass = '';
        switch(true) {
            case ($menuLevel == 1):
                if(!is_bool($listClass))
                    $ulClass = 'class="nav side-menu ' . $listClass . '"';
                break;
            case ($menuLevel >= 2):
                if(!is_bool($listClass))
                    $ulClass = 'class="nav child_menu ' . $listClass . '"';
                break;
        }
        $navigationBar = '<ul '.$ulClass.'>';
        $activePage = $this->get_current_page();
        foreach($navArr as $pageUrl => $pageTitle) {
            $activePageClass = ($pageUrl == $activePage) ? ' active' : '';
            $pageUrl = (is_int(strpos($pageUrl, 'headerLabel'))) ? 'headerLabel' : $this->getPageUrl($pageUrl);
            if($pageUrl == 'headerLabel'){
                $navigationBar .= '<li class="header">' . $pageTitle . '</li>';
            } else if(is_int(strpos($pageUrl, 'multiNavbar'))){
                $navigationBar .= "<li class='treeview'><a href='javascript:void(0)'>$pageTitle</a><ul class='treeview-menu'></ul></li>";

            } else if(!is_array($pageTitle)){
                $navigationBar .= "<li class='$activePageClass'><a href='$pageUrl'>$pageTitle</a></li>";
            } else {
                $levelMenu  = ($menuLevel > 1) ? 'treeview' : '';
                $subMenuParent = $pageTitle[0];
                unset($pageTitle[0]);
                $subMenu    = $this->generate_navigation($pageTitle, $menuLevel+1);
                $navigationBar .= '<li class="'.$levelMenu;
                $navigationBar .= (strpos( $subMenu, 'active' ) === false) ? '' : ' active ';
                $navigationBar .= '"><a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">'.$subMenuParent.'</a>';
                $navigationBar .= $subMenu;
                $navigationBar .= '</li>';
            }
        }
        $navigationBar .= '</ul>';
        return $navigationBar;
    }

    public function get_admin_menus()
    {
        if(!Auth::check()){
            return false;
        }
        // return menu by employee type - admin, superadmin, employee etc
        $menubars = [
            'active' => (isset($this->getPathActions()['as'])) ? $this->getPathActions()['as'] : ''
        ];
        $menubars = array_merge($menubars, $this->get_admin_menus_by_type());
        return $menubars;
    }

    public function validate_admin_page($page)
    {
        if(empty($page))
            return true;
        $menuList = [];
        foreach($this->get_admin_menus_by_type() as $menuId => $menubar)
        {
            $menuList = array_merge($menuList, $menubar);
        }
        return isset($menuList[$page]);
    }

    public function get_gravatar( $email, $s = 80, $d = 'mm', $r = 'g', $img = false, $atts = array() ) {
        $url = 'https://www.gravatar.com/avatar/';
        $url .= md5( strtolower( trim( $email ) ) );
        $url .= "?s=$s&d=$d&r=$r";
        if ( $img ) {
            $url = '<img src="' . $url . '"';
            foreach ( $atts as $key => $val )
                $url .= ' ' . $key . '="' . $val . '"';
            $url .= ' />';
        }
        return $url;
    }
}
