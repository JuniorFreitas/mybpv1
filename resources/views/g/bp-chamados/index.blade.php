@extends('layouts.sistema')
@section('content_header', 'Chamados')

@section('breadcrumb')
    <li class="breadcrumb-item active">Chamados</li>
@endsection

@section('content')
    <div id="bp-support" class="bp-chamados-page"></div>
@stop

@push('js')
<script>
    (function () {
        var apiBaseUrl = @json($apiBaseUrl);
        var applicationId = @json($applicationId);
        var tokenUrl = @json($tokenUrl);

        var app = Vue.createApp({});
        if (window.registerGlobals) {
            window.registerGlobals(app);
        }
        app.mount('#app');

        async function fetchWidgetToken() {
            var response = await fetch(tokenUrl, {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json' }
            });
            if (!response.ok) {
                throw new Error('Falha ao obter token do widget BP Chamados');
            }
            return response.json();
        }

        async function bootBpChamados() {
            if (typeof BpTickets === 'undefined' || !BpTickets.mount) {
                throw new Error('BpTickets não carregou');
            }
            var payload = await fetchWidgetToken();
            var widget = BpTickets.mount('#bp-support', {
                token: payload.token,
                apiBaseUrl: apiBaseUrl,
                applicationId: applicationId
            });
            widget.el.addEventListener('token-expired', async function () {
                var refreshed = await fetchWidgetToken();
                widget.updateToken(refreshed.token);
            });
        }

        function loadScript(src) {
            return new Promise(function (resolve, reject) {
                var existing = document.querySelector('script[src="' + src + '"]');
                if (existing && window.BpTickets) {
                    resolve();
                    return;
                }
                var script = document.createElement('script');
                script.src = src;
                script.async = true;
                script.onload = function () { resolve(); };
                script.onerror = function () { reject(new Error('Falha ao carregar bp-tickets.js')); };
                document.body.appendChild(script);
            });
        }

        loadScript(apiBaseUrl + '/vendor/bp-chamados/bp-tickets.js')
            .then(bootBpChamados)
            .catch(function (err) {
                console.error(err);
                var el = document.getElementById('bp-support');
                if (el) {
                    el.innerHTML = '<div class="alert alert-danger mb-0">Não foi possível carregar o módulo de chamados. Tente novamente em instantes.</div>';
                }
            });
    })();
</script>
@endpush
