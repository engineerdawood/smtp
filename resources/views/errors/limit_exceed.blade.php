@extends('layouts.app')
@section('title', trans('general.limitExceedTitle'))

@section('header_space')

@endsection

@section('content')

    <div class="row">
        <div class="col-sm-12">
            <div class="alert alert-warning">{{ $message }}</div>
            <div class="text-center">
                <a href="{{ helper()->getPageUrl('main::my::pricing') }}" class="btn btn-info">@lang('general.btn.upgradeplan')</a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12 m-lg-t">
            @include('layouts.ad', ['adspace' => 1])
        </div>
    </div>

@stop

@section('footer_space')
@stop
