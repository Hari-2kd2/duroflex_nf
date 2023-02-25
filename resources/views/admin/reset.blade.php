<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="{!! asset('icon.png') !!}" type="image/x-icon" />
    <!-- <link rel="shortcut icon" href="{!! asset('admin_assets/img/logo.png') !!}" type="image/x-icon" /> -->
    <title>Time-Check Reset-Password</title>
    <!-- Bootstrap Core CSS -->
    <link href="{!! asset('admin_assets/bootstrap/dist/css/bootstrap.min.css') !!}" rel="stylesheet">
    <!-- animation CSS -->
    <link href="{!! asset('admin_assets/css/animate.css') !!}" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{!! asset('admin_assets/css/style.css') !!}" rel="stylesheet">
    <!-- color CSS -->
    <link href="{!! asset('admin_assets/css/colors/default.css') !!}" id="theme" rel="stylesheet">
    <!-- toast CSS -->
    <link href="{!! asset('admin_assets/plugins/bower_components/toast-master/css/jquery.toast.css') !!}" rel="stylesheet">
    <style>
        .white-box {
            background: #E8E8E8;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 1px 1px 8px;
            margin: 5% 25% 5% 25%;
            border-radius: 12px;
            color: white;
        }

        .input {
            height: 32px;
            border-radius: 4px;
        }
    </style>
</head>

<body>
    <!-- Preloader -->
    <div class="preloader">
        <div class="cssload-speeding-wheel"></div>
    </div>
    <section id="wrapper" class="new-login-register" style="background: none;">
        <div class="container">
            @if (session()->has('success'))
                <div
                    class="alert alert-success alert-dismissable"style="position: absolute;right:0;width:380px;border-radius:4px;">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <p>{{ session()->get('success') }}</p>
                </div>
            @endif

            @if (session()->has('error'))
                <div class="alert alert-danger alert-dismissable"
                    style="position: absolute;right:0;width:380px;border-radius:4px;">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <p>{{ session()->get('error') }}</p>

                </div>
            @endif
            @if ($errors->any())
                <div
                    class="alert alert-danger alert-block alert-dismissable"style="position: absolute;right:0;width:380px;border-radius:4px;">
                    <ul>
                        <button type="button" class="close" data-dismiss="alert">x</button>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="panel white-box" style="background: #27333e">
                <div class="panel-header">
                    <h3 class="text-white">Reset Password</h3>
                    <hr>
                </div>
                <div class="panel-body">
                    <form type="multipart/formdata">
                        <input type="hidden" name="token" class="token" value="{{ $token }}">
                        <div class="form-group row">
                            <label for="email" class="col-md-4 col-form-label text-md-right">E-Mail
                                Address</label>
                            <div class="col-md-8">
                                <input id="email" type="email"
                                    class="form-control email input @error('email') is-invalid @enderror" name="email"
                                    value="{{ $email ?? old('email') }}" autocomplete="email" autofocus>
                            </div>
                        </div>

                        <div class="form-group row">
                            <label for="password" class="col-md-4 col-form-label text-md-right">Password</label>
                            <div class="col-md-8">
                                <input id="password" type="password"
                                    class="form-control password input @error('password') is-invalid @enderror"
                                    name="password" autocomplete="new-password">
                            </div>

                        </div>

                        <div class="form-group row">
                            <label for="password-confirm" class="col-md-4 col-form-label text-md-right">Confirm
                                Password</label>
                            <div class="col-md-8">
                                <input id="password-confirm" type="password"
                                    class="form-control password_confirmation input" name="password_confirmation"
                                    autocomplete="new-password">
                            </div>
                        </div>

                        <div class="form-group row mb-0 pull-right">
                            <div class="col-md-6 offset-md-4">
                                <button type="submit" id="reset-btn" class="btn btn-instagram">
                                    Reset Password
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>



    </section>
    <!-- jQuery -->
    <script src="{!! asset('admin_assets/plugins/bower_components/jquery/dist/jquery.min.js') !!}"></script>
    <!-- Bootstrap Core JavaScript -->
    <script src="{!! asset('admin_assets/bootstrap/dist/js/bootstrap.min.js') !!}"></script>
    <!-- Menu Plugin JavaScript -->
    <script src="{!! asset('admin_assets/plugins/bower_components/sidebar-nav/dist/sidebar-nav.min.js') !!}"></script>

    <!--slimscroll JavaScript -->
    <script src="{!! asset('admin_assets/js/jquery.slimscroll.js') !!}"></script>
    <!--Wave Effects -->
    <script src="{!! asset('admin_assets/js/waves.js') !!}"></script>
    <!-- Custom Theme JavaScript -->
    <script src="{!! asset('admin_assets/js/custom.min.js') !!}"></script>
    <script src="{!! asset('admin_assets/plugins/bower_components/toast-master/js/jquery.toast.js') !!}"></script>
    <script src="{!! asset('admin_assets/js/toastr.js') !!}"></script>
    <script>
        $('#reset-btn').click(function(e) {
            e.preventDefault();
            var token = $('.token').val();
            var email = $('.email').val();
            var password = $('.password').val();
            var password_confirmation = $('.password_confirmation').val();
            $.ajax({
                type: "get",
                url: "change-password",
                data: {
                    email: email,
                    password: password,
                    password_confirmation: password_confirmation,
                    token: token,
                },
                success: function(data) {
                    alert(data);
                    if (data != 'success') {
                        $.toast({
                            heading: 'Warning',
                            text: 'Something Error Found !, Please try again. !',
                            position: 'top-right',
                            loaderBg: '#ff6849',
                            icon: 'success',
                            hideAfter: 3000,
                            stack: 1
                        });
                        window.setTimeout(function() {
                            location.reload()
                        }, 1000)

                    } else {
                        $.toast({
                            heading: 'success',
                            text: 'Password reset successfully! Redirecting...',
                            position: 'top-right',
                            loaderBg: '#ff6849',
                            icon: 'success',
                            hideAfter: 3000,
                            stack: 1
                        });
                        var url = "{{ url('/') }}";
                        setTimeout(function() {
                            window.location = url;
                        }, 2000);
                    }

                }
            });

        });

        $(function() {
            $(document).on("focus", "#backToLogin", function() {
                $("#recoverform").fadeOut("slow", function() {
                    $('#loginform').css('display', 'block');

                });
            });

            $(".alert-success").delay(1000).fadeOut("slow");
        });
    </script>
</body>

</html>
