@extends('layouts.adminlogin')
@section('title', "Login")

@section('content')
    <div class="animate form ">
        <section class="login_content">
            <form class="" role="form" method="POST" action="{{ url('/password/email') }}">
                {!! csrf_field() !!}
                <h1>Reset Password</h1>
                @if (session('status'))
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                @endif
                <div>
                    <input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" required>
                </div>
                <div>
                    <input type="submit" class="btn btn-default submit" value="Reset Password" />
                    <a href="{{ url('/login') }}" class="reset_pass">Login</a>
                </div>

                <div class="clearfix"></div>

                <div class="separator">
                    @if(helper()->getSettings('enable_register') == 1)
                        <p class="change_link">New to site?
                            <a href="{{ url('/register') }}" class="to_register">Create new account</a>
                        </p>
                        <div class="clearfix"></div>
                        <br />
                    @endif
                    <div>
                        @include('layouts.copyright')
                    </div>
                </div>
            </form>
        </section>
    </div>
@endsection
