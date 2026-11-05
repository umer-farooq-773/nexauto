@include('layouts.guest.links')

<div class="container-fluid">
    <div class="row h-100 align-items-center justify-content-center" style="min-height: 100vh;">
        <div class="col-12 col-sm-8 col-md-6 col-lg-5 col-xl-4">
            <div class="bg-light p-4 my-4 mx-3" style="border-radius: 20px;">
                <div class="login-header text-center">
                    <x-logo/>
                    <h2 class="fw-bold m-0 text-primary">{{ $pageTitle }}</h2>
                    <p class="small opacity-75">{{ $subTitle }}</p>
                </div>
      
                {{ $slot }}
            </div>
        </div>
    </div>
</div>

@include('layouts.guest.footer')

@include('layouts.guest.scripts')

@push('login_scripts')
<script>
    document.addEventListener("click", function(e){

    if(e.target.classList.contains("passwordToggler")){

        const input = e.target.closest(".password-field").querySelector("input");

        if(input.type === "password"){
            input.type = "text";
            e.target.classList.replace("bi-eye-slash","bi-eye");
        }else{
            input.type = "password";
            e.target.classList.replace("bi-eye","bi-eye-slash");
        }

    }

});
</script>
@endpush

@stack('login_scripts')

</body>

</html>