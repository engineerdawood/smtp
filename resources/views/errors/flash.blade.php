<div class="mainAlertContainer">
    @if (session()->has('custom_flash_notification'))
        @foreach(get_custom_flash('custom_flash_notification') as $flash)
            <div class="alert alert-{{ $flash['level'] }}">
                <button type="button"
                        class="close"
                        data-dismiss="alert"
                        aria-hidden="true">&times;</button>

                {!! $flash['message'] !!}
            </div>
        @endforeach
    @endif

    @if(isset($errors) && count($errors) > 0)
        <div class="alert alert-danger">
            <div class="container">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                <div class="container">
                    <ul class="errors-list">
                        @foreach($errors->all() as $error)
                            <li>{!! $error !!}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif
</div>
