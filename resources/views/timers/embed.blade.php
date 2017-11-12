@extends('layouts.app')

@section('content')
    <!-- clipboard -->
    <script src="/js/plugins/clipboard/clipboard.min.js"></script>
    <!-- colorpicker -->
    <script src="/js/plugins/colorpicker/bootstrap-colorpicker.min.js"></script>
    <link rel="stylesheet" href="/css/plugins/colorpicker/bootstrap-colorpicker.min.css" property="stylesheet">
    <!-- js -->
    <script>

        function getEmailPlaceholder(email_service){
            var email_placeholder = '';
            switch(email_service){
                case 'ActiveCampaign':
                    email_placeholder = '%EMAIL%';
                    break;

                case 'iContact':
                    email_placeholder = '[email]';
                    break;

                case 'InfusionSoft':
                    email_placeholder = '~Contact.Email~';
                    break;

                case 'Aweber':
                    email_placeholder = '{!email}';
                    break;

                case 'Ontraport/OAP':
                    email_placeholder = '[E-Mail]';
                    break;

                case 'GetResponse':
                    email_placeholder = '[[email]]';
                    break;

                case 'CampaignMonitor':
                    email_placeholder = '[email]';
                    break;

                case 'MailChimp':
                    email_placeholder = '*|EMAIL|*';
                    break;

                case '1ShoppingCart':
                    email_placeholder = '%$email$%';
                    break;

                case 'SendReach':
                    email_placeholder = '[email]';
                    break;

                case 'ClickFunnelsActionetics':
                    email_placeholder = '#EMAIL#';
                    break;

                default:
                    break;
            }

            return email_placeholder;
        }

        $(function() {
            // select code on field focus
            $("input").on('focus', function () {
               $(this).select();
            });

            // clipboard
            var clipboard = new Clipboard('.btn.clipboard');
            clipboard.on('success', function(e) {
                toastr.success('{{ trans('translate.embed_copy_message') }}', '{{ trans('translate.embed_copy_title') }}');
            });

            var params = {};

            // inline vs floating
            $('.floating').hide();
            $('.email_service_box').hide();
            $('#type').on('change', function() {
                switch ($(this).val()) {
                    case 'fb':
                        $('.floating').show('fast');
                        break;
                    default:
                        $('.floating').hide('fast');
                        break;
                }
            });

            $('#email_type').on('change', function() {
                switch ($(this).val()) {
                    case 'locked_email':
                        $('.email_service_box').show('fast');
                        break;
                    default:
                        $('.email_service_box').hide('fast');
                        break;
                }
            });

            $('#email_type, #email_service').on('change', function (){
                var baseUrl = '{{ url("/")."/" }}';
                var timer_id = '{{ $timer->id }}';
                var image_url = baseUrl+'st/'+timer_id;
                var link_url = baseUrl+'lst/'+timer_id+'?medium_type=email';
                var email_type = $('#email_type').val();
                var email_service = $('#email_service').val();
                var email_placeholder = '';

                if(email_type == 'locked_email'){
                    email_placeholder = getEmailPlaceholder(email_service);
                    image_url += '?email='+email_placeholder;
                    link_url += '&email='+email_placeholder;
                }
                var html_code = '<p style="text-align:center;"><a href="'+link_url+'"><img style="max-width:100%;height:auto;" src="'+image_url+'"></a></p>';
                $('#email-code').val(html_code);
                $('#email-image').val(image_url);
                $('#email-link').val(link_url);
            });


            // background
            $('#background, #type, #position, #expire_action').on('change', function() {
                var baseUrl = '{{ url("/")."/" }}';
                var timer_id = '{{ $timer->id }}';
                var ae = $('#expire_action').val();
                if ($('#type').val() == 'fb') {
                    params = {};
                    params['t'] = $('#type').val();
                    params['p'] = $('#position').val();
                    params['medium_type'] = 'web_floating';
                    // params['bg'] = $('#background').val();
                    if(ae != ''){
                        params['ae'] = ae;
                    }
                    code = '<script src="'+baseUrl+'jst/'+timer_id+'/?'+ $.param(params) +'"></' + 'script><div class="jst-cdt-{{ $timer->id }}"></div>';
                } else {
                    if(ae != ''){
                        code = '<script src="'+baseUrl+'jst/'+timer_id+'/?medium_type=web_inline&ae='+ae+'"></' + 'script><div class="jst-cdt-{{ $timer->id }}"></div>';
                    } else {
                        code = '<script src="'+baseUrl+'jst/'+timer_id+'/?medium_type=web_inline"></' + 'script><div class="jst-cdt-{{ $timer->id }}"></div>';
                    }
                }

                $('#webcode').val(code);

            });

            // color picker
            $('#background').colorpicker();
        })
    </script>

    <!-- heading -->
    <div class="row wrapper border-bottom white-bg page-heading">
        <div class="col-sm-4">
            <h2>{{ trans('translate.get_embed_code') }}</h2>
        </div>
    </div>

    <!-- content -->
    <div class="wrapper wrapper-content">

        <div class="tabs-container">
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#tab-web"> <i class="fa fa-globe"></i> {{ trans('translate.web') }}</a></li>
                <li class=""><a data-toggle="tab" href="#tab-email"><i class="fa fa-envelope"></i> {{ trans('translate.email') }}</a></li>
            </ul>
            <div class="tab-content">

                <div id="tab-web" class="tab-pane active">
                    <div class="panel-body">
                        <div class='form-horizontal'>

                            <!-- Type -->
                            <div class="form-group">
                                <label for="type" class="col-sm-2 control-label">{{ trans('translate.type') }}</label>
                                <div class="col-sm-2">
                                    <select id='type' class="form-control">
                                        <option value='i'>{{ trans('translate.inline') }}</option>
                                        <option value='fb'>{{ trans('translate.floating_bar') }}</option>
                                    </select>
                                </div>
                            </div>
                            <!-- Type -->
                            <div class="form-group">
                                <label for="type" class="col-sm-2 control-label">{{ trans('translate.expire_action') }}</label>
                                <div class="col-sm-2">
                                    <select id='expire_action' class="form-control">
                                        <option value=''>{{ trans('translate.do_nothing') }}</option>
                                        <option value='h'>{{ trans('translate.hide') }}</option>
                                        <option value='r'>{{ trans('translate.redirect') }}</option>
                                    </select>
                                </div>
                            </div>


                            <!-- Background Color -->
                            <div class="form-group floating hidden">
                                <label for="type" class="col-sm-2 control-label">{{ trans('translate.background') }}</label>
                                <div class="col-sm-2">
                                    <input type='text' id='background' value='#000000'>
                                </div>
                            </div>

                            <!-- Position -->
                            <div class="form-group floating">
                                <label for="type" class="col-sm-2 control-label">{{ trans('translate.position') }}</label>
                                <div class="col-sm-2">
                                    <select id='position' class="form-control">
                                        <option value='b'>{{ trans('translate.bottom') }}</option>
                                        <option value='t'>{{ trans('translate.top') }}</option>
                                    </select>
                                </div>
                            </div>

                            <!-- embed code -->
                            <div class="panel panel-success">
                                <div class="panel-heading">
                                    {{ trans('translate.your_code') }}
                                </div>
                                <div class="panel-body">
                                    <div class="input-group col-md-6">
                                        <input type="text" class="form-control" id='webcode' value='<script src="{{ url('/') }}/jst/{{$timer->id}}/?medium_type=web_inline"></script><div class="jst-cdt-{{ $timer->id }}"></div>'>
                                        <span class="input-group-btn">
                                            <button type="button" class="btn btn-default clipboard" data-clipboard-target='#webcode'><i class="fa fa-globe"></i> {{ trans('translate.copy') }}</button>
                                        </span>
                                    </div>
                                </div>
                            </div>

                        </div><!-- /.form-horizontal -->

                    </div>
                </div><!-- /#tab-web -->

                <div id="tab-email" class="tab-pane">
                    <div class="panel-body">
                        <div class='form-horizontal'>

                            <!-- Width -->
