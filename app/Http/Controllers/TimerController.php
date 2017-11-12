<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Timer;
use App\TimerDeadline;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Repositories\TimerRepository;
use Session;
use Carbon\Carbon;
use Intervention\Image\Facades\Image;
use File;
use Response;

class TimerController extends Controller
{
    /**
     * The timer repository instance.
     *
     * @var TimerRepository
     */
    protected $timers;

    /**
     * Create a new controller instance.
     *
     * @param  TimerRepository  $timers
     * @return void
     */
    public function __construct(TimerRepository $timers)
    {
        $this->timers = $timers;
        parent::__construct();
    }

    /**
     * Display a list of all of the user's timers.
     *
     * @param  Request  $request
     * @return Response
     */
    public function index(Request $request)
    {
        return view('timers.index', [
            'timers' => $this->timers->forUser($request->user()),
        ]);
    }

    /**
     * Form to configure (and get) embed code.
     *
     * @param  Request  $request
     * @return Response
     */
    public function embed(Request $request, Timer $timer)
    {
        return view('timers.embed', [
            'timer' => $timer,
        ]);
    }

    /**
     * Create a timer.
     *
     * @param  Request  $request
     * @return Response
     */
    public function create(Request $request)
    {
        // fonts
        $fonts = collect(File::files(base_path().'/resources/assets/fonts'))->transform(function ($item, $key) {
            return str_replace(['.ttf', '-'], ['', ' '], basename($item));
        })->toArray();
        // styles
        $styles = [];
        $timer_styles = config('app.styles');
        foreach ($timer_styles as $name => $style) {
            if(!empty(trans('translate.'.$name))){
                $styles[$name] = ucwords(trans('translate.'.$name));
            } else {
                $styles[$name] = ucwords($name);
            }
        }
        $styles['custom'] = trans('translate.custom');
        // expired images
        $user = \Auth::user();
        $expired = collect(File::files(public_path().'/expired/'.$user->locale.'/'))->transform(function ($item, $key) {
            if (false !== strpos($item, '.gif')) {
                return basename($item);
            }
        })->reject(function($item) {
            return empty($item);
        })->toArray();
        // active style
        $style = (object) config('app.styles.default');
        // timer type
        $timer_type = [
            'deadline' => 'Date',
            'evergreen' => 'Evergreen'
        ];
        // timezone country
        if ('en' == $user->locale) {
            $tz_default = 'America/New_York';
        } else {
            $tz_default = 'Europe/Paris';
        }
        $timezone = (old('timezone')) ? old('timezone') : $tz_default;
        $datetimezone = new \DateTimeZone($timezone);
        $location = timezone_location_get($datetimezone);
        // return view
        return view('timers.create', [
            'timer_type' => $timer_type,
            'styles' => $styles,
            'style' => $style,
            'fonts' => $fonts,
            'locale' => $user->locale,
            'expired' => $expired,
            'timer_styles' => $timer_styles,
            'country' => $location['country_code'],
            'timezone' => $timezone,
        ]);
    }

    /**
     * Update a timer.
     *
     * @param  Request  $request
     * @return Response
     */
    public function update(Request $request, Timer $timer)
    {
        // fonts
        $fonts = collect(File::files(base_path().'/resources/assets/fonts'))->transform(function ($item, $key) {
            return str_replace(['.ttf', '-'], ['', ' '], basename($item));
        })->toArray();
        // timezone country
        $timezone = new \DateTimeZone($timer->timezone);
        $location = timezone_location_get($timezone);
        // styles array
        $styles = [];
        $timer_styles = config('app.styles');
        foreach ($timer_styles as $name => $style) {
            $styles[$name] = ucwords($name);
        }
        $styles['custom'] = 'Custom';
        $user = \Auth::user();
        // expired images
        $expired = collect(File::files(public_path().'/expired/'.$user->locale.'/'))->transform(function ($item, $key) {
            if (false !== strpos($item, '.gif')) {
                return basename($item);
            }
        })->reject(function($item) {
            return empty($item);
        })->toArray();
        // active style
        $style = json_decode($timer->styles);
        if (!is_object($style)) {
            $style = (object) config('app.styles.'.$timer->styles);
        } else {
            $timer->styles = 'custom';
        }
        // timer types
        $timer_type = [
            'deadline' => 'Date',
            'evergreen' => 'Evergreen'
        ];
        // return view
        $input =  $request->input();
        return view('timers.update', [
            'timer' => $timer,
            'timer_type' => $timer_type,
            'styles' => $styles,
            'style' => $style,
            'fonts' => $fonts,
            'locale' => $user->locale,
            'expired' => $expired,
            'timer_styles' => $timer_styles,
            'country' => $location['country_code'],
            'timezone' => $timer->timezone,
        ]);
    }

