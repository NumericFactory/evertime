<p>Dear {{ $user->name }},</p>

<p>{{ trans('translate.welcome') }} to {{ config('app.name') }}.</p>

<p>Your login details are:</p>

<p><b>Username:</b> {{ $user->email }}<br>
<p><b>Password:</b> {{ $password }}</p>

<p><b>Log in here:</b> {{ url('/') }}</p>

<p>Please store this email somewhere safe or print it out. If you lose it, you'll have to reset your password.</p>

<p>Your account has <b>{{ $user->max_timers_count}} Timers</b> and <b>{{ $user->max_frenzy_count}} Frenzies</b></p>

<p>--<br>
<p>{{ config('app.name') }}</p>