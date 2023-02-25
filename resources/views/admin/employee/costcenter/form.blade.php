@extends('admin.master')
@section('content')
@section('title')
@if(isset($editModeData))
@lang('costcenter.edit_costcenter')
@else
@lang('costcenter.add_costcenter')
@endif

@endsection

<div class="container-fluid">
	<div class="row bg-title">
		<div class="col-lg-3 col-md-4 col-sm-4 col-xs-12">
			<ol class="breadcrumb">
				<li class="active breadcrumbColor"><a href="{{ url('dashboard') }}"><i class="fa fa-home"></i> @lang('dashboard.dashboard')</a></li>
				<li>@yield('title')</li>

			</ol>
		</div>
		<div class="col-lg-9 col-md-8 col-sm-8 col-xs-12">
			<a href="{{route('costcenter.index')}}" class="btn btn-success pull-right m-l-20 hidden-xs hidden-sm waves-effect waves-light"><i class="fa fa-list-ul" aria-hidden="true"></i>@lang('costcenter.view_costcenter')</a>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-info">
				<div class="panel-heading"><i class="mdi mdi-clipboard-text fa-fw"></i>@yield('title')</div>
				<div class="panel-wrapper collapse in" aria-expanded="true">
					<div class="panel-body">
						@if(isset($editModeData))
						{{ Form::model($editModeData, array('route' => array('costcenter.update', 2), 'method' => 'PUT','files' => 'true','class' => 'form-horizontal')) }}
						@else
						{{ Form::open(array('route' => 'costcenter.store','enctype'=>'multipart/form-data','class'=>'form-horizontal')) }}
						@endif
						<div class="form-body">
							<div class="row">
								<div class="col-md-offset-2 col-md-6">
									@if($errors->any())
									<div class="alert alert-danger alert-dismissible" role="alert">
										<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button>
										@foreach($errors->all() as $error)
										<strong>{!! $error !!}</strong><br>
										@endforeach
									</div>
									@endif
									@if(session()->has('success'))
									<div class="alert alert-success alert-dismissable">
										<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
										<i class="cr-icon glyphicon glyphicon-ok"></i>&nbsp;<strong>{{ session()->get('success') }}</strong>
									</div>
									@endif
									@if(session()->has('error'))
									<div class="alert alert-danger alert-dismissable">
										<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
										<i class="glyphicon glyphicon-remove"></i>&nbsp;<strong>{{ session()->get('error') }}</strong>
									</div>
									@endif
								</div>
							</div>
							<div class="row">
								<div class="col-md-8">
									<div class="form-group">
										<label class="control-label col-md-4">@lang('costcenter.sub_department_name')<span class="validateRq">*</span></label>
										<div class="col-md-8">
											{{ Form::select('sub_department_id',$subDepartmentList, Input::old('sub_department_id'), array('class' => 'form-control sub_department_id select2 required')) }}
										</div>
									</div>
								</div>
								<div class="col-md-8">
									<div class="form-group">
										<label class="control-label col-md-4">@lang('costcenter.costcenter_number')<span class="validateRq">*</span></label>
										<div class="col-md-8">
											{!! Form::text('cost_center_number',Input::old('cost_center_number'), $attributes = array('class'=>'form-control required cost_center_number','id'=>'cost_center_number','placeholder'=>__('costcenter.cost_center_number'))) !!}
										</div>
									</div>
								</div>
							</div>
							<div class="form-actions">
								<div class="row">
									<div class="col-md-8">
										<div class="row">
											<div class="col-md-offset-4 col-md-8">
												@if(isset($editModeData))
												<button type="submit" class="btn btn-info btn_style"><i class="fa fa-pencil"></i> @lang('common.update')</button>
												@else
												<button type="submit" class="btn btn-info btn_style"><i class="fa fa-check"></i> @lang('common.save')</button>
												@endif
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						{{ Form::close() }}
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection