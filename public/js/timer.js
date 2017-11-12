$(document).ready(function() {
    $('#timer_type').on('change', function() {
        if ($(this).val() == 'evergreen') {
            $('#evergreen_date').show();
            $('#deadline_date').hide();
        } else {
            $('#evergreen_date').hide();
            $('#deadline_date').show();
        }
    });

   $('#upload_custom_image').click(function (){
       if($(this).is(':checked')){
            $('#upload_custom_image_box').show();
            $('#system_image_box').hide();
       } else {
           $('#upload_custom_image_box').hide();
            $('#system_image_box').show();
       }
   });

    $('.addFrenzy').click(function() {
        var str = '';
        var frenzy_count = parseInt($('#frenzy_count').val());
        if(frenzy_count >= parseInt(frenzy_limit_count)){
            alert('Sorry you’ve reached your max Frenzy.');
            return false;
        }

        // start row
        str += '<tr><td><div class="form-horizontal">';

        // offset field
        str += '<div class="form-group">'; // .form-group
        str += '<label for="name" class="col-md-2 control-label">'+translate_with_less_than+'</label>'; // label
        str += '<div class="col-md-1"><input type="number" class="input-sm form-control" value="1" name="offset_value[]" min="1" /></div>'; // number
        str += '<div class="col-md-1">'; // unit of measure
        str += '<select class="input-sm" name="offset_field[]">';
        str += '<option value="days">'+translate_days+'</option>';
        str += '<option value="hours">'+translate_hours+'</option>';
        str += '<option value="minutes">'+translate_minutes+'</option>';
        str += '<option value="seconds">'+translate_seconds+'</option></select>';
        str += '</div>'; // end unit of measure
        str += '<div class="col-md-2"><p class="help-block">'+translate_before_deadline+'</p></div>'; // help
        str += '</div>'; // /.form-group

        // set link to:
        str += '<div class="form-group">'; // .form-group
        str += '<label for="name" class="col-md-2 control-label">'+translate_change_active_link+'</label>'; // label
        str += '<div class="col-md-3"><input type="text" class="form-control" autocomplete="off" value="" name="redirect_link[]" /></div>'; // link
        str += '</div>'; // /.form-group

        // set warning to:
        str += '<div class="form-group">'; // .form-group
        str += '<label for="name" class="col-md-2 control-label">'+translate_show_warning+'</label>'; // label
        str += '<div class="col-md-3"><input type="text" class="form-control" name="warning_message[]" value="" maxlength="40" /></div>';
        str += '</div>'; // /.form-group

        // delete
        str += '<div class="col-md-offset-2 col-md-3"><button type="button" class="btn btn-danger removeFrenzy"><i class="fa fa-btn fa-trash"></i> '+translate_remove_frenzy+'</button></div>';

        // end row
        str += '</div></td></tr>';

        $('#frenzy_count').val(frenzy_count+1);
        $("#frenzy_box_table tbody").append(str);

    });

    $(".removeFrenzy").on("click", function(event) {
        $(this).parent().parent().parent().remove();
        var frenzy_count = parseInt($('#frenzy_count').val());
        $('#frenzy_count').val(frenzy_count-1);
    });
    $("#active_link").on('keyup change', function (){
        var active_link = $(this).val().trim().toLowerCase();
        var expired_link = $("#expired_link").val().trim().toLowerCase();
        if(active_link != '' && expired_link != ''){
            active_link = active_link.replace(/^(https?):\/\//, '').replace('www.', '').replace(/\/$/g, '');
            expired_link = expired_link.replace(/^(https?):\/\//, '').replace('www.', '').replace(/\/$/g, '');
            if(active_link == expired_link){
                $("#warning_url_mismatch").html(active_link_msg).show();
            } else {
                $("#warning_url_mismatch").html('').hide();
            }
        } else {
            $("#warning_url_mismatch").html('').hide();
        }
    });

    $("#expired_link").on('keyup change', function (){
        var active_link = $('#active_link').val().trim().toLowerCase();
        var expired_link = $(this).val().trim().toLowerCase();
        if(active_link != '' && expired_link != ''){
            active_link = active_link.replace(/^(https?):\/\//, '').replace('www.', '').replace(/\/$/g, '');
            expired_link = expired_link.replace(/^(https?):\/\//, '').replace('www.', '').replace(/\/$/g, '');
            if(active_link == expired_link){
                $("#warning_url_mismatch").html(active_link_msg).show();
            } else {
                $("#warning_url_mismatch").html('').hide();
            }
        } else{
            $("#warning_url_mismatch").html('').hide();
        }
    });

    $("#frenzy_box_table").on("keyup change", "input[name='redirect_link[]']",function(event) {
        var redirect_link = $(this).val();
        var active_link = $("#active_link").val().trim().toLowerCase();
        var expired_link = $("#expired_link").val().trim().toLowerCase();

        if(redirect_link != '' && active_link != '' && expired_link != ''){
            active_link = active_link.replace(/^(https?):\/\//, '').replace('www.', '').replace(/\/$/g, '');
            expired_link = expired_link.replace(/^(https?):\/\//, '').replace('www.', '').replace(/\/$/g, '');
            redirect_link = redirect_link.replace(/^(https?):\/\//, '').replace('www.', '').replace(/\/$/g, '');
            if(redirect_link == active_link){
                $("#warning_url_mismatch").html(redirect_active_link_msg).show();
            } else if(redirect_link == expired_link){
                $("#warning_url_mismatch").html(redirect_expired_link_msg).show();
            } else {
                $("#warning_url_mismatch").html('').hide();
            }
        } else {
            $("#warning_url_mismatch").html('').hide();
        }
    });
});