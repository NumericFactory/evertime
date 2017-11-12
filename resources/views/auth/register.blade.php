@extends('layouts.auth')

@section('contents')

<div class="middle-box text-center loginscreen   animated fadeInDown">
    @include('common.errors')
    <div>
        <div>
            <h1 class="logo-name" style="font-size: 70px;">{{ config('app.name') }}</h1>
        </div>
        <h3>Register to {{ config('app.name') }}</h3>
        <p>Create account to see it in action.</p>
        <form class="m-t" role="form" action="{{ url('/register') }}" method="POST">
            {!! csrf_field() !!}
            <div class="form-group">
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="Name" required="">
            </div>
            <div class="form-group">
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="Email" required="">
            </div>
            <div class="form-group">
                <input type="password" name="password" class="form-control" placeholder="Password" required="">
            </div>
            <div class="form-group">
                <input type="password" name="password_confirmation" class="form-control" placeholder="Re-Enter Password" required="">
            </div>
            <button type="submit" class="btn btn-primary block full-width m-b">Register</button>

            <p class="text-muted text-center"><small>Already have an account?</small></p>
            <a class="btn btn-sm btn-white btn-block" href="{{ url('/login') }}">Login</a>
        </form>
        <p class="m-t"> <small>Copyright Scarcity Timer &copy; {{ date('Y') }}</small> </p>
    </div>
</div>
@endsection