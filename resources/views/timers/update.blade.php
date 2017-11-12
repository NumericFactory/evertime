@extends('layouts.app')

@section('content')
    <!-- heading -->
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-4">
            <h2>{{ trans('translate.edit_timer') }}</h2>
        </div>
    </div>

    <!-- content -->
    <div class="wrapper wrapper-content">
        <div>
            <div class="panel panel-default">
                <div class="panel-body">
                    <!-- Display Validation Errors -->
                    @include('common.errors')

                    <!-- New Timer Form -->
                    {!! Form::model($timer, [
                        'method' => 'PATCH',
                        'action' => ['TimerController@store', $timer],
                        'class' => 'form-horizontal',
                        'enctype' => 'multipart/form-data'
                    ]) !!}
                     <input type="hidden" id="frenzy_count" value="{{ !empty($timer->frenzy) ? count(json_decode($timer->frenzy)) : 0 }}" />
                        @include('timers._fields')

                        <!-- Add Timer Button -->
                        <p></p>
                        <div class="alert alert-info" id="warning_url_mismatch" style="display: none;"></div>
                        <div class="col-md-offset-5 col-md-2">
                           <button type="submit" class="btn btn-primary btn-blue" id="add-timer">
                               <i class="fa fa-btn fa-save"></i> {{ trans('translate.save_timer') }}
                           </button>
                        </div>
                        <p></p>

                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>

@section('scripts')
<script type="text/javascript">
var frenzy_limit_count = '{{ Auth()->user()->max_frenzy_count }}';
var translate_days = '{{ trans('translate.days') }}';
var translate_hours = '{{ trans('translate.hours') }}';
var translate_minutes = '{{ trans('translate.minutes') }}';
var translate_seconds = '{{ trans('translate.seconds') }}';
var translate_before_deadline = '{{ trans('translate.before_deadline') }}';
var translate_remove_frenzy = '{{ trans('translate.remove_frenzy') }}';
var translate_with_less_than = '{{ trans('translate.with_less_than') }}';
var translate_change_active_link = '{{ trans('translate.change_active_link') }}';
var translate_show_warning = '{{ trans('translate.show_warning') }}';
var active_link_msg = '{{ trans('translate.active_link_msg') }}';
var redirect_active_link_msg = '{{ trans('translate.redirect_active_link_msg') }}';
var redirect_expired_link_msg = '{{ trans('translate.redirect_expired_link_msg') }}';
</script>
<script type="text/javascript" src="{{ asset('js/timer.js') }}"></script>
@endsection
@endsection