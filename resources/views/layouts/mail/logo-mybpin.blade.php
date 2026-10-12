{{--
    Logo MyBPIN para e-mails (embutido em base64).
    Motivo: asset()/APP_URL local não é acessível pelos clientes de e-mail;
    WebP também tem suporte fraco (ex.: Outlook) — usamos PNG.
    Uso: @include('layouts.mail.logo-mybpin', ['altura' => '40px'])
--}}
@php
    $alturaLogo = $altura ?? '40px';
    $arquivoLogo = $arquivo ?? 'mybpin-logo-transparente.png';
    $caminhoLogo = public_path('images/' . $arquivoLogo);
    $srcLogo = is_file($caminhoLogo)
        ? 'data:image/png;base64,' . base64_encode((string) file_get_contents($caminhoLogo))
        : asset('images/' . $arquivoLogo);
@endphp
<img src="{{ $srcLogo }}" alt="MyBPIN" style="height: {{ $alturaLogo }}; width: auto; border: 0;">
