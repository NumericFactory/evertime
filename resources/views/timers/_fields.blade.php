        <div class="tabs-container">
            <ul class="nav nav-tabs">
                <li class="active"><a data-toggle="tab" href="#tab-timer"> <i class="fa fa-clock-o"></i> {{ trans('translate.timer') }}</a></li>
                <li class=""><a data-toggle="tab" href="#tab-labels"><i class="fa fa-tag"></i> {{ trans('translate.labels') }}</a></li>
                <li class=""><a data-toggle="tab" href="#tab-frenzy"><i class="fa fa-link"></i> {{ trans('translate.frenzy') }}</a></li>
                <li class=""><a data-toggle="tab" href="#tab-styles"><i class="fa fa-image"></i> {{ trans('translate.style') }}</a></li>
            </ul>
            <div class="tab-content">
                <div id="tab-timer" class="tab-pane active">
                    <div class="panel-body">
                        <div class='form-horizontal'>

                            <!-- Timer Name -->
                            <div class="form-group">
                                <label for="name" class="col-sm-2 control-label">{{ trans('translate.timer') }}</label>
                                <div class="col-sm-9">
                                    {{ Form::text('name', old('timer'), ['id' => 'name', 'class' => 'form-control']) }}
                                </div>
                            </div>

                            <!-- Timer Type -->
                            <div class="form-group">
                                <label for="timer_type" class="col-sm-2 control-label">{{ trans('translate.timer_type') }}</label>
                                <div class="col-sm-9">
                                    {{ Form::select('timer_type', $timer_type, old('timer_type'), ['class' => 'form-control', 'id' => 'timer_type']) }}
                                </div>
                            </div>
                            <?php
                                $timer_type = 'deadline';

                                if(isset($timer->timer_type)){
                                    $timer_type = $timer->timer_type;
                                } else {
                                    if(old('timer_type')){
                                        $timer_type = old('timer_type');
                                    }
                                }
                            ?>

                            <!-- Evergreen Timer Fields -->
                            <div id="evergreen_date" style="{{ ($timer_type == 'evergreen') ? 'display:block;': 'display:none;'}}">
                                <div class="form-group">
                                    <label class="col-sm-2 control-label">{{ trans('translate.expiry') }}</label>
                                    <div class="col-sm-1">  <input type="number" class="form-control" value="{{ (isset($timer->offset_days)) ? intval($timer->offset_days) : old('offset_days') }}" name="offset_days" min="0" /><p class="help-block">{{ trans('translate.days') }}</p> </div>
                                    <div class="col-sm-1">  <input type="number" class="form-control" value="{{ (isset($timer->offset_hours)) ? intval($timer->offset_hours) : old('offset_hours') }}" name="offset_hours" min="0" max="24" /><p class="help-block">{{ trans('translate.hours') }}</p> </div>
                                    <div class="col-sm-1">  <input type="number" class="form-control" value="{{ (isset($timer->offset_minutes)) ? intval($timer->offset_minutes) : old('offset_minutes') }}" name="offset_minutes" min="0" max="60" /><p class="help-block">{{ trans('translate.minutes') }}</p> </div>
                                    <div class="col-sm-1">  <input type="number" class="form-control" value="{{ (isset($timer->offset_seconds)) ? $timer->offset_seconds : old('offset_seconds') }}" name="offset_seconds" min="0" max="60" /><p class="help-block">{{ trans('translate.seconds') }}</p> </div>
                                </div>
                            </div>

                            <!-- Deadline -->
                            <div id="deadline_date" style="{{ ($timer_type == 'evergreen') ? 'display:none;': 'display:block;'}}">
                                <div class="form-group">
                                    <label for="deadline" class="col-sm-2 control-label">{{ trans('translate.deadline') }}</label>
                                    <div class="col-sm-9">
                                            <div class='input-group'>
                                                <span class="input-group-addon">
                                                    <span class="glyphicon glyphicon-calendar"></span>
                                                </span>
                                                @if (isset($timer->id))
                                                    {{ Form::text('deadline', old('deadline'), ['id' => 'deadline', 'class' => 'form-control']) }}
                                                @else
                                                    {{ Form::text('deadline', old('deadline', \Carbon\Carbon::now()->addDays(7)), ['id' => 'deadline', 'class' => 'form-control']) }}
                                                @endif
                                            </div>
                                    </div>
                                    <script type="text/javascript">
                                        $(function() {
                                            $('#deadline').datetimepicker({
                                                format: "YYYY-MM-DD HH:mm:00",
                                                sideBySide: true,
                                                @if (isset($timer->id))
                                                    defaultDate: "{{ $timer->deadline }}",
                                                @else
                                                    defaultDate: "{{ \Carbon\Carbon::now()->addDays(7) }}",
                                                @endif
                                            });
                                        })
                                    </script>
                                </div>

                                <!-- Timezone -->
                                <script src="/js/plugins/bootstrap-formhelpers.min.js"></script>
                                <div class="form-group">
                                    <label for="timezone" class="col-sm-2 control-label">{{ trans('translate.timezone') }}</label>
                                    <div class="col-sm-4">
                                        <p>
                                            <select id="country" class="form-control bfh-countries" data-blank="false" data-country="{{ isset($country) ? $country : '' }}"></select>
                                        </p>
                                        <select name='timezone' class="form-control bfh-timezones" data-country="country" data-blank="false" data-timezone="{{ $timezone }}"></select>
                                    </div>
                                </div>
                            </div>


                            <!-- Active Link -->
                            <div class="form-group">
                                <label for="active_link" class="col-sm-2 control-label">{{ trans('translate.active_link') }}</label>
                                <div class="col-sm-9">
                                    {{ Form::text('active_link', old('active_link'), ['id' => 'active_link', 'class' => 'form-control', 'autocomplete' => 'off']) }}
                                </div>
                            </div>
                            <script>
                                $(document).ready(function () {
                                    $("#add-timer").click(function () {
                                        var active_link = $("#active_link").val();
                                        if (!active_link.match(/^http([s]?):\/\/.*/)) {
                                            $("#active_link").val('http://' + active_link);
                                        }
                                        var expired_link = $("#expired_link").val();
                                        if (!expired_link.match(/^http([s]?):\/\/.*/)) {
                                            $("#expired_link").val('http://' + expired_link);
                                        }

                                    });
                                });
                            </script>
                            <!-- Expired Link -->
                            <div class="form-group">
                                <label for="expired_link" class="col-sm-2 control-label">{{ trans('translate.expired_link') }}</label>
                                <div class="col-sm-9">
                                    {{ Form::text('expired_link', old('expired_link'), ['id' => 'expired_link', 'class' => 'form-control', 'autocomplete' => 'off']) }}
                                </div>
                            </div>
                        </div><!-- /.form-horizontal -->

                    </div>
                </div><!-- /#tab-timer -->

                <div id="tab-labels" class="tab-pane">
                    <div class="panel-body">
                        <div class='form-horizontal'>

                            <!-- Days -->
                            <div class="form-group">
                                <label for="label_days" class="col-sm-2 control-label">{{ trans('translate.days') }}</label>
                                <div class="col-sm-9">
                                    @if (isset($timer->id))
                                        {{ Form::text('label_days', old('label_days'), ['id' => 'label_days', 'class' => 'form-control']) }}
                                    @else
                                        {{ Form::text('label_days', 'days', ['id' => 'label_days', 'class' => 'form-control']) }}
                                    @endif
                                </div>
                            </div>

                            <!-- Hours -->
                            <div class="form-group">
                                <label for="label_hours" class="col-sm-2 control-label">{{ trans('translate.hours') }}</label>
                                <div class="col-sm-9">
                                    @if (isset($timer->id))
                                        {{ Form::text('label_hours', old('label_hours'), ['id' => 'label_hours', 'class' => 'form-control']) }}
                                    @else
                                        {{ Form::text('label_hours', 'hours', ['id' => 'label_hours', 'class' => 'form-control']) }}
                                    @endif
                                </div>
                            </div>

                            <!-- Minutes -->
                            <div class="form-group">
                                <label for="label_minutes" class="col-sm-2 control-label">{{ trans('translate.minutes') }}</label>
                                <div class="col-sm-9">
                                    @if (isset($timer->id))
                                        {{ Form::text('label_minutes', old('label_minutes'), ['id' => 'label_minutes', 'class' => 'form-control']) }}
                                    @else
                                        {{ Form::text('label_minutes', 'minutes', ['id' => 'label_minutes', 'class' => 'form-control']) }}
                                    @endif
                                </div>
                            </div>

                            <!-- Seconds -->
                            <div class="form-group">
                                <label for="label_seconds" class="col-sm-2 control-label">{{ trans('translate.seconds') }}</label>
                                <div class="col-sm-9">
                                    @if (isset($timer->id))
                                        {{ Form::text('label_seconds', old('label_seconds'), ['id' => 'label_seconds', 'class' => 'form-control']) }}
                                    @else
                                        {{ Form::text('label_seconds', 'seconds', ['id' => 'label_seconds', 'class' => 'form-control']) }}
                                    @endif
                                </div>
                            </div>

                        </div><!-- /.form-horizontal -->

                    </div>
                </div><!-- /#tab-labels -->

                <?php
                    $frenzy = [];
                    if(!empty($timer->frenzy)){
                        $frenzy = json_decode($timer->frenzy, true);
                    } else if(old('offset_value')){
                        $offset_value = array_values(array_filter(old('offset_value')));
                        $offset_field = array_values(array_filter(old('offset_field')));
                        $redirect_link = array_values(array_filter(old('redirect_link')));
                        $warning_message = array_values(array_filter(old('warning_message')));
                        if(!empty($offset_value)){
                            foreach($offset_value as $key => $offset){
                                if(!empty($offset_field[$key]) && !empty($redirect_link[$key])){
                                    $frenzy = array_merge($frenzy, array([
                                        'offset_value' => $offset,
                                        'offset_field' => $offset_field[$key],
                                        'redirect_link' => $redirect_link[$key],
                                        'warning_message' => isset($warning_message[$key]) ? trim($warning_message[$key]) : ''
                                    ]));
                                }
                            }
                        }
                    }
                    //echo "<pre>"; print_r($frenzy);
                ?>
                <div id="tab-frenzy" class="tab-pane">
                    <div class="panel-body">
                        <div class='form-horizontal'>
                            <div class="form-group">
                                <button type="button" class="btn btn-success pull-right addFrenzy"><i class="fa fa-btn fa-plus"></i> {{ trans('translate.add_frenzy') }}</button>
                            </div>

                            <div id="frenzy_box">
                                <div class="table-responsive">
                                    <table class="table table-striped" id="frenzy_box_table">
                                        <tbody>
                                        @foreach($frenzy as $frenzy_value)
                                            <?php
                                                $offset_field = isset($frenzy_value['offset_field']) ? $frenzy_value['offset_field'] : 'days';
                                            ?>
                                            <!-- start row -->
                                            <tr><td><div class="form-horizontal">

                                                <!-- offset field -->
                                                <div class="form-group"> <!-- .form-group -->
                                                    <label for="name" class="col-md-2 control-label">{{ trans('translate.with_less_than') }}</label> <!-- label -->
                                                    <div class="col-md-1"><input type="number" class="input-sm form-control" value="{{ isset($frenzy_value['offset_value']) ? intval($frenzy_value['offset_value']) : 1 }}" name="offset_value[]" min="1" /></div> <!-- number -->
                                                    <div class="col-md-1"> <!-- unit of measure -->
                                                        <select class="input-sm" name="offset_field[]">
                                                            <option value="days" {{ ($offset_field == 'days') ? 'selected' : '' }}>{{ trans('translate.days') }}</option>
                                                            <option value="hours" {{ ($offset_field == 'hours') ? 'selected' : '' }}>{{ trans('translate.hours') }}</option>
                                                            <option value="minutes" {{ ($offset_field == 'minutes') ? 'selected' : '' }}>{{ trans('translate.minutes') }}</option>
                                                            <option value="seconds" {{ ($offset_field == 'seconds') ? 'selected' : '' }}>{{ trans('translate.seconds') }}</option>
                                                        </select>
                                                    </div> <!-- end unit of measure -->
                                                    <div class="col-md-2"><p class="help-block">{{ trans('translate.before_deadline') }}</p></div> <!-- help -->
                                                </div> <!-- /.form-group -->

                                                <!-- set link to: -->
                                                <div class="form-group"> <!-- .form-group -->
                                                    <label for="name" class="col-md-2 control-label">{{ trans('translate.change_active_link') }}</label> <!-- label -->
                                                    <div class="col-md-3"><input type="text" class="form-control" autocomplete="off" value="{{ isset($frenzy_value['redirect_link']) ? $frenzy_value['redirect_link'] : '' }}" name="redirect_link[]" /></div> <!-- link -->
                                                </div> <!-- /.form-group -->

                                                <!-- set warning to: -->
                                                <div class="form-group"> <!-- .form-group -->
                                                    <label for="name" class="col-md-2 control-label">{{ trans('translate.show_warning') }}</label> <!-- label -->
                                                    <div class="col-md-3"><input type="text" class="form-control" name="warning_message[]" value="{{ isset($frenzy_value['warning_message']) ? $frenzy_value['warning_message'] : '' }}" maxlength="40" /></div>
                                                </div> <!-- /.form-group -->

                                                <!-- delete -->
                                                <div class="col-md-offset-2 col-md-3"><button type='button' class="btn btn-danger removeFrenzy"><i class="fa fa-btn fa-trash"></i> {{ trans('translate.remove_frenzy') }}</button></div>

                                            <!-- end row -->
                                            </div></td></tr>

                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div><!-- /.form-horizontal -->

                    </div>
                </div><!-- /#tab-frenzy -->
                <?php
                    $expired_image = '';
                    if(!empty($timer->expired_image)){
                        $expired_image = $timer->expired_image;
                    }
                ?>
                <script>
                    $(function() {
                        // grab styles from PHP
                        var styles = JSON.parse('{!! json_encode($timer_styles) !!}');
                        // change a style setting? then it's custom
                        $('.style-setting').on('change keyup keydown', function() {
                            $('#style').val('custom');
                        });
                        // choose a new style? then update style settings
                        $('#style').on('change', function() {
                            if ('custom' != $(this).val()) {
                                $('#style-font').val( styles[$(this).val()].font );
                                $('#style-background').val( styles[$(this).val()].background );
                                $('#style-foreground').val( styles[$(this).val()].foreground );
                            }
                            preview();
                        });
                    });
                </script>

                <!--
                    Styles
                -->
                <!-- colorpicker -->
                <script src="/js/plugins/colorpicker/bootstrap-colorpicker.min.js"></script>
                <link rel="stylesheet" href="/css/plugins/colorpicker/bootstrap-colorpicker.min.css" property="stylesheet">
                <script>
                    $(function () {
                        $('#style-background').colorpicker().on('changeColor.colorpicker', function() {
                            $('#style').val('custom');
                            preview();
                        });
                        $('#style-foreground').colorpicker().on('changeColor.colorpicker', function() {
                            $('#style').val('custom');
                            preview();
                        });
                    })
                </script>
                <!-- tab -->
                <div id="tab-styles" class="tab-pane">
                    <div class="panel-body">
                        <div class='row'>
                            <div class='col-md-7'>
                                <div class="form-horizontal">
                                    <p class="text-center"><span class="label label-primary">{{ trans('translate.active') }}</span></p>
                                    <!-- Style -->
                                    <div class="form-group">
                                        <label for="style" class="col-sm-3 control-label">{{ trans('translate.style') }}</label>
                                        <div class="col-sm-8">
                                            {{ Form::select('styles', $styles, old('styles'), ['class' => 'form-control', 'id' => 'style']) }}
                                        </div>
                                    </div>
                                    <!-- Background -->
                                    <div class="form-group">
                                        <label for="style-background" class="col-sm-3 control-label">{{ trans('translate.background_color') }}</label>
                                        <div class="col-sm-8">
                                            <?php
                                                $background = isset($style->background) ? $style->background : '#000000';
                                            ?>
                                            <input id='style-background' class="form-control style-setting" name="background" value="{{ isset($style->background) ? $style->background : '#000000' }}">
                                        </div>
                                    </div>
                                    <!-- Foreground -->
                                    <div class="form-group">
                                        <label for="style-foreground" class="col-sm-3 control-label">{{ trans('translate.font_color') }}</label>
                                        <div class="col-sm-8">
                                            <?php
                                                $foreground = isset($style->foreground) ? $style->foreground : '#FFFFFF';
                                            ?>
                                            <input id='style-foreground' class="form-control style-setting" name="foreground" value="{{ isset($style->foreground) ? $style->foreground : '#FFFFFF' }}">
                                        </div>
                                    </div>
                                    <!-- Font -->
                                    <div class="form-group">
                                        <label for="style-font" class="col-sm-3 control-label">{{ trans('translate.font') }}</label>
                                        <div class="col-sm-8">
                                            <select id='style-font' class="form-control style-setting" name="font">
                                                @foreach ($fonts as $font)
                                                    <option value="{{ $font }}" {{ ($style->font == $font) ? 'selected' : '' }}>{{ $font }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class='form-group'>
                                        <label class="col-sm-3 control-label">{{ trans('translate.preview') }}</label>
                                        <div class='col-sm-8'>
                                            <div id='preview'></div>
                                            <script>
                                                function preview() {
                                                    var bg = escape($('#style-background').val());
                                                    var fg = escape($('#style-foreground').val());
                                                    var fn = escape($('#style-font').val());
                                                    var ld = escape($('#label_days').val());
                                                    var lh = escape($('#label_hours').val());
                                                    var lm = escape($('#label_minutes').val());
                                                    var ls = escape($('#label_seconds').val());
                                                    var img = $('<img/>', {
                                                        'id': 'img-preview',
                                                        'class': 'img-responsive',
                                                        'src': '/preview?bg='+bg+'&fg='+fg+'&fn='+fn+'&ld='+ld+'&lh='+lh+'&lm='+lm+'&ls='+ls,
                                                    });
                                                    $('#preview').html(img);
                                                }
                                                // on form load
                                                preview();
                                                // any time something changes
                                                $('#style, #style-background, #style-foreground, #style-font, #label_days, #label_hours, #label_minutes, #label_seconds').on('change keyup', function() {
                                                    preview();
                                                });
                                            </script>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-offset-1 col-md-3">
                                <div class="form-horizontal">
                                    <p class="text-center"><span class="label label-danger">{{ trans('translate.expired') }}</span></p>
                                    <div class="form-group">
                                        <div class="checkbox">
                                            <label><input type="checkbox" value="1" name="upload_custom_image" id="upload_custom_image" {{ (isset($timer->upload_custom_image) && $timer->upload_custom_image == '1')  ? 'checked' : '' }}>{{ trans('translate.upload_custom_image') }}</label>
                                        </div>
                                    </div>
                                    <div class="form-group" id="upload_custom_image_box" style="{{ (isset($timer->upload_custom_image) && $timer->upload_custom_image == '1')  ? 'display:block;' : 'display:none;' }}">
                                        <label for="custom_expired_image">{{ trans('translate.upload') }}</label>
                                        <input type="file" id="custom_expired_image" name="custom_expired_image" accept="image/*" />
                                        <p class="help-block">*{{ trans('translate.max_file_size') }}</p>

                                        @if(!empty($timer->expired_image) && file_exists(public_path()."/custom_expired_image/".$timer->expired_image))
                                        <img src="{{ asset('custom_expired_image/'.$timer->expired_image)}}" alt="Expired Image" class="img-responsive" />
                                        @endif
                                    </div>
                                    <?php
                                        $show_system_image = true;
                                        if(isset($timer->upload_custom_image) && $timer->upload_custom_image == '1'){
                                           $show_system_image = false;
                                        }
                                    ?>
                                    <div class="form-group" id="system_image_box" style="{{ ($show_system_image)  ? 'display:block;' : 'display:none;' }}">
                                        <label class="control-label">{{ trans('translate.select_expired_image') }}</label>
                                        @foreach ($expired as $this_expired)
                                            <div class="radio">
                                                <label>
                                                    <input type="radio" class='expired-image' name="expired_image" value="{{ $this_expired }}" {{ (!empty($expired_image) && $expired_image == $this_expired)  ? 'checked' : '' }}>
                                                    <img src="{{ asset('expired/'.$locale.'/'.$this_expired) }}" class="img-responsive" alt="Expired Image" />
                                                </label>
                                                <script>
                                                    if ($('.expired-image:checked').length == 0) {
                                                        $('.expired-image').first().attr('checked', 'checked');
                                                    }
                                                </script>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

