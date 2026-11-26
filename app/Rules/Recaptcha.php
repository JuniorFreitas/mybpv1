<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Recaptcha implements Rule
{
    public function passes($attribute, $value): bool
    {
        $secret = (string) config('services.recaptcha.secret_key', '');

        if ($secret === '' || !is_string($value) || $value === '') {
            return false;
        }

        try {
            $response = Http::asForm()
                ->timeout(10)
                ->post('https://www.google.com/recaptcha/api/siteverify', [
                    'secret' => $secret,
                    'response' => $value,
                ]);

            if (!$response->successful()) {
                return false;
            }

            return (bool) data_get($response->json(), 'success', false);
        } catch (\Throwable $e) {
            Log::warning('Falha na verificação reCAPTCHA', [
                'message' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function message(): string
    {
        return 'Falha na verificação de Robô';
    }
}
