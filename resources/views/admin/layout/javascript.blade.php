<!-- Switch js -->
<script src="{!! asset('js/bootstrap-toggle.min.js') !!}"></script>
<!-- Bootstrap Core JavaScript -->
<script src="{!! asset('admin_assets/js/moment.js') !!}"></script>
<script src="{!! asset('admin_assets/js/datetimepicker.js') !!}"></script>
<script src="{!! asset('admin_assets/bootstrap/dist/js/bootstrap.min.js') !!}"></script>
<!-- Menu Plugin JavaScript -->
<script src="{!! asset('admin_assets/plugins/bower_components/sidebar-nav/dist/sidebar-nav.min.js') !!}"></script>
<!--slimscroll JavaScript -->
<script src="{!! asset('admin_assets/js/jquery.slimscroll.js') !!}"></script>
<!--Wave Effects -->
<script src="{!! asset('admin_assets/js/waves.js') !!}"></script>
<!--Counter js -->
<script src="{!! asset('admin_assets/plugins/bower_components/waypoints/lib/jquery.waypoints.js') !!}"></script>
<!-- Sparkline chart JavaScript -->
<script src="{!! asset('admin_assets/plugins/bower_components/jquery-sparkline/jquery.sparkline.min.js') !!}"></script>
<!-- Custom Theme JavaScript -->
<script src="{!! asset('admin_assets/js/custom.js') !!}"></script>
<!--Jquery Toast  Script -->
<script src="{!! asset('admin_assets/plugins/bower_components/toast-master/js/jquery.toast.js') !!}"></script>
<!-- DataTable Script -->
<script src="{!! asset('admin_assets/latest/jquery.dataTables.min.js') !!}"></script>
<script src="{!! asset('admin_assets/plugins/bower_components/datatables/jquery.dataTables.min.js') !!}"></script>
<script src="{!! asset('admin_assets/plugins/bower_components/sweetalert/sweetalert-dev.js') !!}"></script>
<!-- bootstrap-datepicker -->
<script src="{!! asset('admin_assets/plugins/bower_components/datepicker/bootstrap-datepicker.js') !!}"></script>
<!--bootstrap Datetime Picker -->
<script src="{!! asset('admin_assets/js/moment.js') !!}"></script>
<!--TIme picker js-->
<script src="{!! asset('admin_assets/plugins/bower_components/timepicker/bootstrap-timepicker.min.js') !!}"></script>
<!-- select2 -->
<script src="{!! asset('admin_assets/plugins/bower_components/select2/select2.full.min.js') !!}"></script>
<script src="{!! asset('admin_assets/plugins/bower_components/toast-master/js/jquery.toast.js') !!}"></script>
<script src="{!! asset('admin_assets/js/toastr.js') !!}"></script>
<!-- jquery-validator -->
<script type="text/javascript" src="{!! asset('admin_assets/plugins/bower_components/jquery-validator/jquery-validator.1.15.0.js') !!}"></script>
<script type="text/javascript" src="{!! asset('admin_assets/plugins/bower_components/jquery-validator/jquery-additional-method.1.15.0.min.js') !!}"></script>
<!-- Star Ratings -->
<script src="{!! asset('admin_assets/plugins/bower_components/rateyo/jquery.rateyo.js') !!}"></script>



