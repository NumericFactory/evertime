@extends('layouts.app')

@section('content')
<!-- heading -->
<div class="row wrapper border-bottom white-bg page-heading">
    <div class="col-md-6">
        <h2>{{ trans('translate.current_timers') }}</h2>
    </div>
    <button class="btn btn-success pull-right btn-blue" onclick="window.location.href='{{ route('timer.create') }}'" style='margin-top:20px;'><i class="fa fa-btn fa-plus"></i> {{ trans('translate.add_timer') }}</button>
</div>

<!-- content -->
<div class="wrapper wrapper-content">
    <!-- notification -->
    @if(Session::has('flash_message'))
    <div class="alert alert-success">
        {{ Session::get('flash_message') }}
    </div>
    @endif
    @include('common.errors')

    <!-- timers -->
    @if (isset($timers) && count($timers) > 0)

    <div class="row">
        <div class="col-lg-12">
            <div class="ibox float-e-margins">
                <div class="ibox-content">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ trans('translate.timer') }}</th>
                                    <th>{{ trans('translate.get_code') }}</th>
                                    <th>{{ trans('translate.status') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($timers as $timer)
                                <?php
                                    $status = getTimerStatus($timer);
                                ?>
                                <tr>
                                    <td class="table-text">
                                        <p><a href='{{ url('/') }}/timer/{{ $timer->id }}'>{{ $timer->name }}</a></p>
                                        <div class='row'>
                                            <div class='col-md-8'>
                                                <!-- Edit -->
                                                <p><a href='{{ url('/') }}/timer/{{ $timer->id }}' class='btn btn-primary btn-blue'><i class="fa fa-btn fa-edit"></i>{{ trans('translate.edit') }}</a></p>
                                            </div>
                                            <div class='col-md-4'>
                                                <!-- Delete -->
                                                <form action="{{ url('/') }}/timer/{{ $timer->id }}" method="POST">
                                                    {{ csrf_field() }}
                                                    {{ method_field('DELETE') }}

                                                    <button type="submit" id="delete-timer-{{ $timer->id }}" class="btn btn-xs btn-danger" onclick='return confirm("{{ trans('translate.delete') }} {{ trans('translate.timer') }}?");'>
                                                        <i class="fa fa-btn fa-trash"></i>{{ trans('translate.delete') }}
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </td>

                                    <td class='embed'>
                                        <!-- Embed -->
                                        <a href='{{ url('/') }}/embed/{{ $timer->id }}' class="btn btn-default"><i class="fa fa-code"></i> {{ trans('translate.get_code') }}</a>
                                    </td>

                                    <td>
                                        @if($status == 'Active')
                                        <span class="label label-primary">{{ trans('translate.active') }}</span>
                                        @else
                                        <span class="label label-danger">{{ trans('translate.expired') }}</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection