<?php

namespace App\Services\Preadmissao;

use App\Models\Cliente;

/**
 * Termo de aceite e aviso de privacidade do envio de documentos de pré-admissão.
 *
 * MINUTA: o texto precisa ser validado pelo jurídico antes de produção. Ao alterar qualquer
 * palavra, troque a VERSAO — o aceite grava versão, hash e o texto exato que o candidato viu.
 */
class TermoDocumentosPreAdmissao
{
    public const VERSAO = '2026-10-minuta-1';

    /**
     * @return array{versao: string, titulo: string, declaracao: string, itens: array<int, string>,
     *               privacidade: array<int, array{titulo: string, texto: string}>, hash: string}
     */
    public function montar(Cliente $empresa): array
    {
        $controlador = trim((string) ($empresa->razao_social ?: $empresa->nome_fantasia));
        $cnpj = trim((string) ($empresa->cnpj ?? ''));
        $controladorCompleto = $controlador . ($cnpj !== '' ? ' (CNPJ ' . $cnpj . ')' : '');

        $termo = [
            'versao' => self::VERSAO,
            'titulo' => 'Termo de declaração e ciência',
            'declaracao' => 'Li e concordo com o termo de declaração e ciência e com o aviso de privacidade abaixo.',
            'itens' => [
                'Declaro que sou o(a) titular do CPF informado neste acesso e que os dados e documentos enviados são meus, verdadeiros, legíveis e correspondem aos originais.',
                'Estou ciente de que a declaração falsa ou o uso de documento falso pode configurar crime (arts. 298, 299 e 304 do Código Penal) e levar ao encerramento do processo de admissão.',
                'Reconheço que este envio é feito por meio eletrônico, com identificação por CPF e código de uso único enviado ao meu e-mail, e concordo que esse meio é válido para comprovar a autoria e a integridade do ato, nos termos do art. 10, § 2º, da Medida Provisória nº 2.200-2/2001 e da Lei nº 14.063/2020.',
                'Estou ciente de que são registrados data, hora, endereço IP, navegador e a identificação (hash) de cada arquivo enviado, e que receberei um comprovante por e-mail.',
            ],
            'privacidade' => [
                [
                    'titulo' => 'Quem trata seus dados',
                    'texto' => $controladorCompleto . ' é a controladora dos dados. O MyBPIN é a plataforma (operadora) usada para receber e guardar os documentos.',
                ],
                [
                    'titulo' => 'Para que servem',
                    'texto' => 'Os dados e documentos são usados somente para o processo de admissão: conferência cadastral, exames admissionais, registro do contrato de trabalho e obrigações trabalhistas e previdenciárias.',
                ],
                [
                    'titulo' => 'Base legal',
                    'texto' => 'Procedimentos preliminares a contrato a pedido do titular e cumprimento de obrigação legal (Lei nº 13.709/2018 — LGPD, art. 7º, II e V; para dados sensíveis, art. 11, II, "a").',
                ],
                [
                    'titulo' => 'Por quanto tempo',
                    'texto' => 'Se você for admitido(a), os documentos ficam guardados pelos prazos exigidos na legislação trabalhista e previdenciária. Se não for, são eliminados após o encerramento do processo, salvo obrigação legal de guarda.',
                ],
                [
                    'titulo' => 'Seus direitos',
                    'texto' => 'Você pode pedir confirmação, acesso, correção e eliminação dos seus dados (LGPD, art. 18) pelos canais de atendimento do RH de ' . $controlador . '.',
                ],
            ],
        ];

        $termo['hash'] = hash('sha256', json_encode($termo, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return $termo;
    }
}
