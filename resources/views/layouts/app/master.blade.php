<!DOCTYPE html>
<html lang="en">

<head>
    <title>@yield('title')</title>
    @include('layouts.app.meta')
    @include('layouts.app.css')
    @yield('css')

</head>

<body>

<div class="main-wrapper">


    @include('layouts.app.header')
    @include('layouts.app.sidebar')



<div class="page-wrapper">
<div class="content">
        @yield('content')
</div>
@include('layouts.app.footer')

</div>

@include('layouts.app.modal')



</div>


    @include('layouts.app.script')

</body>

</html>
