{{-- Scripts classicos sincronos (jQuery/Vue/axios/globals) — devem rodar ANTES de funcoes.js e Vue.createApp inline --}}
@if (file_exists(public_path('js/legacy-globals.css')))
    <link rel="stylesheet" href="{{ asset('js/legacy-globals.css') }}">
@endif
<script src="{{ asset('js/legacy-globals.js') }}"></script>
<script src="{{ asset('js/funcoes.js') }}"></script>
