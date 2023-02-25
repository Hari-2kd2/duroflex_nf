<!-- ============================================================== -->
<!-- Topbar header - style you can find in pages.scss -->
<!-- ============================================================== -->
<nav class="navbar navbar-default navbar-static-top">
    <div class="navbar-header">

        <div class="top-left-part logo-visiability hidden-xs" style="background: #EC1B25;">
            <!-- Logo -->
            <a class="logo" href="{{ url('dashboard') }}">
                <!-- Logo icon image, you can use font-icon also --><b>
                    <!--This is dark logo icon--><img style="max-width: 210px;min-width:30px;object-fit:cover;"
                        src="{{ url('uploads/front/' . $front_setting->logo) }}" alt="Home"
                        class="dark-logo img-fluid img-responsive hidden-xs" />
                </b>
                <!-- Logo text image you can use text also --><span class="hidden-xs">
                    <!--This is dark logo text-->
                </span>
            </a>
        </div>

        <!-- Search input and Toggle icon -->

        <ul class="nav navbar-top-links navbar-left">
            <li><a href="javascript:void(0)" class="open-close waves-effect waves-light menuIcon"><i
                        class="ti-menu tiMenu"></i></a>
            </li>

            <li class="dropdown">
                <a class="dropdown-toggle waves-effect waves-light" data-toggle="dropdown" href="#"
                    style="color:#fff"> {{ App::getLocale() }}
                    <div class="notify"> <span class="heartbit"></span> <span class="point"></span> </div>
                </a>
                <ul class="dropdown-menu mailbox animated bounceInDown">
                    <li>
                        <div class="drop-title">@lang('common.chose_a_language')</div>
                    </li>
                    <li>
                        <div class="message-center">
                            <a href="{{ url('local/en') }}">

                                <h5>English</h5>
                            </a>
                        </div>


                        <div class="message-center">
                            <a href="{{ url('local/es') }}" title="Spanish">

                                <h5>Español</h5>
                            </a>
                        </div>

                        <div class="message-center">
                            <a href="{{ url('local/fr') }}" title="French">

                                <h5>Française</h5>
                            </a>
                        </div>

                        <div class="message-center">
                            <a href="{{ url('local/th') }}" title="Thai">

                                <h5>ไทย</h5>
                            </a>
                        </div>

                    </li>
                    <li>
                        <a class="text-center" href="javascript:void(0);"> <strong>@lang('common.see_all_languages')</strong> <i
                                class="fa fa-angle-right"></i> </a>
                    </li>
                </ul>
                <!-- /.dropdown-messages -->
            </li>

        </ul>
        {{-- <ul class="nav navbar-top-links navbar-right pull-right hidden-xs">
                    <li><a class="text-right" href="{{ Url('/') }}"><span class="btn btn-info">Visit
                                Website</span></a>
                    </li>
                </ul> --}}
        <ul class="nav navbar-top-links navbar-right pull-right imageIcon">
            {{-- <li>
                    <form role="search" class="app-search hidden-sm hidden-xs m-r-10">
                        <input type="text" placeholder="Search..." class="form-control"> <a href=""><i class="fa fa-search"></i></a> </form>
                </li> --}}
            <li class="dropdown">
                <?php
                    $employeeInfo = employeeInfo();
                    if($employeeInfo[0]->photo != ''){
                    ?>
                <a class="dropdown-toggle profile-pic" data-toggle="dropdown" href="#">
                    <img src="{!! asset('uploads/employeePhoto/' . $employeeInfo[0]->photo) !!}" alt="user-img" width="36" height="34" class="img-custom">
                    <b class="hidden-xs " style="color: #fff !important;padding-right: 4px"><span
                            class="text-capitalize">{!! ucwords($employeeInfo[0]->user_name) !!}</span></b>
                    <span class="caret"></span>
                </a>
                <?php  }else{ ?>
                <a class="dropdown-toggle profile-pic" data-toggle="dropdown" href="#">
                    <img src="{!! asset('admin_assets/img/default.png') !!}" alt="user-img" width="36" height="34"
                        class="img-custom"><span style="color: #fff !important;"></span>
                    <b class="hidden-xs" style="color: #fff !important; padding-right: 4px"><span class="hideMenu"
                            style="color: #fff !important;">{!! ucwords($employeeInfo[0]->user_name) !!}</span></b>
                    <span class="caret hideMenu"></span>
                </a>
                <?php } ?>
                <ul class="dropdown-menu dropdown-user animated stripMove imageDropdown" style="border: 1px solid #000">
                    <li><a href="{{ url('profile') }}"><i class="ti-user"></i> @lang('common.my_profile')</a>
                    </li>
                    {{-- <li role="separator" class="divider"></li> --}}
                    <li><a href="{{ url('changePassword') }}"><i class="ti-settings"></i>
                            @lang('common.change_password')</a></li>
                    {{-- <li role="separator" class="divider"></li> --}}
                    <li><a href="{{ URL::to('/logout') }}"><i class="fa fa-power-off"></i>
                            @lang('common.logout')</a></li>
                </ul>
                <!-- /.dropdown-user -->
            </li>
            <!-- /.dropdown -->
        </ul>
    </div>
    <!-- /.navbar-header -->
    <!-- /.navbar-top-links -->
    <!-- /.navbar-static-side -->
</nav>
<!-- End Top Navigation -->
