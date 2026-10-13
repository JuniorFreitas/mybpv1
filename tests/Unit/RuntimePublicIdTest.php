<?php

namespace Tests\Unit;

use App\Support\RuntimePublicId;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RuntimePublicIdTest extends TestCase
{
    #[Test]
    public function encode_decode_roundtrip(): void
    {
        $publicId = RuntimePublicId::encode(7953);

        $this->assertNotSame('7953', $publicId);
        $this->assertDoesNotMatchRegularExpression('/[\/+]/', $publicId);
        $this->assertSame(7953, RuntimePublicId::decode($publicId));
    }

    #[Test]
    public function decode_invalido_falha(): void
    {
        $this->expectException(InvalidArgumentException::class);
        RuntimePublicId::decode('token-invalido');
    }

    #[Test]
    public function encode_id_zero_falha(): void
    {
        $this->expectException(InvalidArgumentException::class);
        RuntimePublicId::encode(0);
    }
}