<!--                             <div class="form-group">
                                <label for="type" class="col-sm-2 control-label">Width</label>
                                <div class="col-sm-2">
                                    <div class="input-group col-md-6">
                                        <input type='number' class="form-control" id='width' value='300'>
                                        <span class="input-group-addon">px</span>
                                    </div>
                                    <span class="help-block">Max 300px recommended.</span>
                                </div>
                            </div>
 -->
                            @if($timer->timer_type == 'evergreen')
                            <div class="form-group">
                                <label for="type" class="col-sm-2 control-label">{{ trans('translate.type') }}</label>
                                <div class="col-sm-2">
                                    <select id='email_type' class="form-control">
                                        <option value='normal'>{{ trans('translate.normal') }}</option>
                                        <option value='locked_email'>{{ trans('translate.locked_email') }}</option>
                                    </select>
                                </div>
                            </div>
                            @endif

                            <!-- Email Services -->
                            <div class="form-group email_service_box">
                                <label for="type" class="col-sm-2 control-label">{{ trans('translate.email_service') }}</label>
                                <div class="col-sm-2">
                                    <select id='email_service' class="form-control">
                                        @foreach(config('app.email_service_provider') as $key => $value)
                                        <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>


                            <div class="panel panel-success">
                                <div class="panel-heading">
                                    {{ trans('translate.your_code') }}
                                </div>
                                <div class="panel-body">
                                    <!-- image url -->
                                    <div class="form-group">
                                        <label for="email-image" class="col-sm-2 control-label"><i class='fa fa-image'></i> {{ trans('translate.image_url') }}</label>
                                        <div class="input-group col-md-6">
                                            <input type="text" class="form-control" id='email-image' value='{{ url("/") }}/st/{{$timer->id}}'>
                                            <span class="input-group-btn">
                                                <button type="button" class="btn btn-default clipboard" data-clipboard-target='#email-image'><i class="fa fa-image"></i> {{ trans('translate.copy') }}</button>
                                            </span>
                                        </div>
                                    </div>
                                    <!-- link url -->
                                    <div class="form-group">
                                        <label for="email-link" class="col-sm-2 control-label"><i class='fa fa-link'></i> Link URL</label>
                                        <div class="input-group col-md-6">
                                            <input type="text" class="form-control" id='email-link' value='{{ url("/") }}/lst/{{$timer->id}}/?medium_type=email'>
                                            <span class="input-group-btn">
                                                <button type="button" class="btn btn-default clipboard" data-clipboard-target='#email-link'><i class="fa fa-globe"></i> {{ trans('translate.copy') }}</button>
                                            </span>
                                        </div>
                                    </div>
                                    <!-- html code -->
                                    <div class="form-group">
                                        <label for="email-code" class="col-sm-2 control-label"><i class='fa fa-code'></i> {{ trans('translate.html_code') }}</label>
                                        <div class="input-group col-md-6">
                                            <input type="text" class="form-control" id='email-code' value='<p style="text-align:center;"><a href="{{ url('/') }}/lst/{{$timer->id}}/?medium_type=email"><img style="max-width:100%;height:auto;" src="{{ url("/") }}/st/{{$timer->id}}"></a></p>'>
                                            <span class="input-group-btn">
                                                <button type="button" class="btn btn-default clipboard" data-clipboard-target='#email-code'><i class="fa fa-code"></i> {{ trans('translate.copy') }}</button>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div><!-- /.form-horizontal -->

                    </div>
                </div><!-- /#tab-email -->

            </div>
        </div>

    </div>
@endsection