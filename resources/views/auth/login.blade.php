@extends('layouts.auth')

@section('contents')
<div class="middle-box text-center loginscreen animated fadeInDown">

    @include('common.errors')
    <div>
        <p><img src='{{ asset("images/logo-300px.png") }}'></p>
        <h3 class="white-color">Welcome to {{ config('app.name') }}</h3>
        <p class="white-color">Login in. To see it in action.</p>
        <!-- Form -->
        <form action="{{ url('/login') }}" method="POST" class="m-t" role="form">
            {!! csrf_field() !!}
            <div class="form-group">
                <input type="email" name="email" class="form-control" value="{{ old('email') }}" placeholder="Email" required="">
            </div>
            <div class="form-group">
                <input type="password" name="password" class="form-control" placeholder="Password" required="">
            </div>
            <button type="submit" class="btn btn-primary block full-width m-b btn-blue">Login</button>

            <a href="{{ url('/password/reset') }}"><small>Forgot password?</small></a>
        </form>

        <p class="m-t white-color"> <small>Copyright {{ config('app.name') }} &copy; {{ date('Y') }}</small> </p>
    </div>
</div>
@endsection