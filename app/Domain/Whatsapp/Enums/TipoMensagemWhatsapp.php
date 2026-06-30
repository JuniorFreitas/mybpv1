<?php

namespace App\Domain\Whatsapp\Enums;

enum TipoMensagemWhatsapp: string
{
    case RecrutamentoSelecao = 'recrutamento_selecao';
    case RecrutamentoProvas = 'recrutamento_provas';
    case ExameEncaminhamento = 'exame_encaminhamento';
    case AdmissaoDocumentos = 'admissao_documentos';
    case AdmissaoExame = 'admissao_exame';
    case IntermitenteConvocacao = 'intermitente_convocacao';
    case CartaOfertaGerencial = 'carta_oferta_gerencial';
    case CartaOfertaSgi = 'carta_oferta_sgi';
    case ParecerRotaTransporte = 'parecer_rota_transporte';
    case MovimentacaoAprovacao = 'movimentacao_aprovacao';

    public function label(): string
    {
        return config("whatsapp_templates.tipos.{$this->value}.label", $this->value);
    }

    public function modulo(): string
    {
        return config("whatsapp_templates.tipos.{$this->value}.modulo", 'Geral');
    }

    /** @return string[] */
    public function placeholders(): array
    {
        $globais = config('whatsapp_templates.placeholders_globais', []);
        $especificos = config("whatsapp_templates.tipos.{$this->value}.placeholders", []);

        return array_values(array_unique(array_merge($globais, $especificos)));
    }

    /** @return array<string, string> */
    public static function modulosCatalogo(): array
    {
        $modulos = [];

        foreach (self::cases() as $tipo) {
            $modulos[$tipo->modulo()] = $tipo->modulo();
        }

        return $modulos;
    }

    /** @return string[] */
    public static function modulosLista(): array
    {
        return array_values(self::modulosCatalogo());
    }

    public static function tryFromString(?string $value): ?self
    {
        if ($value === null || $value === '') {
            return null;
        }

        return self::tryFrom($value);
    }

    /** @return array<string, array{label: string, modulo: string, placeholders: string[]}> */
    public static function catalogo(): array
    {
        $catalogo = [];

        foreach (self::cases() as $tipo) {
            $catalogo[$tipo->value] = [
                'label' => $tipo->label(),
                'modulo' => $tipo->modulo(),
                'placeholders' => $tipo->placeholders(),
            ];
        }

        return $catalogo;
    }
}