    /**
     * Create a new timer.
     *
     * @param  Request  $request
     * @return Response
     */
    public function store(Request $request, Timer $timer)
    {
        // validation
        $input = $request->input();

        if($input['timer_type'] == 'deadline'){
            $rules = [
                'name' => 'required|max:255',
                'deadline' => 'required|date_format:Y-m-d H:i:s',
                'active_link' => 'required|max:255|url',
                'expired_link' => 'required|max:255|url',
                'timezone' => 'required',
            ];
        } else {
            $rules = [
                'name' => 'required|max:255',
                'offset_days' => 'required',
                'offset_hours' => 'required',
                'offset_minutes' => 'required',
                'offset_seconds' => 'required',
                'active_link' => 'required|max:255|url',
                'expired_link' => 'required|max:255|url',
            ];
        }

        // custom expired image validation
        if ($request->upload_custom_image == 1) {
            // Post Method indicates that New Timer is created
            if (!$request->hasFile('custom_expired_image') && $request->method() == 'POST') {
                return redirect()->back()->with('error', trans('translate.image_upload_msg'))->withInput();
            }

            // Patch Method indicates that Timer is edited
            if ($request->method() == 'PATCH') {
                if($timer->upload_custom_image == 0 && $request->upload_custom_image == 1){
                    if(!$request->hasFile('custom_expired_image')){
                        return redirect()->back()->with('error', trans('translate.image_upload_msg'))->withInput();
                    }
                }
            }
        } else {
            // require them to select an expired image
            $rules['expired_image'] = 'required';
        }
        $this->validate($request, $rules);

        // frenzies
        $frenzy = [];
        if (isset($input['offset_value'])) {
            $offset_value = array_values(array_filter($input['offset_value']));
            $offset_field = array_values(array_filter($input['offset_field']));
            $redirect_link = array_values(array_filter($input['redirect_link']));
            $warning_message = array_values(array_filter($input['warning_message']));
            if (!empty($offset_value)) {
                foreach ($offset_value as $key => $offset){
                    if (!empty($offset_field[$key])) {
                        $frenzy = array_merge($frenzy, array([
                            'offset_value' => $offset,
                            'offset_field' => $offset_field[$key],
                            'redirect_link' => isset($redirect_link[$key]) ? trim($redirect_link[$key]) : '',
                            'warning_message' => isset($warning_message[$key]) ? trim($warning_message[$key]) : '',
                        ]));
                    }
                }
            }
        }

        $destinationPath = public_path().'/custom_expired_image/';

        if($request->hasFile('custom_expired_image') && $request->upload_custom_image == 1){
            $extension = $request->file('custom_expired_image')->getClientOriginalExtension();
            if(!in_array($extension, ['gif', 'png', 'bmp', 'jpg', 'jpeg'])){
                return redirect()->back()->with('error', trans('translate.invalid_file_msg'))->withInput();
            }
            $imgsize = $request->file('custom_expired_image')->getSize();
            $size = round($imgsize/1000000);
            if($size > 1){
                return redirect()->back()->with('error', trans('translate.file_size_msg'))->withInput();
            }

            $expired_image = $fileName = auth()->user()->id. '_'.  uniqid() .'.'.$extension;
            $path = $destinationPath.$fileName;
            Image::make($request->file('custom_expired_image')->getRealPath())->resize(800, 400, function ($constraint){
                $constraint->aspectRatio();
                $constraint->upsize();
            })->save($path);
        } else {
            $expired_image = $request->expired_image;
        }

        // Check if User has reuploaded the expired image
        if(!empty($expired_image)){
            if (isset($timer->id)) {
                $image = $timer->expired_image;
                if(!empty($image) && file_exists($destinationPath.$image)){
                   unlink($destinationPath.$image);
                }
            }
        }

        if (isset($timer->id)) {
            // update
            $update_data = [
                'name' => $request->name,
                'timer_type' => $request->timer_type,
                'offset_days' => $request->offset_days,
                'offset_hours' => $request->offset_hours,
                'offset_minutes' => $request->offset_minutes,
                'offset_seconds' => $request->offset_seconds,
                'deadline' => $request->deadline,
                'timezone' => isset($request->timezone) ? $request->timezone : 'EST',
                'active_link' => $request->active_link,
                'expired_link' => $request->expired_link,
                'label_days' => $request->label_days,
                'label_hours' => $request->label_hours,
                'label_minutes' => $request->label_minutes,
                'label_seconds' => $request->label_seconds,
                'frenzy' => json_encode($frenzy),
                'styles' => ($input['styles'] == 'custom') ? json_encode(['background' => $input['background'], 'foreground' => $input['foreground'], 'font' => $input['font'], 'format' => 'dhms']) : $request->styles,
                'upload_custom_image' => isset($request->upload_custom_image) ? '1' : '0',
            ];
            if(!empty($expired_image)){
                $update_data['expired_image'] = $expired_image;
            }
            // save
            $request->user()->timers()->where('id', '=', $timer->id)->update($update_data);
            Session::flash('flash_message', trans('translate.timer_saved'));

            // generate new style
            if ($request->styles == 'custom') {
                TimerController::generateStyle('custom', Timer::where('id', $timer->id)->first());
            }
        } else {
            // create
            $newtimer = $request->user()->timers()->create([
                'name' => $request->name,
                'timer_type' => $request->timer_type,
                'offset_days' => $request->offset_days,
                'offset_hours' => $request->offset_hours,
                'offset_minutes' => $request->offset_minutes,
                'offset_seconds' => $request->offset_seconds,
                'deadline' => $request->deadline,
                'timezone' => isset($request->timezone) ? $request->timezone : 'EST',
                'active_link' => $request->active_link,
                'expired_link' => $request->expired_link,
                'label_days' => $request->label_days,
                'label_hours' => $request->label_hours,
                'label_minutes' => $request->label_minutes,
                'label_seconds' => $request->label_seconds,
                'frenzy' => json_encode($frenzy),
                'styles' => ($input['styles'] == 'custom') ? json_encode(['background' => $input['background'], 'foreground' => $input['foreground'], 'font' => $input['font'], 'format' => 'dhms']) : $request->styles,
                'upload_custom_image' => isset($request->upload_custom_image) ? '1' : '0',
                'expired_image' => $expired_image,
            ]);
            Session::flash('flash_message', trans('translate.timer_added'));

            // generate new style
            if ($request->styles == 'custom') {
                TimerController::generateStyle('custom', $newtimer);
            }
        }

        // go back to list
        return redirect('/timers');
    }

    /**
     * Destroy the given timer.
     *
     * @param  Request  $request
     * @param  Timer  $timer
     * @return Response
     */
    public function destroy(Request $request, Timer $timer)
    {
        // $this->authorize('destroy', $timer);
        $user =  \Auth::user();
        $count = $user->timers()->where('id', $timer->id)->count();
        if($count > 0){
            // delete
            $timer->delete();
        }

        return redirect('/timers');
    }

    /**
     * Return animated GIF countdown timer. (Super-efficient.)
     *
     * @param Request $request
     * @param Timer $timer
     * @return Response
     */
    public function image(Request $request, Timer $timer)
    {
        ignore_user_abort();
        set_time_limit(0);

        $email = strtolower(trim($request->get('email')));
        // used a few times
        $now = Carbon::now($timer->timezone);

        // deadline
        if($timer->timer_type == 'deadline'){
            $deadline = new Carbon($timer->deadline, $timer->timezone);
        } else {
            $deadline = Carbon::now($timer->timezone)->addDays($timer->offset_days)->addHours($timer->offset_hours)->addMinutes($timer->offset_minutes)->addSeconds($timer->add_seconds);
            if(!empty($email)){
                $timer_deadline = TimerDeadline::where('timer_id', '=', $timer->id)->where('email', '=', $email)->first();
                if(!$timer_deadline){
                    TimerDeadline::create([
                        'timer_id' => $timer->id,
                        'email' => $email,
                        'deadline' => $deadline
                    ]);
                } else {
                    $deadline = new Carbon($timer_deadline->deadline, $timer->timezone);
                }
            } else {
                $deadline_cookie = \Cookie::get('st_timer_deadline_'.$timer->id);
                if($deadline_cookie){
                    $deadline = unserialize($deadline_cookie);
                }
            }
        }

        // Generate Unique Id
        $uniqid = uniqid();
        $tmp_files = [];
        // style settings
        $style = json_decode($timer->styles);
        if (!is_object($style)) {
            $style = (object) config('app.styles.'.$timer->styles);
            $stylename = $timer->styles;
            $style_prefix = config('app.style_directory').'/'.$style->name.'/'.$style->name;
        } else {
            $stylename = 'custom';
            $style_prefix = config('app.style_directory').'/custom/user-'.$timer->user_id.'/'.$timer->id.'/'.$timer->id;
        }

        // full path to font
        $font = config('app.font_directory').'/'.str_replace(['', ' '], ['.ttf', '-'], $style->font).'.ttf';

        /**
         * Do we need space for a Frenzy label?
         */
        $frenzies = json_decode($timer->frenzy, true);
        $has_warning = false;
        foreach ($frenzies as $frenzy) {
            if (trim($frenzy['warning_message']) != '') {
                $has_warning = true;
                break;
            }
        }

        /**
         * Timer dimensions
         */

        // get all widths
        $widths = [
            'placeholder' => trim(`/usr/local/bin/gm identify -format "%w" $style_prefix-placeholder.gif`),
            'delimiter' => trim(`/usr/local/bin/gm identify -format "%w" $style_prefix-delimiter.gif`),
            'space' => trim(`/usr/local/bin/gm identify -format "%w" $style_prefix-space.gif`),
        ];
        // calculate width
        $width = ($widths['space'] * substr_count($style->format, '_')) + ($widths['placeholder'] * preg_match_all('/[dhms]/', $style->format)) + ($widths['delimiter'] * substr_count($style->format, ':'));
        // get height
        $placeholder_height = trim(`/usr/local/bin/gm identify -format "%h" $style_prefix-placeholder.gif`);
        // label height
        $label_height = $placeholder_height/3;
        // warning height
        $warning_height = ($has_warning) ? $placeholder_height / 2 : 0;
        // canvas height
        $height = $placeholder_height + $label_height + $warning_height;
        // label pointsize
        $label_pointsize = 72/3.4;
        // warning fontsize
        $warning_fontsize = 72 / 2.3;

        /**
        Calculate new dimensions based on ratio between real width and desired width.
        **/
        if (isset($_GET['w']) && intval($_GET['w']) > 0) {
            $ratio = intval($_GET['w']) / $width;
            $width = $ratio * $width;
            $placeholder_height = $ratio * $placeholder_height;
            $label_height = $ratio * $label_height;
            $warning_height = $ratio * $warning_height;
            $height = $ratio * $height;
            $label_pointsize = $ratio * $label_pointsize;
            $widths['space'] = $ratio * $widths['space'];
            $widths['placeholder'] = $ratio * $widths['placeholder'];
            $widths['delimiter'] = $ratio * $widths['delimiter'];
            $warning_fontsize = $ratio * $warning_fontsize;
        }

        /**
         * Draw timer
         */

        // draw canvas in bg color
        `/usr/local/bin/gm convert -size {$width}x{$height} xc:"{$style->background}" /tmp/{$uniqid}_canvas.gif`;
        $tmp_files[] = $uniqid.'_canvas.gif';

        /**
         * Make labels
         */

        // days
        echo `/usr/local/bin/gm convert -size {$widths['placeholder']}x$label_height xc:"{$style->background}" -fill "{$style->foreground}" -font $font -pointsize $label_pointsize -gravity Center -draw 'text 0,2 "{$timer->label_days}"' /tmp/{$uniqid}_days.gif`;
        // hours
        echo `/usr/local/bin/gm convert -size {$widths['placeholder']}x$label_height xc:"{$style->background}" -fill "{$style->foreground}" -font $font -pointsize $label_pointsize -gravity Center -draw 'text 0,2 "{$timer->label_hours}"' /tmp/{$uniqid}_hours.gif`;
        // minutes
        echo `/usr/local/bin/gm convert -size {$widths['placeholder']}x$label_height xc:"{$style->background}" -fill "{$style->foreground}" -font $font -pointsize $label_pointsize -gravity Center -draw 'text 0,2 "{$timer->label_minutes}"' /tmp/{$uniqid}_minutes.gif`;
        // seconds
        echo `/usr/local/bin/gm convert -size {$widths['placeholder']}x$label_height xc:"{$style->background}" -fill "{$style->foreground}" -font $font -pointsize $label_pointsize -gravity Center -draw 'text 0,2 "{$timer->label_seconds}"' /tmp/{$uniqid}_seconds.gif`;

        $tmp_files[] = $uniqid.'_days.gif';
        $tmp_files[] = $uniqid.'_hours.gif';
        $tmp_files[] = $uniqid.'_minutes.gif';
        $tmp_files[] = $uniqid.'_seconds.gif';

        // current difference
        $diff = $now->diff($deadline);

        // current
        $current = [
            'days' => sprintf('%02d', $diff->d),
            'hours' => sprintf('%02d', $diff->h),
            'minutes' => sprintf('%02d', $diff->i),
            'seconds' => sprintf('%02d', $diff->s),
        ];

        // copy canvas.gif to compose.gif
        copy('/tmp/'.$uniqid.'_canvas.gif', '/tmp/'.$uniqid.'_compose2.gif');
        $tmp_files[] = $uniqid.'_compose2.gif';

        /**
         * First Frame, if expired, show expired.
         */

        if ($now->diffInSeconds($deadline, false) < 0) {
            $destinationPath = ($timer->upload_custom_image) ? public_path().'/custom_expired_image/' : config('app.expired_directory').'/';
            $expired = $destinationPath.$timer->expired_image;
            // resize expired
            echo `/usr/local/bin/gm convert -size {$width}x{$height} $expired -resize {$width}x{$height} +profile "*" /tmp/{$uniqid}_expired.gif`;
            $tmp_files[] = $uniqid.'_expired.gif';
            // background color
            $identify = `/usr/local/bin/gm identify -verbose /tmp/{$uniqid}_expired.gif`;
            preg_match("/Background Color:(.*)/", $identify, $matches);
            $expired_background = isset($matches[1]) ? $matches[1] : '#ffffff';
            // "expired" canvas
            `/usr/local/bin/gm convert -size {$width}x{$height} xc:"{$expired_background}" /tmp/{$uniqid}_expired-canvas.gif`;
            $tmp_files[] = $uniqid.'_expired-canvas.gif';
            // overlay
            echo `/usr/local/bin/gm composite -gravity Center /tmp/{$uniqid}_expired.gif /tmp/{$uniqid}_expired-canvas.gif /tmp/{$uniqid}_compose2.gif`;
            // Create response and add encoded image data
            $response = \Response::make(file_get_contents('/tmp/'.$uniqid.'_compose2.gif'));
            $tmp_files[] = $uniqid.'_compose2.gif';
            if(!empty($tmp_files)){
                foreach($tmp_files as $file){
                    if(file_exists('/tmp/'.$file)){
                        @unlink('/tmp/'.$file);
                    }
                }
            }
            // Set content-type
            $response->header('Content-Type', 'image/gif');
            $response->header('Cache-Control', 'no-cache, max-age=0');

            // output
            return $response->withCookie(cookie()->forever('st_timer_deadline_'.$timer->id, serialize($deadline)));
        }

        // first frame, numbers
        $format = '';
        $timer_parts = str_split($style->format);
        foreach ($timer_parts as $index => $part) {
            // pixels from left of canvas
            $left = (int) ($widths['space'] * substr_count($format, '_')) + ($widths['placeholder'] * preg_match_all('/[dhms]/', $format)) + ($widths['delimiter'] * substr_count($format, ':'));

            // thing to draw next
            switch ($part) {
                case '_':
                    $character = 'space';
                    $this_width = $widths['space'];
                    break;
                case ':':
                    $character = 'delimiter';
                    $this_width = $widths['delimiter'];
                    break;
                case 'd':
                    $character = $current['days'];
                    $this_width = $widths['placeholder'];
                    // label
                    echo `/usr/local/bin/gm composite -geometry +{$left}+0 /tmp/{$uniqid}_days.gif /tmp/{$uniqid}_compose2.gif /tmp/{$uniqid}_compose2.gif`;
                    break;
                case 'h':
                    $character = $current['hours'];
                    $this_width = $widths['placeholder'];
                    // label
                    echo `/usr/local/bin/gm composite -geometry +{$left}+0 /tmp/{$uniqid}_hours.gif /tmp/{$uniqid}_compose2.gif /tmp/{$uniqid}_compose2.gif`;
                    break;
                case 'm':
                    $character = $current['minutes'];
                    $this_width = $widths['placeholder'];
                    // label
                    echo `/usr/local/bin/gm composite -geometry +{$left}+0 /tmp/{$uniqid}_minutes.gif /tmp/{$uniqid}_compose2.gif /tmp/{$uniqid}_compose2.gif`;
                    break;
                case 's':
                    $character = $current['seconds'];
                    $this_width = $widths['placeholder'];
                    // label
                    echo `/usr/local/bin/gm composite -geometry +{$left}+0 /tmp/{$uniqid}_seconds.gif /tmp/{$uniqid}_compose2.gif /tmp/{$uniqid}_compose2.gif`;
                    break;
            }

            // draw character
            echo `/usr/local/bin/gm composite -geometry +{$left}+{$label_height} -size {$this_width}x$placeholder_height -resize {$this_width}x$placeholder_height $style_prefix-$character.gif /tmp/{$uniqid}_compose2.gif /tmp/{$uniqid}_compose2.gif`;

            // add letter to format (to help calculate next drawing position)
            $format .= $part;
        }

        /**
         * Animate timer
         */

        $delay = 100;

        // base image
        $cmd = "/usr/local/bin/gm convert ";
        $cmd .= "-delay $delay -page ";
        $cmd .= "+0+0 /tmp/{$uniqid}_compose2.gif ";

        // warning
        $current_warning = '';

        // create batch for GraphicsMagick
        $frame_number = 0;
        for ($second = $now->timestamp; $second < $deadline->timestamp; $second++) {
            // increment frame counter
            $frame_number++;

            // difference
            $diff = Carbon::createFromTimestamp($second)->diff($deadline);

            /**
             * Frenzy Message
             */

            $diff_hours = Carbon::createFromTimestamp($second)->diffInHours($deadline);
            $diff_days = Carbon::createFromTimestamp($second)->diffInDays($deadline);
            $diff_minutes = Carbon::createFromTimestamp($second)->diffInMinutes($deadline);
            $diff_seconds = Carbon::createFromTimestamp($second)->diffInSeconds($deadline);

            $frenzy = [];
            $warning = '';
            if (!empty($timer->frenzy)) {
                $frenzy = json_decode($timer->frenzy, true);

                if (!empty($frenzy)) {
                    foreach ($frenzy as $frenzy_value) {
                        if ($frenzy_value['offset_field'] == 'days') {
                            if ($diff_days < $frenzy_value['offset_value']) {
                                $warning = $frenzy_value['warning_message'];
                            }
                        } elseif ($frenzy_value['offset_field'] == 'hours') {
                            if ($diff_hours < $frenzy_value['offset_value']) {
                                $warning = $frenzy_value['warning_message'];
                            }
                        } elseif ($frenzy_value['offset_field'] == 'minutes') {
                            if ($diff_minutes < $frenzy_value['offset_value']) {
                                $warning = $frenzy_value['warning_message'];
                            }
                        } elseif ($frenzy_value['offset_field'] == 'seconds') {
                            if ($diff_seconds < $frenzy_value['offset_value']) {
                                $warning = $frenzy_value['warning_message'];
                            }
                        }
                    }
                }

                $warning = addslashes($warning);

                /**
                 * Add frenzy warning
                 */

                if ($warning != $current_warning) {
                    // draw warning GIF
                    `/usr/local/bin/gm convert -size {$width}x$warning_height xc:"{$style->background}" -fill "{$style->foreground}" -font $font -pointsize $warning_fontsize -gravity Center -draw "text 0,0 '{$warning}'" /tmp/{$uniqid}_warning.gif`;

                    /**
                     * Add to GIF
                     */

                    $warning_top = $placeholder_height + $label_height;

                    // add page
                    echo `/usr/local/bin/gm composite -geometry +0+{$warning_top} /tmp/{$uniqid}_warning.gif /tmp/{$uniqid}_compose2.gif /tmp/{$uniqid}_compose2.gif`;

                    // keep track of the label
                    $current_warning = $warning;
                }
            }

            $this_width = $widths['placeholder'];

            // did minutes change?
            if ($diff->i != $current['minutes']) {
                // update known minute
                $current['minutes'] = $diff->i;
                // set position
                $temp_format = substr($style->format, 0, strpos($style->format, 'm'));
                $left = (int) ($widths['space'] * substr_count($temp_format, '_')) + ($widths['placeholder'] * preg_match_all('/[dhms]/', $temp_format)) + ($widths['delimiter'] * substr_count($temp_format, ':'));
                // get number
                $number = sprintf('%02d', $diff->i);
                // add page
                $cmd .= "-delay 0 -page +{$left}+{$label_height} -size {$this_width}x$placeholder_height -resize {$this_width}x$placeholder_height $style_prefix-$number.gif ";
            }

            // did hours change?
            if ($diff->h != $current['hours']) {
                // update known minute
                $current['hours'] = $diff->h;
                // set position
                $temp_format = substr($style->format, 0, strpos($style->format, 'h'));
                $left = (int) ($widths['space'] * substr_count($temp_format, '_')) + ($widths['placeholder'] * preg_match_all('/[dhms]/', $temp_format)) + ($widths['delimiter'] * substr_count($temp_format, ':'));
                // get number
                $number = sprintf('%02d', $diff->h);
                // add page
                $cmd .= "-delay 0 -page +{$left}+{$label_height} -size {$this_width}x$placeholder_height -resize {$this_width}x$placeholder_height $style_prefix-$number.gif ";
            }

            // did days change?
            if ($diff->d != $current['days']) {
                // update known minute
                $current['days'] = $diff->d;
                // set position
                $temp_format = substr($style->format, 0, strpos($style->format, 'd'));
                $left = (int) ($widths['space'] * substr_count($temp_format, '_')) + ($widths['placeholder'] * preg_match_all('/[dhms]/', $temp_format)) + ($widths['delimiter'] * substr_count($temp_format, ':'));
                // get number
                $number = sprintf('%02d', $diff->d);
                // add page
                $cmd .= "-delay 0 -page +{$left}+{$label_height} $style_prefix-$number.gif ";
            }

            // always draw seconds
            $temp_format = substr($style->format, 0, strpos($style->format, 's'));
            $left = ($widths['space'] * substr_count($temp_format, '_')) + ($widths['placeholder'] * preg_match_all('/[dhms]/', $temp_format)) + ($widths['delimiter'] * substr_count($temp_format, ':'));
            $number = sprintf('%02d', $diff->s);
            $cmd .= "-delay $delay -page +{$left}+{$label_height} -size {$this_width}x{$placeholder_height} -resize {$this_width}x{$placeholder_height} $style_prefix-$number.gif ";

            // stop at x frames
            if ($frame_number >= 800) break;
        }

        /**
         * Last frame - expired.
         */

        if (Carbon::createFromTimestamp($second)->diffInSeconds($deadline, false) <= 0) {
            $destinationPath = ($timer->upload_custom_image) ? public_path().'/custom_expired_image/' : public_path().'/expired/';
            $expired = $destinationPath.$timer->expired_image;
            // resize expired
            echo `/usr/local/bin/gm convert -size {$width}x{$height} $expired -resize {$width}x{$height} +profile "*" /tmp/{$uniqid}_expired.gif`;
            // background color
            $identify = `/usr/local/bin/gm identify -verbose /tmp/{$uniqid}_expired.gif`;
            preg_match("/Background Color:(.*)/", $identify, $matches);
            $expired_background = (isset($matches)) ? $matches[1] : 'black';
            // overlay
            echo `/usr/local/bin/gm composite -gravity Center /tmp/{$uniqid}_expired.gif /tmp/{$uniqid}_expired-canvas.gif /tmp/{$uniqid}_expired-frame.gif`;
            // add expired frame
            $cmd .= "-delay $delay -page +0+0 /tmp/{$uniqid}_expired-frame.gif ";
        }


        // output file
        $cmd .= "/tmp/{$uniqid}_compose2.gif";

        // make the gif
        `$cmd`;

        // Create response and add encoded image data
        $response = \Response::make(file_get_contents('/tmp/'.$uniqid.'_compose2.gif'));
        // Set content-type
        $response->header('Content-Type', 'image/gif');
        $response->header('Cache-Control', 'no-cache, max-age=0');

        // remove tmp files
        if(!empty($tmp_files)){
            foreach($tmp_files as $file){
                if(file_exists('/tmp/'.$file)){
                    @unlink('/tmp/'.$file);
                }
            }
        }

        // output
        return $response->withCookie(cookie()->forever('st_timer_deadline_'.$timer->id, serialize($deadline)));
    }


