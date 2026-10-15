<?php

namespace Tests\Unit\ApiExterna;

use App\Support\ApiExterna\ApiExternaId;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ApiExternaIdTest extends TestCase
{
    #[Test]
    public function encode_decode_no_formato_uuid(): void
    {
        $publicId = ApiExternaId::encode(118435);

        $this->assertNotNull($publicId);
        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $publicId
        );
        $this->assertSame(36, strlen($publicId));
        $this->assertSame(118435, ApiExternaId::decode($publicId));
    }

    #[Test]
    public function decode_invalido_retorna_null(): void
    {
        $this->assertNull(ApiExternaId::decode('nao-e-uuid'));
        $this->assertNull(ApiExternaId::decode('00000000-0000-0000-0000-000000000000'));
        $this->assertNull(ApiExternaId::decode(''));
    }

    #[Test]
    public function map_row_troca_id_por_public_id_e_remove_empresa(): void
    {
        $mapped = ApiExternaId::mapRow([
            'id' => 10,
            'empresa_id' => 104,
            'nome' => 'Teste',
        ], ['id']);

        $this->assertArrayHasKey('public_id', $mapped);
        $this->assertArrayNotHasKey('id', $mapped);
        $this->assertArrayNotHasKey('empresa_id', $mapped);
        $this->assertSame(10, ApiExternaId::decode($mapped['public_id']));
    }
}
