
    var timer_done = (typeof(timer_done) != 'undefined') ? timer_done : false;

    /**
     * Prevent same timer twice
     */
    var newid = 'jst-cdt-{{ $timer->id}}-'+document.getElementsByClassName('jst-cdt-{{ $timer->id }}').length;

    /**
     * Left padding function: http://stackoverflow.com/questions/10073699/pad-a-number-with-leading-zeros-in-javascript
     */
    function pad(n, width, z) {
      z = z || '0';
      n = n + '';
      return n.length >= width ? n : new Array(width - n.length + 1).join(z) + n;
    }

    /**
     * Javascript we need
     * http://www.html5rocks.com/en/tutorials/speed/script-loading/
     */
    !function(e,t,r){function n(){for(;d[0]&&"loaded"==d[0][f];)c=d.shift(),c[o]=!i.parentNode.insertBefore(c,i)}for(var s,a,c,d=[],i=e.scripts[0],o="onreadystatechange",f="readyState";s=r.shift();)a=e.createElement(t),"async"in i?(a.async=!1,e.head.appendChild(a)):i[f]?(d.push(a),a[o]=n):e.write("<"+t+' src="'+s+'" defer></'+t+">"),a.src=s}(document,"script",[
        "//cdnjs.cloudflare.com/ajax/libs/postscribe/1.4.0/postscribe.min.js",
        "//cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.6/moment-with-locales.min.js",
        "//cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.0/moment-timezone-with-data.js",
    ]);

    /**
     * Get URL param
     */
    // Read a page's GET URL variables and return them as an associative array.
    function getUrlVars()
    {
        var vars = [], hash;
        var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
        for (var i = 0; i < hashes.length; i++) {
            hash = hashes[i].split('=');
            vars.push(hash[0]);
            vars[hash[0]] = hash[1];
        }
        return vars;
    }

    /**
     * Wait till all js we need is loaded
     */

    (function jsLoaded() {
        if (typeof(moment) == 'undefined' || typeof(postscribe) == 'undefined' || typeof(moment.tz) == 'undefined') {
            setTimeout(jsLoaded, 500); // every half second
        } else {
            // only execute once
            if (timer_done) return;

            // hide all other timers
            for (var timer_index = 0; timer_index < document.getElementsByClassName('jst-cdt-{{ $timer->id }}').length; timer_index++) {
                if (timer_index > 0) {
                    document.getElementsByClassName('jst-cdt-{{ $timer->id }}')[timer_index].style.display = 'none';
                }
            }

            /**
             * Timer
             */

            // font
            postscribe(document.getElementsByClassName('jst-cdt-{{ $timer->id }}')[0], '<style>@font-face {font-family: "EverTimer";src: url("https://app.evertimer.io/fonts/{{ $font }}") format("truetype");}</style>');

            // timer
            postscribe(document.getElementsByClassName('jst-cdt-{{ $timer->id }}')[0], '<div class="jst-countdown-{{ $timer->id }}" style="word-wrap:normal;z-index:9999999999;text-align:center;{{ $css }}"><a class="jst-countdown-a-{{ $timer->id }}" style="font-size:72px;display:block;color:{{ $style->foreground }};text-decoration:none;font-family:EverTimer;" href="{{ url('/') }}/lst/{{$timer->id}}/?medium_type={{$medium_type}}">00</a></div>');

            // padding & widths
            var padding = document.getElementsByClassName('jst-countdown-a-{{ $timer->id }}')[0].offsetWidth / 2;
            document.getElementsByClassName('jst-countdown-a-{{ $timer->id }}')[0].style.padding = '0 '+padding;
            document.getElementsByClassName('jst-countdown-a-{{ $timer->id }}')[0].innerHTML = '00';
            // number_width = (typeof(number_width) == 'number') ? number_width : document.getElementsByClassName('jst-countdown-a-{{ $timer->id }}')[0].offsetWidth;
            number_width = 144;

            // reset padding
            document.getElementsByClassName('jst-countdown-a-{{ $timer->id }}')[0].style.padding = '0';

            // css
            var mobilecss = '';
            mobilecss += '<style type="text/css">';
            // numbers
            mobilecss += '.jst-countdown-{{ $timer->id }} .jst-number {display:inline-block;}';
            // line height
            mobilecss += '.jst-countdown-{{ $timer->id }} * {line-height: 100% !important;}';
            // width
            mobilecss += '.jst-number {width:'+ number_width +'px !important;}';
            // mobile responsive
            mobilecss += '@media only screen and (max-width: 500px) {';
            mobilecss += '.jst-countdown-a-{{ $timer->id }} {font-size: 42px !important;}';
            mobilecss += '.jst-number {width:72px !important;}';
            mobilecss += '}';
            // end css
            mobilecss += '</style>';
            postscribe(document.getElementsByClassName('jst-cdt-{{ $timer->id }}')[0], mobilecss);

            // labels
            var label_css = 'color:{{ $style->foreground }};text-decoration:none;font-family:EverTimer;';
            document.getElementsByClassName('jst-countdown-a-{{ $timer->id }}')[0].innerHTML = '<div class="jst-labels" style="font-size:0.25em;"></div><div class="jst-numbers"></div><div class="jst-warning" style="font-size:0.45em;"></div>';
            postscribe(document.getElementsByClassName('jst-labels')[0], '<div class="jst-label jst-number" style="'+ label_css +'">{{ $timer->label_days }}</div>');
            postscribe(document.getElementsByClassName('jst-labels')[0], '<div class="jst-label jst-number" style="'+ label_css +'">{{ $timer->label_hours }}</div>');
            postscribe(document.getElementsByClassName('jst-labels')[0], '<div class="jst-label jst-number" style="'+ label_css +'">{{ $timer->label_minutes }}</div>');
            postscribe(document.getElementsByClassName('jst-labels')[0], '<div class="jst-label jst-number" style="'+ label_css +'">{{ $timer->label_seconds }}</div>');

            // default deadline, should all else fail
            var deadline_date = '{{ $deadline }}';

            // get deadline for this email
            if (typeof(getUrlVars()["email"]) != 'undefined') {
                var request = new XMLHttpRequest();
                request.open('GET', '{{ url('/') }}/api/{{ $timer->id }}/getdeadline?email='+getUrlVars()["email"]+'&'+Math.random(), false);
                request.onload = function() {
                  if (request.status >= 200 && request.status < 400) {
                    // success!
                    var data = JSON.parse(request.responseText);
                    deadline_date = data.deadline;
                  }
                };
                request.send();
            }

            // deadline and difference
            var deadline = moment.tz(deadline_date, '{{ $timer->timezone }}');
            var now = moment.tz(null, '{{ $timer->timezone }}');
            var difference = deadline.diff(now, 'seconds');

            // after expiry action
            var redirect_link = '{{ $redirect_link }}';
            var ae = '{{ $ae }}';

            // fix body margins for floating bar
            {!! $fixmargin !!}
            if (fixmargin) {
                var addedmargin = 141;
                if (fixmargin == 'top') {
                    var margin = parseInt(document.getElementsByTagName('body')[0].style.marginTop);
                    margin = isNaN(margin) ? 0 : margin;
                    document.getElementsByTagName('body')[0].style.marginTop = (margin+addedmargin)+'px';
                } else if (fixmargin == 'bottom') {
                    var margin = parseInt(document.getElementsByTagName('body')[0].style.marginBottom);
                    margin = isNaN(margin) ? 0 : margin;
                    document.getElementsByTagName('body')[0].style.marginBottom = (margin+addedmargin)+'px';
                }
            }

            // refresh
            (function jst_refresh() {
                var now = moment();
                var difference = deadline.diff(now, 'seconds');
                if (difference >= 0) {
                    setTimeout(jst_refresh, 1000);

                    /**
                     * Frenzies
                     */

                    // time difference in days, hours, etc
                    diff_hours = deadline.diff(now, 'hours');
                    diff_days = deadline.diff(now, 'days');
                    diff_minutes = deadline.diff(now, 'minutes');
                    diff_seconds = deadline.diff(now, 'seconds');

                    frenzies = {!! $timer->frenzy !!};
                    warning = '';
                    for (i = 0; i < frenzies.length; i++) {
                        frenzy = frenzies[i];

                        if (diff_days < frenzy['offset_value'] && frenzy['offset_field'] == 'days') {
                            warning = frenzy['warning_message'];
                        }
                        if (diff_hours < frenzy['offset_value'] && frenzy['offset_field'] == 'hours') {
                            warning = frenzy['warning_message'];
                        }
                        if (diff_minutes < frenzy['offset_value'] && frenzy['offset_field'] == 'minutes') {
                            warning = frenzy['warning_message'];
                        }
                        if (diff_seconds < frenzy['offset_value'] && frenzy['offset_field'] == 'seconds') {
                            warning = frenzy['warning_message'];
                        }

                        /**
                         * Add frenzy warning
                         */

                        if (warning != '') {
                            // draw warning
                            document.getElementsByClassName('jst-warning')[0].innerHTML = warning;

                            // keep track of the label
                            current_warning = warning;
                        }
                    }

                    /**
                     * Timer
                     */
                    var now = moment();
                    var difference = deadline.diff(now);
                    var timer = '<div class="jst-number">'+ pad(moment.duration(difference).days(), 2) +'</div>';
                    timer += '<div class="jst-number">'+ pad(moment.duration(difference).hours(), 2) +'</div>';
                    timer += '<div class="jst-number">'+ pad(moment.duration(difference).minutes(), 2) +'</div>';
                    timer += '<div class="jst-number">'+ pad(moment.duration(difference).seconds(), 2) +'</div>';
                    document.getElementsByClassName('jst-numbers')[0].innerHTML = timer;
                } else {
                    // show expired image
                    document.getElementsByClassName('jst-countdown-a-{{ $timer->id }}')[0].innerHTML = "<img style='max-width:100%;height:142px;' src='{{ $expired_image }}'>";

                    // do "expiry action"
                    if (ae == 'r') {
                        window.location.href = redirect_link;
                    } else if (ae == 'h') {
                        document.getElementsByClassName('jst-countdown-{{ $timer->id }}')[0].style.display = 'none';
                    }
                }

                timer_done = true;
            })();
        }
    })();
