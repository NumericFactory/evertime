@extends('layouts.auth')

@section('contents')

<div class="passwordBox animated fadeInDown">
    <div class="row">
        @if (session('status'))
        <div class="alert alert-success">
            {{ session('status') }}
        </div>
        @endif
        <div class="col-md-12">
            <div class="ibox-content">
                <h2 class="font-bold">Forgot password</h2>
                <p>
                    Enter your email address and your password will be reset and emailed to you.
                </p>
                <div class="row">

                    <div class="col-lg-12">
                        <form class="m-t" role="form" action="{{ url('/password/email') }}" method="POST">
                            {!! csrf_field() !!}
                            <div class="form-group">
                                <input type="email" class="form-control" placeholder="Email address" required="" name="email" value="{{ old('email') }}">
                                @if ($errors->has('email'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <button type="submit" class="btn btn-primary block full-width m-b btn-blue">Send Password Reset Link</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <hr/>
    <div class="row">
        <div class="col-md-6">
            <p class="white-color">Copyright {{ config('app.name') }} <small>© {{ date('Y') }}</small></p>
        </div>
    </div>
</div>
@endsection