    /**
     * Return single frame preview
     *
     * @param Request $request
     * @param Timer $timer
     * @return Response
     */
    public function preview()
    {
        // now: used a few times
        $now = Carbon::now();

        // deadline
        $fake_deadline = array(2, 7, 3, 32);
        $deadline = Carbon::now()->addDays($fake_deadline[0])->addHours($fake_deadline[1])->addMinutes($fake_deadline[2])->addSeconds($fake_deadline[3]);

        // random id for filenames
        $uniqid = uniqid();

        // temp files to delete later
        $tmp_files = [];

        // path
        $stylename = 'custom';
        $style_prefix = '/tmp/'.$uniqid;

        // preview style
        $style = (object) [
            'background' => $_GET['bg'],
            'foreground' => $_GET['fg'],
            'font' => $_GET['fn'],
            'format' => 'dhms',
        ];

        // labels
        $label_days = $_GET['ld'];
        $label_hours = $_GET['lh'];
        $label_minutes = $_GET['lm'];
        $label_seconds = $_GET['ls'];

        // full path to font
        $font = config('app.font_directory').'/'.str_replace(['', ' '], ['.ttf', '-'], $style->font).'.ttf';

        /**
         * Generate numbers for preview
         */

        // generate number template
        echo `OMP_NUM_THREADS=1 /usr/local/bin/gm convert -background "{$style->background}" -fill "{$style->background}" -font $font -pointsize 72 "label:00" {$style_prefix}-placeholder.gif`;

        // garbage collection
        $tmp_files[] = "{$uniqid}-placeholder.gif";

        // get placeholder dimensions
        $width = trim(`/usr/local/bin/gm identify -format "%w" {$style_prefix}-placeholder.gif`);
        $height = trim(`/usr/local/bin/gm identify -format "%h" {$style_prefix}-placeholder.gif`);

        // for GM batch
        ob_start();

        // generate numbers
        foreach ($fake_deadline as $number) {
            $number = sprintf('%02d', $number);
            echo 'convert -background "'.$style->background.'" -fill "'.$style->foreground.'" -font '.$font.' -size '.$width.'x'.$height.' -gravity center "label:'.$number.'" '.$style_prefix.'-'.$number.".gif\n";
            // garbage collection
            $tmp_files[] = "{$uniqid}-{$number}.gif";
        }

        // delimiter
        echo 'convert -background "'.$style->background.'" -fill "'.$style->foreground.'" -font '.$font.' -pointsize 72 "label::" '.$style_prefix.'-delimiter'.".gif\n";

        // garbage collection
        $tmp_files[] = "{$uniqid}-delimiter.gif";

        // space
        echo 'convert -background "'.$style->background.'" -fill "'.$style->foreground.'" -font '.$font.' -pointsize 72 "label: " '.$style_prefix.'-space'.".gif\n";

        // garbage collection
        $tmp_files[] = "{$uniqid}-space.gif";

        // write batch file
        $batchfile = '/tmp/'.$uniqid.'-batch.txt';
        file_put_contents($batchfile, ob_get_clean());

        // garbage collection
        $tmp_files[] = "{$uniqid}-batch.txt";

        // batch create frames
        echo `OMP_NUM_THREADS=1 /usr/local/bin/gm batch $batchfile 2>&1`;

        /**
         * Timer dimensions
         */

        // get all widths
        $widths = [
            'placeholder' => trim(`/usr/local/bin/gm identify -format "%w" $style_prefix-placeholder.gif`),
            'delimiter' => trim(`/usr/local/bin/gm identify -format "%w" $style_prefix-delimiter.gif`),
            'space' => trim(`/usr/local/bin/gm identify -format "%w" $style_prefix-space.gif`),
        ];
        // calculate width
        $width = ($widths['space'] * substr_count($style->format, '_')) + ($widths['placeholder'] * preg_match_all('/[dhms]/', $style->format)) + ($widths['delimiter'] * substr_count($style->format, ':'));
        // get height
        $placeholder_height = trim(`/usr/local/bin/gm identify -format "%h" $style_prefix-placeholder.gif`);
        // label height
        $label_height = $placeholder_height/3;
        // warning height
        $warning_height = $placeholder_height / 2;
        // canvas height
        $height = $placeholder_height + $label_height + $warning_height;
        // label pointsize
        $label_pointsize = 72/3.4;
        // warning fontsize
        $warning_fontsize = 72 / 2.3;

        /**
         * Draw timer
         */

        // draw canvas in bg color
        `/usr/local/bin/gm convert -size {$width}x{$height} xc:"{$style->background}" /tmp/{$uniqid}_canvas.gif`;
        $tmp_files[] = $uniqid.'_canvas.gif';

        /**
         * Make labels
         */

        // days
        echo `/usr/local/bin/gm convert -size {$widths['placeholder']}x$label_height xc:"{$style->background}" -fill "{$style->foreground}" -font $font -pointsize $label_pointsize -gravity Center -draw 'text 0,2 "{$label_days}"' /tmp/{$uniqid}_days.gif`;
        // hours
        echo `/usr/local/bin/gm convert -size {$widths['placeholder']}x$label_height xc:"{$style->background}" -fill "{$style->foreground}" -font $font -pointsize $label_pointsize -gravity Center -draw 'text 0,2 "{$label_hours}"' /tmp/{$uniqid}_hours.gif`;
        // minutes
        echo `/usr/local/bin/gm convert -size {$widths['placeholder']}x$label_height xc:"{$style->background}" -fill "{$style->foreground}" -font $font -pointsize $label_pointsize -gravity Center -draw 'text 0,2 "{$label_minutes}"' /tmp/{$uniqid}_minutes.gif`;
        // seconds
        echo `/usr/local/bin/gm convert -size {$widths['placeholder']}x$label_height xc:"{$style->background}" -fill "{$style->foreground}" -font $font -pointsize $label_pointsize -gravity Center -draw 'text 0,2 "{$label_seconds}"' /tmp/{$uniqid}_seconds.gif`;

        $tmp_files[] = $uniqid.'_days.gif';
        $tmp_files[] = $uniqid.'_hours.gif';
        $tmp_files[] = $uniqid.'_minutes.gif';
        $tmp_files[] = $uniqid.'_seconds.gif';

        // current difference
        $diff = $now->diff($deadline);

        // current
        $current = [
            'days' => sprintf('%02d', $diff->d),
            'hours' => sprintf('%02d', $diff->h),
            'minutes' => sprintf('%02d', $diff->i),
            'seconds' => sprintf('%02d', $diff->s),
        ];

        // copy canvas.gif to compose.gif
        copy('/tmp/'.$uniqid.'_canvas.gif', '/tmp/'.$uniqid.'_compose2.gif');
        $tmp_files[] = $uniqid.'_compose2.gif';

        // first frame, numbers
        $format = '';
        $timer_parts = str_split($style->format);
        foreach ($timer_parts as $index => $part) {
            // pixels from left of canvas
            $left = (int) ($widths['space'] * substr_count($format, '_')) + ($widths['placeholder'] * preg_match_all('/[dhms]/', $format)) + ($widths['delimiter'] * substr_count($format, ':'));

            // thing to draw next
            switch ($part) {
                case '_':
                    $character = 'space';
                    $this_width = $widths['space'];
                    break;
                case ':':
                    $character = 'delimiter';
                    $this_width = $widths['delimiter'];
                    break;
                case 'd':
                    $character = $current['days'];
                    $this_width = $widths['placeholder'];
                    // label
                    echo `/usr/local/bin/gm composite -geometry +{$left}+0 /tmp/{$uniqid}_days.gif /tmp/{$uniqid}_compose2.gif /tmp/{$uniqid}_compose2.gif`;
                    break;
                case 'h':
                    $character = $current['hours'];
                    $this_width = $widths['placeholder'];
                    // label
                    echo `/usr/local/bin/gm composite -geometry +{$left}+0 /tmp/{$uniqid}_hours.gif /tmp/{$uniqid}_compose2.gif /tmp/{$uniqid}_compose2.gif`;
                    break;
                case 'm':
                    $character = $current['minutes'];
                    $this_width = $widths['placeholder'];
                    // label
                    echo `/usr/local/bin/gm composite -geometry +{$left}+0 /tmp/{$uniqid}_minutes.gif /tmp/{$uniqid}_compose2.gif /tmp/{$uniqid}_compose2.gif`;
                    break;
                case 's':
                    $character = $current['seconds'];
                    $this_width = $widths['placeholder'];
                    // label
                    echo `/usr/local/bin/gm composite -geometry +{$left}+0 /tmp/{$uniqid}_seconds.gif /tmp/{$uniqid}_compose2.gif /tmp/{$uniqid}_compose2.gif`;
                    break;
            }

            // draw character
            echo `/usr/local/bin/gm composite -geometry +{$left}+{$label_height} -size {$this_width}x$placeholder_height -resize {$this_width}x$placeholder_height $style_prefix-$character.gif /tmp/{$uniqid}_compose2.gif /tmp/{$uniqid}_compose2.gif`;

            // add letter to format (to help calculate next drawing position)
            $format .= $part;
        }

        // Create response and add encoded image data
        $response = \Response::make(file_get_contents('/tmp/'.$uniqid.'_compose2.gif'));
        // Set content-type
        $response->header('Content-Type', 'image/gif');
        $response->header('Cache-Control', 'no-cache, max-age=0');

        // remove tmp files
        if(!empty($tmp_files)){
            foreach($tmp_files as $file){
                if(file_exists('/tmp/'.$file)){
                    @unlink('/tmp/'.$file);
                }
            }
        }

        // output
        return $response;
    }

