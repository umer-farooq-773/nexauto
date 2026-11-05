<form class="row g-3 px-2 {{ $extraClass }}" id="{{ $formId }}" action="{{ $formAction }}" method="post"
    @if($enctype) enctype="multipart/form-data" @endif {{ $attributes }}>
    @csrf

    {{ $slot }}
</form>