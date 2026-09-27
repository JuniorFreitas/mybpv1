<?php

namespace Tests\Unit\Models;

use App\Models\TipoRecebeEmail;
use App\Models\User;
use Tests\TestCase;

class UserNotificacaoEmailScopeTest extends TestCase
{
    public function test_escopo_exige_check_ativo_e_exclui_sistema(): void
    {
        config(['mail.suppress_recipients' => ['sistema@mybp.com.br']]);

        $query = User::withoutGlobalScopes()
            ->paraNotificacaoEmail(TipoRecebeEmail::VENCIMENTO_ASO, 10);

        $sql = $query->toSql();
        $bindings = $query->getBindings();

        $this->assertStringContainsString('user_recebe_email', $sql);
        $this->assertStringContainsString('tipo_recebe_email', $sql);
        $this->assertStringContainsString('deleted_at', $sql);
        $this->assertStringContainsString('ativo', $sql);
        $this->assertStringContainsString('empresa_id', $sql);
        $this->assertContains('sistema@mybp.com.br', $bindings);
        $this->assertContains(TipoRecebeEmail::VENCIMENTO_ASO, $bindings);
        $this->assertContains(10, $bindings);
    }

    public function test_logins_bloqueados_incluem_sistema_mesmo_sem_config(): void
    {
        config(['mail.suppress_recipients' => []]);

        $this->assertContains('sistema@mybp.com.br', User::loginsBloqueadosNotificacao());
    }
}
