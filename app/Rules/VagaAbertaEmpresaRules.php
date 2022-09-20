<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class VagaAbertaEmpresaRules implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @param $empresa_id
     */

    public $error_message;

    public function __construct($empresa_id, $site = false)
    {
        $this->empresa_id = $empresa_id;
        $this->site = $site;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $vagaAberta = \DB::table('vagas_abertas')
            ->where('id', $value)
            ->where('empresa_id', $this->empresa_id)
            ->where('ativo_sistema', true);

        $vagaAtivaSistema = $vagaAberta->count();

        $validaSite = $vagaAberta->where('ativo', true)->count();

        if ($vagaAtivaSistema == 0 || ($this->site && $validaSite == 0)) {
            $this->error_message = "Vaga não encontrada";
            return false;
        }
        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->error_message;
    }
}