    /**
     * (Re-)generate style specified by $style.
     *
     * @param String $name
     * @return Response
     */
    public function generateStyle($name, Timer $timer) {

        // custom style
        if ($name == 'custom') {
            $style = json_decode($timer->styles);
            $style_prefix = config('app.style_directory').'/custom/user-'.$timer->user_id.'/'.$timer->id.'/'.$timer->id;

            // make user directory
            @mkdir(config('app.style_directory').'/custom/user-'.$timer->user_id);

            // delete everything in style directory
            $style_directory = config('app.style_directory').'/custom/user-'.$timer->user_id.'/'.$timer->id;
            `rm -rf ./$style_directory/*.gif`;

            // make timer directory
            @mkdir(config('app.style_directory').'/custom/user-'.$timer->user_id.'/'.$timer->id);

        // built-in style
        } else {
            // get style
            $style = (object) config('app.styles.'.$name);

            // if style doesn't exist in config
            if (!isset($style->name)) {
                return 'Invalid style';
            }

            // style directory
            @mkdir(config('app.style_directory').'/'.$style->name);

            $style_prefix = config('app.style_directory').'/'.$style->name.'/'.$style->name;
        }

        // font
        $font = config('app.font_directory').'/'.str_replace(['', ' '], ['.ttf', '-'], $style->font).'.ttf';

        // generate number template
        echo `OMP_NUM_THREADS=1 /usr/local/bin/gm convert -background "{$style->background}" -fill "{$style->background}" -font $font -pointsize 72 "label:00" {$style_prefix}-placeholder.gif`;

        // get placeholder dimensions
        $width = trim(`/usr/local/bin/gm identify -format "%w" {$style_prefix}-placeholder.gif`);
        $height = trim(`/usr/local/bin/gm identify -format "%h" {$style_prefix}-placeholder.gif`);

        // for GM batch
        ob_start();

        // generate numbers
        for ($i = 0; $i < 100; $i++) {
            $number = sprintf('%02d', $i);
            echo 'convert -background "'.$style->background.'" -fill "'.$style->foreground.'" -font '.$font.' -size '.$width.'x'.$height.' -gravity center "label:'.$number.'" '.$style_prefix.'-'.$number.".gif\n";
        }

        // delimiter
        echo 'convert -background "'.$style->background.'" -fill "'.$style->foreground.'" -font '.$font.' -pointsize 72 "label::" '.$style_prefix.'-delimiter'.".gif\n";

        // space
        echo 'convert -background "'.$style->background.'" -fill "'.$style->foreground.'" -font '.$font.' -pointsize 72 "label: " '.$style_prefix.'-space'.".gif\n";

        // write batch file
        $batchfile = $style_prefix.'.txt';
        file_put_contents($batchfile, ob_get_clean());

        // batch create frames
        echo `OMP_NUM_THREADS=1 /usr/local/bin/gm batch $batchfile 2>&1`;
    }

