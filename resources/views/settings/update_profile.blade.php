@extends('layouts.app')

@section('content')
    <!-- heading -->
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-4">
            <h2>{{ trans('translate.update_profile') }}</h2>
        </div>
    </div>

    <!-- content -->
    <div class="wrapper wrapper-content">
        <div>
            <div class="panel panel-default">
                <div class="panel-body">
                    <!-- notification -->
                    @if(Session::has('flash_message'))
                    <div class="alert alert-success">
                        {{ Session::get('flash_message') }}
                    </div>
                    @endif
                    <!-- Display Validation Errors -->
                    @include('common.errors')

                    <!-- Form -->
                    <form action="{{ url('/settings/update-profile') }}" method="POST" class="form-horizontal">
                        {!! csrf_field() !!}

                        <!-- Name -->
                        <div class="form-group">
                            <label for="name" class="col-sm-3 control-label">{{ trans('translate.name') }}</label>

                            <div class="col-sm-6">
                                <input type="text" id="name" name="name" class="form-control" value="{{ old('name') ? old('name') : $user->name }}">
                            </div>
                        </div>

                        <!-- Locale -->
                        <div class="form-group">
                            <label for="locale" class="col-sm-3 control-label">{{ trans('translate.locale') }}</label>

                            <div class="col-sm-6">
                                <?php
                                $locale = old('locale') ? old('locale') : $user->locale
                                ?>
                                <select name="locale" id="locale" class="form-control">
                                    @foreach(config('app.locales') as $code => $language)
                                        <option value="{{ $code }}" {{ ($locale == $code) ? 'selected' : '' }}>{{ $language }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Save -->
                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-6">
                                <button type="submit" class="btn btn-success btn-blue">
                                    <i class="fa fa-btn fa-save"></i>{{ trans('translate.update') }}
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

@endsection