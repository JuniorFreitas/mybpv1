<p align="center"><a href="https://laravel.com" target="_blank"><img src="http://127.0.0.1:8000/images/logo_bpse_color.png" width="400"></a></p>

<p align="center">
</p>

## METODOS ÚTEIS

````
ORDENAÇÃO COM JOIN

->select('feedback_curriculos.*')
->join('curriculos', 'curriculos.id', '=', 'feedback_curriculos.curriculo_id')
->orderBy('curriculos.nome');;
````