    /**
     * Return animated Javascript countdown timer.
     *
     * @param Request $request
     * @param Timer $timer
     * @return Response
     */
    public function javascript(Request $request, Timer $timer)
    {
        if ($timer->timer_type == 'deadline') {
            $deadline = new Carbon($timer->deadline, $timer->timezone);
        } else {
            $deadline = Carbon::now($timer->timezone)->addDays($timer->offset_days)->addHours($timer->offset_hours)->addMinutes($timer->offset_minutes)->addSeconds($timer->add_seconds);
            $deadline_cookie = \Cookie::get('st_timer_deadline_'.$timer->id);
            if ($deadline_cookie) {
                $deadline = unserialize($deadline_cookie);
            }
        }

        $now = Carbon::now($timer->timezone);
        $diff_hours = $now->diffInHours($deadline, false);
        $diff_days = $now->diffInDays($deadline, false);
        $diff_minutes = $now->diffInMinutes($deadline, false);
        $diff_seconds = $now->diffInSeconds($deadline, false);

        $link = ($diff_seconds > 0) ? $timer->active_link : $timer->expired_link;
        if (!empty($timer->frenzy)) {
            $frenzy = json_decode($timer->frenzy, true);

            if (!empty($frenzy)){
                foreach ($frenzy as $frenzy_value){
                    if ($frenzy_value['offset_field'] == 'days') {
                        if ($diff_days < $frenzy_value['offset_value']) {
                            $link = $frenzy_value['redirect_link'];
                        }
                    } else if ($frenzy_value['offset_field'] == 'hours') {
                        if ($diff_hours < $frenzy_value['offset_value']) {
                            $link = $frenzy_value['redirect_link'];
                        }
                    } else if ($frenzy_value['offset_field'] == 'minutes') {
                        if ($diff_minutes < $frenzy_value['offset_value']) {
                            $link = $frenzy_value['redirect_link'];
                        }
                    } else if ($frenzy_value['offset_field'] == 'seconds') {
                        if ($diff_seconds < $frenzy_value['offset_value']) {
                            $link = $frenzy_value['redirect_link'];
                        }
                    }
                }
            }
        }

        // style
        $style = json_decode($timer->styles);
        if (!is_object($style)) {
            $style = (object) config('app.styles.'.$timer->styles);
        } else {
            $timer->styles = 'custom';
        }

        // After Expiry
        $ae = !empty($request->get('ae')) ? trim($request->get('ae')) : '';

        // floating
        if (isset($_GET['t']) && $_GET['t'] == 'fb') {
            $css = 'position:fixed;width:100%;left:0;background:'. $style->background .';padding:10px 0;';
            $css .= (isset($_GET['p']) && $_GET['p'] == 't') ? 'top:0;' : 'bottom:0;';
            if (isset($_GET['bg'])) {
                $background = preg_replace('/[^0-9]/', '', $_GET['bg']);
                $css .= 'background:#'.$background.';';
            }
            if (isset($_GET['p'])) {
                switch ($_GET['p']) {
                    case 't':
                        $fixmargin = 'var fixmargin = "top";';
                        break;
                    case 'b':
                        $fixmargin = 'var fixmargin = "bottom";';
                        break;
                }
            }
        // inline
        } else {
            $css = '';
            $fixmargin = 'var fixmargin = "";';
        }
        $medium_type = isset($_GET['medium_type']) ? $_GET['medium_type'] : 'web_inline';

        // expired image
        $expired_path = ($timer->upload_custom_image) ? url('/').'/custom_expired_image/' : url('/').'/expired/all/';
        $expired_image = $expired_path . $timer->expired_image;

        // use transparent files for web timers
        if (!$timer->upload_custom_image) {
            $expired_image = str_replace('.gif', '.png', $expired_image);
        }

        // return view
        return response()->view('timers.js', [
            'timer' => $timer,
            'deadline' => $deadline,
            'style' => $style,
            'css' => $css,
            'font' => str_replace(' ', '-', $style->font).'.ttf',
            'fixmargin' => $fixmargin,
            'medium_type' => $medium_type,
            'expired_image' => $expired_image,
            'ae' => $ae,
            'redirect_link' => ($diff_seconds > 0) ? $link : $timer->expired_link,
        ])->withCookie(cookie()->forever('st_timer_deadline_'.$timer->id, serialize($deadline)));
    }

