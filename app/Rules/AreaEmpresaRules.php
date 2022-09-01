<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class AreaEmpresaRules implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @param $empresa_id
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
        $area = \DB::table('area_etiquetas')
            ->where('id', $value)
            ->where('empresa_id', $this->empresa_id)
            ->where('ativo', true);

        if ($area->count() == 0) {
            $this->error_message = "Área não encontrada";
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
