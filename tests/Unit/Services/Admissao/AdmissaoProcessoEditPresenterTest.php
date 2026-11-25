<?php

namespace Tests\Unit\Services\Admissao;

use App\Models\FeedbackCurriculo;
use App\Services\Admissao\AdmissaoProcessoEditPresenter;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdmissaoProcessoEditPresenterTest extends TestCase
{
    #[Test]
    public function presenter_instancia_e_expoe_montar(): void
    {
        $presenter = new AdmissaoProcessoEditPresenter();
        $this->assertInstanceOf(AdmissaoProcessoEditPresenter::class, $presenter);
        $this->assertTrue(method_exists($presenter, 'montar'));
        $this->assertTrue(is_a(FeedbackCurriculo::class, FeedbackCurriculo::class, true));
    }
}