    /**
     * Return deadline based on Timer ID and email.
     *
     * @param Request $request
     * @param Timer $timer
     * @return Response
     */
    public function api_getdeadline(Request $request, Timer $timer)
    {
        // email
        $email = strtolower(trim($request->get('email')));

        // deadline
        if ($timer->timer_type == 'deadline') {
            // date
            $deadline = new Carbon($timer->deadline, $timer->timezone);
        } else {
            // evergreen
            $deadline = Carbon::now($timer->timezone)->addDays($timer->offset_days)->addHours($timer->offset_hours)->addMinutes($timer->offset_minutes)->addSeconds($timer->add_seconds);

            // locked to email
            if (!empty($email)) {
                $timer_deadline = TimerDeadline::where('timer_id', '=', $timer->id)->where('email', '=', $email)->first();
                if (!$timer_deadline){
                    TimerDeadline::create([
                        'timer_id' => $timer->id,
                        'email' => $email,
                        'deadline' => $deadline
                    ]);
                } else {
                    $deadline = new Carbon($timer_deadline->deadline, $timer->timezone);
                }
            // locked to device
            } else {
                $deadline_cookie = \Cookie::get('st_timer_deadline_'.$timer->id);
                if ($deadline_cookie) {
                    $deadline = unserialize($deadline_cookie);
                }
            }
        }

        // return
        return response()->json(['deadline' => $deadline->toDateTimeString()])->header('Access-Control-Allow-Origin', '*');
    }


