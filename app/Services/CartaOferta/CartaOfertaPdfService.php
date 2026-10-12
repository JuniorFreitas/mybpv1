<?php

namespace App\Services\CartaOferta;

use App\Models\Arquivo;
use App\Models\CartaOferta;
use App\Models\CartaOfertaTemplate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PDF;

/**
 * PDF da carta oferta (template publicado da empresa; sem template, o modelo padrão).
 */
class CartaOfertaPdfService
{
    public function __construct(
        protected CartaOfertaContextoBuilder $contexto,
        protected CartaOfertaTemplateRenderer $renderer,
    ) {
    }

    public function gerar(CartaOferta $carta): string
    {
        $dados = $this->contexto->paraCarta($carta);
        $nomeCargo = $dados['cargo'] !== CartaOfertaContextoBuilder::NAO_INFORMADO ? $dados['cargo'] : 'Cargo';

        $template = CartaOfertaTemplate::query()
            ->withoutGlobalScopes()
            ->where('empresa_id', $carta->empresa_id)
            ->publicado()
            ->orderBy('versao', 'desc')
            ->first();

        if ($template) {
            $html = $this->renderer->render($template->conteudo_html, $dados);
            $pdf = PDF::loadView('pdf.admissao.carta-oferta-template', ['html' => $html]);
        } else {
            $pdf = PDF::loadView('pdf.admissao.carta-oferta', ['cartaOferta' => $carta, 'nomeCargo' => $nomeCargo]);
        }

        return $pdf->setPaper('A4', 'portrait')->output();
    }

    /**
     * Proposta "congelada": gerada uma única vez e guardada com o hash, para que o aceite se refira
     * exatamente ao PDF que o candidato viu, mesmo que o template seja alterado depois.
     */
    public function propostaCongelada(CartaOferta $carta): Arquivo
    {
        if ($carta->proposta_arquivo_id && ($arquivo = Arquivo::find($carta->proposta_arquivo_id))) {
            return $arquivo;
        }

        $conteudo = $this->gerar($carta);
        $arquivo = $this->guardar($carta, $conteudo, 'proposta', 'Carta Oferta.pdf');

        $carta->forceFill([
            'proposta_arquivo_id' => $arquivo->id,
            'proposta_hash' => hash('sha256', $conteudo),
        ])->saveQuietly();

        return $arquivo;
    }

    public function guardar(CartaOferta $carta, string $conteudo, string $prefixo, string $nome): Arquivo
    {
        $disco = Arquivo::DISCO_DOCUMENTO_ASSINATURA;
        $path = 'carta-oferta/' . $carta->id . '/' . $prefixo . '_' . Str::lower(Str::random(20)) . '.pdf';
        Storage::disk($disco)->put($path, $conteudo);

        return Arquivo::create([
            'quem_enviou' => null,
            'nome' => $nome,
            'imagem' => false,
            'layout' => null,
            'extensao' => '.pdf',
            'file' => $path,
            'thumb' => null,
            'bytes' => strlen($conteudo),
            'temporario' => false,
            'chave' => null,
            'disco' => $disco,
        ]);
    }

    public function conteudo(Arquivo $arquivo): ?string
    {
        return Storage::disk($arquivo->disco)->get($arquivo->file);
    }
}
