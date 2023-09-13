@php
    $lang = Session::get('language');
@endphp
<link rel="stylesheet" href="{{ asset('public//assets/css/vendor.bundle.base.css') }}" async>

<link rel="stylesheet" href="{{ asset('public//assets/fonts/font-awesome.min.css') }}" async/>
<link rel="stylesheet" href="{{ asset('public//assets/select2/select2.min.css') }}" async>
<link rel="stylesheet" href="{{ asset('public//assets/jquery-toast-plugin/jquery.toast.min.css') }}">
<link rel="stylesheet" href="{{ asset('public//assets/color-picker/color.min.css') }}" async>
@if ($lang)
    @if ($lang->is_rtl)
        <link rel="stylesheet" href="{{ asset('public//assets/css/rtl.css') }}">
    @else
        <link rel="stylesheet" href="{{ asset('public//assets/css/style.css') }}">
    @endif
@else
    <link rel="stylesheet" href="{{ asset('public//assets/css/style.css') }}">
@endif
<link rel="stylesheet" href="{{ asset('public//assets/css/datepicker.min.css') }}" async>
<link rel="stylesheet" href="{{ asset('public//assets/css/ekko-lightbox.css') }}">

<link rel="stylesheet" href="{{ asset('public//assets/bootstrap-table/bootstrap-table.min.css') }}">
<link rel="stylesheet" href="{{ asset('public//assets/bootstrap-table/fixed-columns.min.css') }}">


{{-- <link rel="shortcut icon" href="{{asset('public/'. config('global.LOGO_SM')) }}" /> --}}
<link rel="shortcut icon" href="{{ url('public/'. Storage::url(env('FAVICON'))) }}"/>

@php
    $theme_color = getSettings('theme_color');
    // echo json_encode($theme_color);
    $theme_color = $theme_color['theme_color'];
@endphp
<style>
    :root {
        --theme-color: <?=$theme_color ?>;
    }
</style>
<script>
    var baseUrl = "{{ URL::to('/') }}";
    const onErrorImage = (e) => {
        e.target.src = "{{ asset('public//storage/no_image_available.jpg') }}";
    };
</script>
