<p align="center"><a href="https://laravel.com" target="_blank"><img src="http://127.0.0.1:8000/images/logo_bpse_color.png" width="400"></a></p>

<p align="center">
</p>

## METODOS ÚTEIS

ORDENAÇÃO COM JOIN
````php
$query->select('feedback_curriculos.*')
->join('curriculos', 'curriculos.id', '=', 'feedback_curriculos.curriculo_id')
->orderBy('curriculos.nome');
````

Envio de notificação via whatsApp utilizando ZAPME

Para enviar anexo só passar o array com indice anexo com duas posições 'arquivo', 'tipo'
````php
(new ZapNotificacao())->enviar([
    'enviado_id' => 1,
    'telefone' => "5598999023762",
    'mensagem' => 'Enviando pdf',
    'anexo' =>
        [
            'arquivo' => \App\Models\Sistema::convertBase2('http://dspace.bc.uepb.edu.br/jspui/bitstream/123456789/855/1/PDF%20-%20Bruna%20Suellen%20Ara%C3%BAjo%20Diniz%20Epaminondas.pdf', true),
            'tipo' => ZapNotificacao::EXTENSAO_PDF
        ]
])
````

````json

{"max": 1, "min": 1, "multiple": false, "sogestao": false, "apenas_img": true, "apenas_pdf": false, "obrigatorio": true, "apenas_pdf_img": false}
````

Filtro em collection (exemplo: )
````php
$result = collect($result)->filter(function ($item) use ($request) {
    return stripos($item['centro_custo'], $request->campoCentroCusto) !== false;
})->values();
````
