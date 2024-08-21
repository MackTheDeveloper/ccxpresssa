<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
@section('client_css')
    @include('layouts.included_css_js')
@show
    <body class="skin-green-light">
        <div id="loading">
            <img id="loading-image" src="{{asset('images/loading.gif')}}" class="admin_img" alt="logo"> 
        </div>
    	<div id="app">
    	
<nav class="navbar navbar-default navbar-static-top">
    <h3 style="color: #fff;font-size: 22px;text-align: center;padding-top: 10px;margin:0px">Cargo Management</h3>
</nav>
        
		
        
		<div class="content-wrapper">
        @yield('content')
        </div>
        
        
        </div>
        @section('scripts')
            @yield('page_level_js')
        @show
    </body>
</html>

