<!-- ============================================================== -->
<!-- Left Sidebar - style you can find in sidebar.scss  -->
<!-- ============================================================== -->
<div class="navbar-default sidebar" role="navigation">
    <div class="sidebar-nav slimscrollsidebar">
        <div class="sidebar-head">
            <h3><span class="fa-fw open-close"><i class="ti-close ti-menu"></i></span> <span
                    class="hide-menu">Navigation</span>
            </h3>
        </div>
        <div style="padding-top: 60px "></div>
        <ul class="nav" id="side-menu">
            <li><a href="{{ url('dashboard') }}" class="waves-effect waves-ripple"><i class="mdi mdi-home hideMenu"
                        data-icon="v"></i> <span class="hide-menu hideMenu">
                        {{ __('menu.dashboard') }}
                    </span></a>
            </li>
            <?php
            $sideMenu = showMenu();
            $menuItem = '';
            
            foreach ($sideMenu as $key => $value) {
                $menuItem .= '<li class="treeview waves-effect waves-ripple"><a href="javascript:void(0)" class="module"><i class="iconFontSize ' . $value['icon_class'] . ' hideMenu"></i> <span class="hide-menu hideMenu">&nbsp;' . __('menu' . '.' . str_replace(' ', '_', strtolower($value['name']))) . '<span class="fa arrow"></span></span></a>';
                if ($value['sub_menu']) {
                    $menuItem .= '<ul class="treeview-menu nav nav-second-level">';
            
                    foreach ($value['sub_menu'] as $menu) {
                        if ($menu['menu_url'] != '' || $menu['sub_menu']) {
                            $menuItem .= '<li><a href="' . ($menu['menu_url'] ? route($menu['menu_url']) : 'javascript:void(0)') . '"><i data-icon="/" class="linea-icon linea-basic fa-fw"></i><span class="hideMenu">' . __('menu' . '.' . str_replace(' ', '_', strtolower($menu['name']))) . '</span>' . ($menu['sub_menu'] ? '<i class="fa arrow"></i>' : '') . '</a>';
                            if ($menu['sub_menu']) {
                                $menuItem .= '<ul class="treeview-menu nav nav-third-level">';
                                foreach ($menu['sub_menu'] as $subMenu) {
                                    $menuItem .= '<li class=""><a class="hideMenu" href="' . ($subMenu['menu_url'] ? route($subMenu['menu_url']) : 'javascript:void(0)') . '"> <i class="fa fa-circle-o"></i> &nbsp;' . __('menu' . '.' . str_replace(' ', '_', strtolower($subMenu['name']))) . '</a></li>';
                                }
                                $menuItem .= '</ul>';
                            }
                            $menuItem .= '</li>';
                        }
                    }
            
                    $menuItem .= '</ul>';
                }
            
                $menuItem .= '</li>';
            }
            echo $menuItem;
            ?>

            {{-- <a title="Logout" class="text-white btn btn-sm logout"
                style="position: absolute;bottom: 6px;right: 6px;font-size:12px;background:#F3F3F3;color:#3E729A"><span
                    style="font-size:12px"></span><i class="fa fa-power-off"></i></a>
            <a href="{{ url('profile') }}" title="Profile" class="text-white btn btn-sm"
                style="position: absolute;bottom: 6px;right: 44px;font-size:12px;background:#F3F3F3;white;color:#3E729A"><span
                    style="font-size:12px"></span><i class="fa fa-user"></i></a> --}}

        </ul>

        <!-- /.dropdown-user -->
    </div>
    {{-- <div><a href="{{ URL::to('/logout') }}" class="text-white btn btn-instagram btn-sm"
            style="position: absolute;bottom: 12px;right: 12px;font-size:12px"><span
                style="padding-right: 12px;font-size:12px">Logout</span><i class="fa fa-power-off"></i></a>
    </div> --}}
    {{-- <div><a href="{{ url('profile') }}" class="text-white btn btn-instagram btn-sm"
            style="position: absolute;bottom: 12px;left: 12px;font-size:14px"><span
                style="padding-right: 12px;font-size:14px">{!! ucwords('Profile') !!}</span><i class="fa fa-user"></i></a>
    </div> --}}
</div>
