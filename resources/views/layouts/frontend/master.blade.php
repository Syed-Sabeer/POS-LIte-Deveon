<!DOCTYPE html>
<html lang="en">

<head>


    @include('layouts.frontend.meta')
    @include('layouts.frontend.css')
    @yield('css')
    <meta name="csrf-token" content="{{ csrf_token() }}">

</head>

<body class="body-wrapper">

    @include('layouts.frontend.preheader')

  <div id="smooth-wrapper">
    <div id="smooth-content">
    @include('layouts.frontend.header')

<main>
    @yield('content')
</main>

    @include('layouts.frontend.footer')

  </div>
  </div>

    @include('layouts.frontend.postfooter')




    @include('layouts.frontend.script')
    @yield('script')
    @yield('js')

</body>

</html>
