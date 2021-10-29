@extends('layouts.adminlogin')
@section('title', "Login")

@section('content')
    <div class="animate form ">
        <section class="login_content">
            <form class="" role="form" method="POST" action="{{ url('/login') }}">
                {!! csrf_field() !!}
                <h1>Login</h1>
                <div>
                    <input type="email" class="form-control" placeholder="Email" name="email" value="{{ old('email') }}" required autofocus />
                </div>
                <div>
                    <input type="password" class="form-control" placeholder="Password" name="password" required="" />
                </div>
                <div class="checkbox">
                    <label>
                        <input type="checkbox" name="remember"> Remember Me
                    </label>
                </div>
                <div>
                    <input type="submit" class="btn btn-default submit" value="Log In" />
                    <a class="reset_pass" href="{{ url('/password/reset') }}">Forgot Your Password?</a>
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
