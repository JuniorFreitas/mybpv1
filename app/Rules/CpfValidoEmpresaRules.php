<?php

namespace App\Rules;

use App\Models\Sistema;
use Illuminate\Contracts\Validation\Rule;

class CpfValidoEmpresaRules implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $error_message;

    public function __construct($empresa_id)
    {
        $this->empresa_id = $empresa_id;
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
        $cpf = Sistema::transformCpfCnpj($value);
        if (!Sistema::validaRuleCPF($cpf)) {
            $this->error_message = "CPF inválido";
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
