@extends('layouts.adminlogin')
@section('title', 'Register')

@section('content')
    <div id="register" class="animate form">
        <section class="login_content">
            <form class="" role="form" method="POST" action="{{ url('/register') }}">
                {{ csrf_field() }}
                <h1>Create Account</h1>
                <div>
                    <input type="text" class="form-control" placeholder="Name" name="name" value="{{ old('name') }}" required autofocus/>
                </div>
                <div>
                    <input type="email" class="form-control" placeholder="Email" name="email" value="{{ old('email') }}" required />
                </div>
                <div>
                    <input id="password" type="password" class="form-control" placeholder="Password" name="password" required>
                </div>
                <div>
                    <input id="password-confirm" type="password" class="form-control" placeholder="Confirm Password" name="password_confirmation" required>
                </div>
                <div>
                    <input type="submit" class="btn btn-default" value="Submit" />
                    <a href="{{ url('/login') }}" class="reset_pass">Already have account</a>
                </div>

                <div class="clearfix"></div>

                <div class="separator">
                    <div>
                        @include('layouts.copyright')
                    </div>
                </div>
            </form>
        </section>
    </div>

@endsection
