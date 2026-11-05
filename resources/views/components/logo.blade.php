<a href="{{ route('welcome') }}" class="navbar-brand ms-3 mb-3">
    <img src="{{ (config('app.logo') != '' && file_exists(public_path(config('app.logo')))) ? asset(config('app.logo')) : asset('assets/img/logo.png') }}" 
         onerror="this.onerror=null; this.src='{{ asset('assets/img/logo.png') }}';"
         alt="{{ config('app.name') }} logo"
         class="logo-img mb-2" 
         width="50px"
         style="object-fit: contain;">
</a>