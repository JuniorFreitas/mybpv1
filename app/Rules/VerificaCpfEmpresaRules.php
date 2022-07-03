<?php

namespace App\Rules;

use App\Models\Sistema;
use Illuminate\Contracts\Validation\Rule;

class VerificaCpfEmpresaRules implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $error_message;

    public function __construct($empresa_id, $ignore = false)
    {
        $this->empresa_id = $empresa_id;
        $this->ignore = $ignore;
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
        $curriculo = \DB::table('curriculos')->join('users', 'users.id', '=', 'curriculos.id')
            ->where('curriculos.cpf', $cpf)
            ->where('users.empresa_id', $this->empresa_id);

        if (!$this->ignore && $curriculo->count() > 0) {
            $this->error_message = "CPF já cadastrado";
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
        return $this->error_message;;
    }
}
