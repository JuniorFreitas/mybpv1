{{--
    Logo MyBPIN para e-mails (embutido em base64).
    Uso: @include('layouts.mail.logo-mybpin', ['logoAltura' => '40px'])
    Nomes próprios: $arquivo e $altura vazam do e-mail pai (ex.: lista de anexos).
--}}
@php
    $alturaLogo = (isset($logoAltura) && is_string($logoAltura) && $logoAltura !== '')
        ? $logoAltura
        : '40px';

    $arquivoLogo = 'mybpin-logo-transparente.png';
    if (isset($logoArquivo) && is_string($logoArquivo)) {
        $nomeLogo = basename($logoArquivo);
        if (preg_match('/\A[A-Za-z0-9._-]+\.png\z/', $nomeLogo) === 1) {
            $arquivoLogo = $nomeLogo;
        }
    }

    $caminhoLogo = public_path('images/' . $arquivoLogo);
    $srcLogo = is_file($caminhoLogo)
        ? 'data:image/png;base64,' . base64_encode((string) file_get_contents($caminhoLogo))
        : asset('images/' . $arquivoLogo);
@endphp
<img src="{{ $srcLogo }}" alt="MyBPIN" style="height: {{ $alturaLogo }}; width: auto; border: 0;">
