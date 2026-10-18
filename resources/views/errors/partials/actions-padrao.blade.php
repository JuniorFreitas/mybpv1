<a class="btn btn-primary" href="{{ url('/g/login') }}">Ir para o login</a>
<a class="btn btn-secondary" href="{{ url()->previous() !== url()->current() ? url()->previous() : url('/') }}">
    Voltar
</a>