<script>
    $(function() {
        $(".select2").select2();
        $('#myTable').DataTable({
            "ordering": false,
        });

    });

    function addMenuClass() {
        var segment3 = '{{ Request::segment(1) }}';
        var url = base_url + segment3;
        // var navItem = $(this).find("[href='" + url + "']");

        $('a[href="' + url + '"]').parents('.treeview-menu').addClass('collapse in');
        $('a[href="' + url + '"]').parents('.treeview-menu').parent().children('.module').addClass('active');
    }

    $(".alert-success").delay(2000).fadeOut("slow");
    //   $(".alert-danger").delay(2000).fadeOut("slow");
    $(document).on("focus", ".yearPicker", function() {
        $(this).datepicker({
            format: 'yyyy',
            minViewMode: 2
        }).on('changeDate', function(e) {
            $(this).datepicker('hide');
        });
    });

    $(document).on("focus", ".datetimepicker", function() {
        $(this).datetimepicker({
            format: 'YYYY-MM-DD hh:mm a',
        }).on('changeDate', function(e) {
            $(this).datetimepicker('hide');
        });
    });

    $(document).on("focus", ".dateField", function() {
        $(this).datepicker({
            format: 'dd/mm/yyyy',
            todayHighlight: true,
            clearBtn: true
        }).on('changeDate', function(e) {
            $(this).datepicker('hide');
        });
    });
    $(document).on("focus", ".timePicker", function() {
        $(this).timepicker({
            showInputs: false,
            minuteStep: 1
        });
    });
    $(".monthField").datepicker({
        format: "yyyy-mm",
        viewMode: "months",
        minViewMode: "months"
    }).on('changeDate', function(e) {
        $(this).datepicker('hide');
    });

    $(document).on('click', '.delete', function() {
        var actionTo = $(this).attr('href');
        var token = $(this).attr('data-token');
        var id = $(this).attr('data-id');
        swal({
                title: "Are you sure?",
                text: "You will not be able to recover this imaginary file!",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, delete it!",
                closeOnConfirm: false
            },
            function(isConfirm) {
                if (isConfirm) {
                    $.ajax({
                        url: actionTo,
                        type: 'post',
                        data: {
                            _method: 'delete',
                            _token: token
                        },
                        success: function(data) {
                            var formsg = data.split("|||");
                            data = data.split("|||");
                            data = data[0];
                            console.log(data);
                            if (data == 'hasForeignKey') {
                                swal({
                                    title: "Oops!",
                                    text: "This data is used anywhere",
                                    type: "error"
                                });
                            } else if (data == 'success') {
                                swal({
                                        title: "Deleted!",
                                        text: "Your information delete successfully.",
                                        type: "success"
                                    },
                                    function(isConfirm) {
                                        if (isConfirm) {
                                            $('.' + id).fadeOut();
                                        }
                                    });
                            } else if (data == 'all_device_offline') {
                                swal({
                                    title: "Oops!",
                                    text: "All device are offline. So you cannot delete this employee",
                                    type: "error"
                                });
                            } else if (data == 'some_device_offline') {
                                swal({
                                    title: "Oops!",
                                    text: "Following device are offline[ '" + formsg[
                                            1] +
                                        "' ]. So you cannot delete this employee",
                                    type: "error"
                                });
                            } else {
                                swal({
                                    title: "Error!",
                                    text: "Something Error Found !, Please try again.",
                                    type: "error"
                                });
                            }
                        }

                    });
                } else {
                    swal("Cancelled", "Your data is safe .", "error");
                }
            });
        return false;
    });

    $(document).on('click', '.logout', function() {
        var actionTo = "{{ url('ajaxlogout') }}";
        var token = $(this).attr('data-token');
        var id = $(this).attr('data-id');
        swal({
                title: "Are you sure?",
                text: "Please select Yes to confirm!",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Yes, logout!",
                closeOnConfirm: false
            },
            function(isConfirm) {
                if (isConfirm) {
                    $.ajax({
                        url: actionTo,
                        type: 'get',
                        data: {
                            _method: 'get',
                            _token: token
                        },
                        success: function(data) {
                            var formsg = data.split("|||");
                            data = data.split("|||");
                            data = data[0];

                            if (data == 'success') {
                                swal({
                                        title: "Log out!",
                                        text: "Your account logout successfully.",
                                        type: "success"
                                    },
                                    function(isConfirm) {
                                        if (isConfirm) {
                                            $('.' + id).fadeOut();
                                        }
                                    });

                                setInterval(() => {
                                    window.location.href = "login";
                                }, 1000);

                            } else {
                                swal({
                                    title: "Error!",
                                    text: "Something Error Found !, Please try again.",
                                    type: "error"
                                });
                            }



                        }
                    });
                } else {
                    swal("Cancelled", "You are still logged in .", "error");
                }
            });
        return false;
    });
</script>