    /**
     * Redirect based on timer status.
     *
     * @param Request $request
     * @param Timer $timer
     * @return Response
     */
    public function link(Request $request, Timer $timer)
    {
        $email = strtolower(trim($request->get('email')));

        // deadline
        if($timer->timer_type == 'deadline'){
            $deadline = new Carbon($timer->deadline, $timer->timezone);
        } else {
            $deadline = Carbon::now($timer->timezone)->addDays($timer->offset_days)->addHours($timer->offset_hours)->addMinutes($timer->offset_minutes)->addSeconds($timer->add_seconds);
            if(!empty($email)){
                $timer_deadline = TimerDeadline::where('timer_id', '=', $timer->id)->where('email', '=', $email)->first();
                if(!$timer_deadline){
                    TimerDeadline::create([
                        'timer_id' => $timer->id,
                        'email' => $email,
                        'deadline' => $deadline
                    ]);
                } else {
                    $deadline = new Carbon($timer_deadline->deadline, $timer->timezone);
                }
            } else {
                $deadline_cookie = \Cookie::get('st_timer_deadline_'.$timer->id);
                if($deadline_cookie){
                    $deadline = unserialize($deadline_cookie);
                }
            }
        }
        // used a few times
        $now = Carbon::now($timer->timezone);

        $diff_hours = $now->diffInHours($deadline, false);
        $diff_days = $now->diffInDays($deadline, false);
        $diff_minutes = $now->diffInMinutes($deadline, false);
        $diff_seconds = $now->diffInSeconds($deadline, false);


        // difference
        $diff = $now->diffInSeconds($deadline, false);

        // UTM tags
        $medium_type = isset($_GET['medium_type']) ? $_GET['medium_type'] : 'web_inline';
        $utm_tag = [
            'utm_source' => 'Scarcity Timer',
            'utm_campaign' => $timer->name
        ];
        if($medium_type == 'web_inline'){
            $utm_tag = array_merge($utm_tag, ['utm_medium' => 'Web Inline']);
        } else if($medium_type == 'web_floating'){
            $utm_tag = array_merge($utm_tag, ['utm_medium' => 'Web Floating']);
        } else if($medium_type == 'email'){
            $utm_tag = array_merge($utm_tag, ['utm_medium' => 'email']);
        }

        // add email if present
        $utm_tag = (isset($_GET['email'])) ? array_merge($utm_tag, ['email' => $_GET['email']]) : $utm_tag;

        // make url query
        $utm_string = http_build_query($utm_tag);

        // set link
        $link = ($diff > 0) ? $timer->active_link : $timer->expired_link;

        $frenzy = [];
        if(!empty($timer->frenzy)){
            $frenzy = json_decode($timer->frenzy, true);

            if(!empty($frenzy)){
                foreach($frenzy as $frenzy_value){
                    if($frenzy_value['offset_field'] == 'days'){
                        if($diff_days < $frenzy_value['offset_value']){
                            $link = !empty($frenzy_value['redirect_link']) ? $frenzy_value['redirect_link'] : $link;
                        }
                    } else if($frenzy_value['offset_field'] == 'hours'){
                        if($diff_hours < $frenzy_value['offset_value']){
                            $link = !empty($frenzy_value['redirect_link']) ? $frenzy_value['redirect_link'] : $link;
                        }
                    } else if($frenzy_value['offset_field'] == 'minutes'){
                        if($diff_minutes < $frenzy_value['offset_value']){
                            $link = !empty($frenzy_value['redirect_link']) ? $frenzy_value['redirect_link'] : $link;
                        }
                    } else if($frenzy_value['offset_field'] == 'seconds'){
                        if($diff_seconds < $frenzy_value['offset_value']){
                            $link = !empty($frenzy_value['redirect_link']) ? $frenzy_value['redirect_link'] : $link;
                        }
                    }
                }
            }
        }

        $url_parts = parse_url($link);
        if(isset($url_parts['query'])){
            $link = $link.'&'.$utm_string;
        } else {
            $link = $link.'?'.$utm_string;
        }
        return redirect($link);
    }
}
