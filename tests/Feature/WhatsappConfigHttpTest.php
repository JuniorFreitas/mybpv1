<?php

namespace Tests\Feature;

use App\Models\EmpresaWhatsappConfig;
use App\Models\EmpresaWhatsappTemplate;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Feature tests HTTP das APIs de configuração WhatsApp (WhatsappConfigController).
 *
 * Cobre o débito registrado em docs/WHATSAPP_STATUS_ENTREGA.md §5.2
 * ("Feature tests HTTP das APIs WhatsApp") e a materialização de
 * empresa_whatsapp_configs implementada em FEAT-001
 * (WhatsappConfigService::ensureConfigMaterializada).
 *
 * Banco: SQLite em memória (phpunit.xml). Nenhuma tabela real é tocada.
 */
class WhatsappConfigHttpTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['activitylog.enabled' => false]);
        $this->garantirTabelas();
        $this->registrarGates();
        $this->withoutMiddleware();
        Cache::flush();
    }

    // ------------------------------------------------------------------
    // 1) GET status — materializa indiretamente via gate, sem gravar config
    // ------------------------------------------------------------------

    public function test_status_sem_config_explicita_retorna_modulos_padrao_quando_liberado(): void
    {
        $this->criarClienteConfig(10, true);
        $user = $this->criarUsuario(['empresa_id' => 10]);
        $this->actingAs($user);

        $response = $this->getJson('/g/configuracoes/whatsapp/status');

        $response->assertOk()
            ->assertJsonPath('whatsapp_liberado', true)
            ->assertJsonPath('modulos.Recrutamento', true)
            ->assertJsonPath('modulos.Movimentação', true)
            ->assertJsonPath('empresa_id', 10);

        // status() não materializa: continua sem registro explícito até alguém chamar /config.
        $this->assertDatabaseMissing('empresa_whatsapp_configs', ['empresa_id' => 10]);
    }

    public function test_status_quando_empresa_nao_liberada_modulos_ficam_falsos(): void
    {
        $this->criarClienteConfig(11, false);
        $user = $this->criarUsuario(['empresa_id' => 11]);
        $this->actingAs($user);

        $response = $this->getJson('/g/configuracoes/whatsapp/status');

        $response->assertOk()
            ->assertJsonPath('whatsapp_liberado', false)
            ->assertJsonPath('modulos.Recrutamento', false)
            ->assertJsonPath('modulos.Movimentação', false);
    }

    // ------------------------------------------------------------------
    // 2) GET config — materializa empresa_whatsapp_configs (FEAT-001)
    // ------------------------------------------------------------------

    public function test_config_get_materializa_registro_padrao_quando_inexistente(): void
    {
        $this->criarClienteConfig(20, true);
        $user = $this->criarUsuario(['empresa_id' => 20]);
        $this->actingAs($user);

        $this->assertDatabaseMissing('empresa_whatsapp_configs', ['empresa_id' => 20]);

        $response = $this->getJson('/g/configuracoes/whatsapp/config');

        $response->assertOk()
            ->assertJsonPath('whatsapp_liberado', true)
            ->assertJsonPath('readonly', false);

        $this->assertDatabaseHas('empresa_whatsapp_configs', ['empresa_id' => 20]);

        $config = EmpresaWhatsappConfig::where('empresa_id', 20)->first();
        $this->assertIsArray($config->modulos_habilitados);
        $this->assertTrue($config->modulos_habilitados['Recrutamento'] ?? false);

        $modulos = collect($response->json('config.modulos_habilitados'));
        $this->assertTrue($modulos->firstWhere('modulo', 'Recrutamento')['habilitado']);
    }

    // ------------------------------------------------------------------
    // 3) PUT modulos — persiste e invalida cache
    // ------------------------------------------------------------------

    public function test_update_modulos_persiste_desabilita_e_reflete_no_status(): void
    {
        $this->criarClienteConfig(30, true);
        $user = $this->criarUsuario(['empresa_id' => 30]);
        $this->actingAs($user);

        // Pré-aquece o cache lendo o status antes de desabilitar o módulo.
        $this->getJson('/g/configuracoes/whatsapp/status')
            ->assertJsonPath('modulos.Movimentação', true);

        $response = $this->putJson('/g/configuracoes/whatsapp/modulos', [
            'modulos_habilitados' => [
                ['modulo' => 'Movimentação', 'habilitado' => false],
            ],
        ]);

        $response->assertOk()->assertJsonPath('success', true);

        $modulos = collect($response->json('modulos_habilitados'));
        $this->assertFalse($modulos->firstWhere('modulo', 'Movimentação')['habilitado']);
        $this->assertTrue($modulos->firstWhere('modulo', 'Recrutamento')['habilitado']);

        $config = EmpresaWhatsappConfig::where('empresa_id', 30)->first();
        $this->assertFalse($config->modulos_habilitados['Movimentação']);

        // Cache deve ter sido invalidado: a releitura via /status reflete o novo valor.
        $this->getJson('/g/configuracoes/whatsapp/status')
            ->assertJsonPath('modulos.Movimentação', false)
            ->assertJsonPath('modulos.Recrutamento', true);
    }

    public function test_update_modulos_sem_permissao_edicao_retorna_403(): void
    {
        $this->criarClienteConfig(31, true);
        $this->registrarGates(podeConfigurar: false);
        $user = $this->criarUsuario(['empresa_id' => 31]);
        $this->actingAs($user);

        $this->putJson('/g/configuracoes/whatsapp/modulos', [
            'modulos_habilitados' => [
                ['modulo' => 'Recrutamento', 'habilitado' => false],
            ],
        ])->assertStatus(403);

        $this->assertDatabaseMissing('empresa_whatsapp_configs', ['empresa_id' => 31]);
    }

    public function test_update_modulos_bloqueado_quando_empresa_nao_liberou_whatsapp(): void
    {
        // Permissão de tela OK, mas cliente_configs.envia_whatsapp = false.
        $this->criarClienteConfig(32, false);
        $user = $this->criarUsuario(['empresa_id' => 32]);
        $this->actingAs($user);

        $this->putJson('/g/configuracoes/whatsapp/modulos', [
            'modulos_habilitados' => [
                ['modulo' => 'Recrutamento', 'habilitado' => false],
            ],
        ])->assertStatus(403);
    }

    // ------------------------------------------------------------------
    // 4) PUT config contato — salva campos de contato
    // ------------------------------------------------------------------

    public function test_update_config_salva_dados_de_contato_e_preserva_modulos_existentes(): void
    {
        $this->criarClienteConfig(40, true);
        $user = $this->criarUsuario(['empresa_id' => 40]);
        $this->actingAs($user);

        // Módulo customizado previamente (via ensureConfigMaterializada + update manual).
        EmpresaWhatsappConfig::create([
            'empresa_id' => 40,
            'modulos_habilitados' => ['Recrutamento' => false, 'Movimentação' => true],
        ]);

        $response = $this->putJson('/g/configuracoes/whatsapp/config', [
            'nome_exibicao' => 'MyBP Soluções RH',
            'telefone_contato' => '5598999990000',
            'endereco_completo' => 'Av. Teste, 123, Centro, 65000-000, São Luís-MA',
            'texto_assinatura' => 'Equipe MyBP',
        ]);

        $response->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('empresa_whatsapp_configs', [
            'empresa_id' => 40,
            'nome_exibicao' => 'MyBP Soluções RH',
            'telefone_contato' => '5598999990000',
            'texto_assinatura' => 'Equipe MyBP',
        ]);

        // módulos_habilitados não foram enviados no PUT e devem permanecer como estavam.
        $config = EmpresaWhatsappConfig::where('empresa_id', 40)->first();
        $this->assertFalse($config->modulos_habilitados['Recrutamento']);
        $this->assertTrue($config->modulos_habilitados['Movimentação']);
    }

    public function test_update_config_sem_permissao_retorna_403_e_nao_grava(): void
    {
        $this->criarClienteConfig(41, true);
        $this->registrarGates(podeConfigurar: false);
        $user = $this->criarUsuario(['empresa_id' => 41]);
        $this->actingAs($user);

        $this->putJson('/g/configuracoes/whatsapp/config', [
            'nome_exibicao' => 'Hack LTDA',
        ])->assertStatus(403);

        $this->assertDatabaseMissing('empresa_whatsapp_configs', ['empresa_id' => 41]);
    }

    // ------------------------------------------------------------------
    // 5) Isolamento tenant / authorize
    // ------------------------------------------------------------------

    public function test_isolamento_tenant_usuario_nao_admin_nao_acessa_config_de_outra_empresa(): void
    {
        $this->criarClienteConfig(50, true);
        $this->criarClienteConfig(99, true);

        EmpresaWhatsappConfig::create([
            'empresa_id' => 50,
            'nome_exibicao' => 'Empresa 50',
            'modulos_habilitados' => ['Recrutamento' => true],
        ]);
        EmpresaWhatsappConfig::create([
            'empresa_id' => 99,
            'nome_exibicao' => 'Empresa 99 (sigilosa)',
            'modulos_habilitados' => ['Recrutamento' => false],
        ]);

        $user = $this->criarUsuario(['empresa_id' => 50]); // sem administracao_clientes
        $this->actingAs($user);

        // Mesmo tentando forçar ?empresa_id=99 via query string, o controller ignora
        // o parâmetro para usuários sem permissão administracao_clientes.
        $response = $this->getJson('/g/configuracoes/whatsapp/config?empresa_id=99');

        $response->assertOk()
            ->assertJsonPath('config.nome_exibicao', 'Empresa 50')
            ->assertJsonMissing(['nome_exibicao' => 'Empresa 99 (sigilosa)']);
    }

    public function test_admin_pode_acessar_e_editar_config_de_outra_empresa_via_empresa_id(): void
    {
        $this->criarClienteConfig(60, true);
        $this->criarClienteConfig(70, true);

        EmpresaWhatsappConfig::create([
            'empresa_id' => 70,
            'nome_exibicao' => 'Cliente 70 LTDA',
            'modulos_habilitados' => ['Recrutamento' => true],
        ]);

        $this->registrarGates(podeConfigurar: true, podeAdministrar: true);
        $admin = $this->criarUsuario(['id' => 1, 'empresa_id' => 60]);
        $this->actingAs($admin);

        $this->getJson('/g/configuracoes/whatsapp/config?empresa_id=70')
            ->assertOk()
            ->assertJsonPath('config.nome_exibicao', 'Cliente 70 LTDA');

        $this->putJson('/g/configuracoes/whatsapp/modulos?empresa_id=70', [
            'modulos_habilitados' => [
                ['modulo' => 'Recrutamento', 'habilitado' => false],
            ],
        ])->assertOk();

        $empresa70 = EmpresaWhatsappConfig::where('empresa_id', 70)->first();
        $this->assertFalse($empresa70->modulos_habilitados['Recrutamento']);

        // A empresa do próprio admin (60) não foi tocada.
        $this->assertDatabaseMissing('empresa_whatsapp_configs', ['empresa_id' => 60]);
    }

    public function test_status_e_acessivel_a_qualquer_autenticado_mas_config_exige_permissao(): void
    {
        $this->criarClienteConfig(80, true);
        $this->registrarGates(podeConfigurar: false, podeAdministrar: false);
        $user = $this->criarUsuario(['empresa_id' => 80]);
        $this->actingAs($user);

        // status() não chama authorizeAccess(): qualquer usuário autenticado acessa.
        $this->getJson('/g/configuracoes/whatsapp/status')->assertOk();

        // showConfig() exige configuracao_whatsapp OU administracao_clientes.
        $this->getJson('/g/configuracoes/whatsapp/config')->assertStatus(403);
        $this->putJson('/g/configuracoes/whatsapp/modulos', [
            'modulos_habilitados' => [['modulo' => 'Recrutamento', 'habilitado' => false]],
        ])->assertStatus(403);
    }

    public function test_config_readonly_quando_empresa_ainda_nao_liberou_whatsapp(): void
    {
        // Permissão de tela concedida, porém empresa não liberou (envia_whatsapp = false).
        $this->criarClienteConfig(90, false);
        $user = $this->criarUsuario(['empresa_id' => 90]);
        $this->actingAs($user);

        $this->getJson('/g/configuracoes/whatsapp/config')
            ->assertOk()
            ->assertJsonPath('readonly', true);

        $this->putJson('/g/configuracoes/whatsapp/config', [
            'nome_exibicao' => 'Tentativa sem liberação',
        ])->assertStatus(403);
    }

    // ------------------------------------------------------------------
    // 6) Preferências do usuário (rota barata: UserController sem construtor)
    // ------------------------------------------------------------------

    public function test_preferencias_usuario_show_e_update(): void
    {
        $this->criarClienteConfig(100, true);
        $user = $this->criarUsuario(['id' => 500, 'empresa_id' => 100]);
        $this->actingAs($user);

        $this->getJson('/g/usuario/whatsapp-preferencias')
            ->assertOk()
            ->assertJsonPath('whatsapp_liberado', true);

        $response = $this->putJson('/g/usuario/whatsapp-preferencias', [
            'preferencias' => [
                ['modulo' => 'Recrutamento', 'receber' => false],
            ],
        ]);

        $response->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('usuario_whatsapp_preferencias', [
            'user_id' => 500,
            'modulo' => 'Recrutamento',
            'receber' => 0,
        ]);

        $recheck = $this->getJson('/g/usuario/whatsapp-preferencias');
        $preferencias = collect($recheck->json('preferencias'));
        $this->assertFalse($preferencias->firstWhere('modulo', 'Recrutamento')['receber']);
    }

    public function test_preferencias_usuario_sem_permissao_retorna_403(): void
    {
        Gate::define('preferencias_notificacao_whatsapp', fn () => false);
        $this->criarClienteConfig(101, true);
        $user = $this->criarUsuario(['id' => 501, 'empresa_id' => 101]);
        $this->actingAs($user);

        $this->getJson('/g/usuario/whatsapp-preferencias')->assertStatus(403);

        $this->putJson('/g/usuario/whatsapp-preferencias', [
            'preferencias' => [['modulo' => 'Recrutamento', 'receber' => false]],
        ])->assertStatus(403);
    }

    // ------------------------------------------------------------------
    // 7) Templates — listar, exibir, editar, restaurar padrão, preview
    // ------------------------------------------------------------------

    public function test_templates_index_lista_catalogo_completo_sem_customizacao(): void
    {
        $this->criarClienteConfig(110, true);
        $user = $this->criarUsuario(['empresa_id' => 110]);
        $this->actingAs($user);

        $response = $this->getJson('/g/configuracoes/whatsapp/templates');

        $response->assertOk();

        $lista = collect($response->json());
        $this->assertCount(10, $lista);

        $recrutamento = $lista->firstWhere('tipo_mensagem', 'recrutamento_selecao');
        $this->assertNotNull($recrutamento);
        $this->assertFalse($recrutamento['customizado']);
        $this->assertFalse($recrutamento['ativo']);
        $this->assertSame($recrutamento['corpo_padrao'], $recrutamento['corpo']);
        $this->assertSame('Recrutamento', $recrutamento['modulo']);
    }

    public function test_templates_index_sem_permissao_retorna_403(): void
    {
        $this->criarClienteConfig(111, true);
        $this->registrarGates(podeConfigurar: false, podeAdministrar: false);
        $user = $this->criarUsuario(['empresa_id' => 111]);
        $this->actingAs($user);

        $this->getJson('/g/configuracoes/whatsapp/templates')->assertStatus(403);
    }

    public function test_templates_show_retorna_tipo_especifico(): void
    {
        $this->criarClienteConfig(112, true);
        $user = $this->criarUsuario(['empresa_id' => 112]);
        $this->actingAs($user);

        $this->getJson('/g/configuracoes/whatsapp/templates/admissao_documentos')
            ->assertOk()
            ->assertJsonPath('tipo_mensagem', 'admissao_documentos')
            ->assertJsonPath('modulo', 'Admissão');
    }

    public function test_templates_show_tipo_invalido_retorna_404(): void
    {
        $this->criarClienteConfig(113, true);
        $user = $this->criarUsuario(['empresa_id' => 113]);
        $this->actingAs($user);

        $this->getJson('/g/configuracoes/whatsapp/templates/tipo-que-nao-existe')
            ->assertStatus(404);
    }

    public function test_templates_update_persiste_customizacao_e_reflete_na_listagem(): void
    {
        $this->criarClienteConfig(120, true);
        $user = $this->criarUsuario(['empresa_id' => 120]);
        $this->actingAs($user);

        $response = $this->putJson('/g/configuracoes/whatsapp/templates/recrutamento_selecao', [
            'corpo' => 'Olá {{nome_destinatario}}, você foi selecionado para a vaga {{vaga_titulo}}!',
            'ativo' => true,
        ]);

        $response->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('empresa_whatsapp_templates', [
            'empresa_id' => 120,
            'tipo_mensagem' => 'recrutamento_selecao',
            'corpo' => 'Olá {{nome_destinatario}}, você foi selecionado para a vaga {{vaga_titulo}}!',
            'ativo' => 1,
        ]);

        $show = $this->getJson('/g/configuracoes/whatsapp/templates/recrutamento_selecao');
        $show->assertOk()
            ->assertJsonPath('customizado', true)
            ->assertJsonPath('corpo', 'Olá {{nome_destinatario}}, você foi selecionado para a vaga {{vaga_titulo}}!');
    }

    public function test_templates_update_sem_permissao_retorna_403_e_nao_grava(): void
    {
        $this->criarClienteConfig(121, true);
        $this->registrarGates(podeConfigurar: false);
        $user = $this->criarUsuario(['empresa_id' => 121]);
        $this->actingAs($user);

        $this->putJson('/g/configuracoes/whatsapp/templates/recrutamento_selecao', [
            'corpo' => 'Tentativa sem permissão',
        ])->assertStatus(403);

        $this->assertDatabaseMissing('empresa_whatsapp_templates', [
            'empresa_id' => 121,
            'tipo_mensagem' => 'recrutamento_selecao',
        ]);
    }

    public function test_templates_destroy_restaura_padrao_removendo_customizacao(): void
    {
        $this->criarClienteConfig(130, true);
        $user = $this->criarUsuario(['empresa_id' => 130]);
        $this->actingAs($user);

        EmpresaWhatsappTemplate::create([
            'empresa_id' => 130,
            'tipo_mensagem' => 'admissao_exame',
            'corpo' => 'Corpo customizado antigo',
            'ativo' => true,
        ]);

        $this->deleteJson('/g/configuracoes/whatsapp/templates/admissao_exame')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('empresa_whatsapp_templates', [
            'empresa_id' => 130,
            'tipo_mensagem' => 'admissao_exame',
        ]);

        $show = $this->getJson('/g/configuracoes/whatsapp/templates/admissao_exame');
        $show->assertOk()->assertJsonPath('customizado', false);
        $this->assertSame($show->json('corpo_padrao'), $show->json('corpo'));
    }

    public function test_templates_preview_renderiza_placeholders_do_corpo_enviado(): void
    {
        $this->criarClienteConfig(140, true);
        $user = $this->criarUsuario(['empresa_id' => 140]);
        $this->actingAs($user);

        $response = $this->postJson('/g/configuracoes/whatsapp/templates/recrutamento_selecao/preview', [
            'corpo' => 'Olá {{nome_destinatario}}, sua vaga é {{vaga_titulo}}.',
            'contexto' => [
                'nome_destinatario' => 'Maria Teste',
                'vaga_titulo' => 'Analista RH',
            ],
        ]);

        $response->assertOk();
        $this->assertStringContainsString('Olá Maria Teste, sua vaga é Analista RH.', $response->json('mensagem'));
    }

    // ------------------------------------------------------------------
    // 8) Usuários-notificações — listagem paginada e preferência admin
    // ------------------------------------------------------------------

    public function test_usuarios_notificacoes_lista_paginada_retorna_usuarios_da_empresa(): void
    {
        $this->criarClienteConfig(200, true);
        $this->criarClienteConfig(201, true);

        $admin = $this->criarUsuario(['id' => 1, 'empresa_id' => 200]);
        $this->actingAs($admin);

        $userAptoId = $this->criarUsuarioPersistido(['nome' => 'Ana Apta', 'empresa_id' => 200]);
        $this->criarTelefoneWhatsapp($userAptoId, '98999990000');

        $userSemTelefoneId = $this->criarUsuarioPersistido(['nome' => 'Bruno Sem Telefone', 'empresa_id' => 200]);

        // Usuário de outra empresa não deve aparecer na listagem.
        $this->criarUsuarioPersistido(['nome' => 'Carla Outra Empresa', 'empresa_id' => 201]);

        $response = $this->getJson('/g/configuracoes/whatsapp/usuarios-notificacoes');

        $response->assertOk()
            ->assertJsonPath('whatsapp_liberado', true)
            ->assertJsonPath('meta.total', 2);

        $nomes = collect($response->json('data'))->pluck('nome');
        $this->assertTrue($nomes->contains('Ana Apta'));
        $this->assertTrue($nomes->contains('Bruno Sem Telefone'));
        $this->assertFalse($nomes->contains('Carla Outra Empresa'));

        $itemApto = collect($response->json('data'))->firstWhere('id', $userAptoId);
        $this->assertTrue($itemApto['telefone']['tem_whatsapp']);
        $this->assertTrue($itemApto['pode_receber_whatsapp']);

        $itemSemTelefone = collect($response->json('data'))->firstWhere('id', $userSemTelefoneId);
        $this->assertFalse($itemSemTelefone['telefone']['tem_whatsapp']);
        $this->assertFalse($itemSemTelefone['pode_receber_whatsapp']);
    }

    public function test_usuarios_notificacoes_filtra_apenas_aptos_para_whatsapp(): void
    {
        $this->criarClienteConfig(210, true);
        $admin = $this->criarUsuario(['id' => 1, 'empresa_id' => 210]);
        $this->actingAs($admin);

        $aptoId = $this->criarUsuarioPersistido(['nome' => 'Apto Whats', 'empresa_id' => 210]);
        $this->criarTelefoneWhatsapp($aptoId, '98988880000');
        $this->criarUsuarioPersistido(['nome' => 'Nao Apto', 'empresa_id' => 210]);

        $response = $this->getJson('/g/configuracoes/whatsapp/usuarios-notificacoes?apto_whatsapp=sim');

        $response->assertOk()->assertJsonPath('meta.total', 1);
        $this->assertSame('Apto Whats', $response->json('data.0.nome'));
    }

    public function test_usuarios_notificacoes_lista_sem_permissao_retorna_403(): void
    {
        $this->criarClienteConfig(220, true);
        $this->registrarGates(podeConfigurar: false, podeAdministrar: false);
        $user = $this->criarUsuario(['empresa_id' => 220]);
        $this->actingAs($user);

        $this->getJson('/g/configuracoes/whatsapp/usuarios-notificacoes')->assertStatus(403);
    }

    public function test_usuarios_notificacoes_update_persiste_preferencia_e_retorna_usuario(): void
    {
        $this->criarClienteConfig(230, true);
        $admin = $this->criarUsuario(['id' => 1, 'empresa_id' => 230]);
        $this->actingAs($admin);

        $userId = $this->criarUsuarioPersistido(['nome' => 'Usuário Preferência', 'empresa_id' => 230]);
        $this->criarTelefoneWhatsapp($userId, '98977770000');

        $response = $this->putJson("/g/configuracoes/whatsapp/usuarios-notificacoes/{$userId}", [
            'modulo' => 'Recrutamento',
            'receber' => false,
        ]);

        $response->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('usuario_whatsapp_preferencias', [
            'user_id' => $userId,
            'modulo' => 'Recrutamento',
            'receber' => 0,
        ]);

        $this->assertFalse($response->json('usuario.preferencias.Recrutamento.receber'));
        $this->assertSame($userId, $response->json('usuario.id'));
    }

    public function test_usuarios_notificacoes_update_sem_permissao_retorna_403_e_nao_grava(): void
    {
        $this->criarClienteConfig(240, true);
        $this->registrarGates(podeConfigurar: false);
        $user = $this->criarUsuario(['empresa_id' => 240]);
        $this->actingAs($user);

        $userId = $this->criarUsuarioPersistido(['nome' => 'Sem Permissao', 'empresa_id' => 240]);

        $this->putJson("/g/configuracoes/whatsapp/usuarios-notificacoes/{$userId}", [
            'modulo' => 'Recrutamento',
            'receber' => false,
        ])->assertStatus(403);

        $this->assertDatabaseMissing('usuario_whatsapp_preferencias', ['user_id' => $userId]);
    }

    public function test_usuarios_notificacoes_update_bloqueado_quando_empresa_nao_liberou_whatsapp(): void
    {
        // Gate de tela OK, mas cliente_configs.envia_whatsapp = false → canEdit() falha.
        $this->criarClienteConfig(250, false);
        $admin = $this->criarUsuario(['id' => 1, 'empresa_id' => 250]);
        $this->actingAs($admin);

        $userId = $this->criarUsuarioPersistido(['nome' => 'Empresa Nao Liberada', 'empresa_id' => 250]);

        $this->putJson("/g/configuracoes/whatsapp/usuarios-notificacoes/{$userId}", [
            'modulo' => 'Recrutamento',
            'receber' => false,
        ])->assertStatus(403);

        $this->assertDatabaseMissing('usuario_whatsapp_preferencias', ['user_id' => $userId]);
    }

    public function test_usuarios_notificacoes_update_modulo_invalido_retorna_erro(): void
    {
        $this->criarClienteConfig(260, true);
        $admin = $this->criarUsuario(['id' => 1, 'empresa_id' => 260]);
        $this->actingAs($admin);

        $userId = $this->criarUsuarioPersistido(['nome' => 'Modulo Invalido', 'empresa_id' => 260]);

        $this->putJson("/g/configuracoes/whatsapp/usuarios-notificacoes/{$userId}", [
            'modulo' => 'ModuloQueNaoExiste',
            'receber' => false,
        ])->assertStatus(422);

        $this->assertDatabaseMissing('usuario_whatsapp_preferencias', ['user_id' => $userId]);
    }

    // ------------------------------------------------------------------
    // Fixtures
    // ------------------------------------------------------------------

    private function garantirTabelas(): void
    {
        Schema::dropIfExists('curriculo_telefone');
        Schema::dropIfExists('usuario_whatsapp_preferencias');
        Schema::dropIfExists('empresa_whatsapp_templates');
        Schema::dropIfExists('empresa_whatsapp_configs');
        Schema::dropIfExists('cliente_configs');
        Schema::dropIfExists('clientes');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->nullable();
            $table->string('login')->nullable();
            $table->string('tipo')->nullable();
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->unsignedBigInteger('grupo_id')->nullable();
            $table->boolean('ativo')->default(true);
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('razao_social')->nullable();
            $table->string('tel_principal')->nullable();
            $table->string('logradouro')->nullable();
            $table->string('bairro')->nullable();
            $table->string('cep')->nullable();
            $table->string('numero')->nullable();
            $table->string('complemento')->nullable();
            $table->string('municipio')->nullable();
            $table->string('uf')->nullable();
            $table->timestamps();
        });

        Schema::create('cliente_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cliente_id')->nullable();
            $table->boolean('envia_whatsapp')->nullable();
            $table->timestamps();
        });

        Schema::create('empresa_whatsapp_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id')->unique();
            $table->string('nome_exibicao')->nullable();
            $table->string('telefone_contato', 30)->nullable();
            $table->text('endereco_completo')->nullable();
            $table->text('texto_assinatura')->nullable();
            $table->json('modulos_habilitados')->nullable();
            $table->timestamps();
        });

        Schema::create('empresa_whatsapp_templates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->string('tipo_mensagem', 80);
            $table->text('corpo');
            $table->boolean('ativo')->default(true);
            $table->timestamps();
            $table->unique(['empresa_id', 'tipo_mensagem']);
        });

        Schema::create('usuario_whatsapp_preferencias', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('modulo', 80);
            $table->boolean('receber')->default(true);
            $table->timestamps();
            $table->unique(['user_id', 'modulo']);
        });

        // Usada por WhatsappUsuarioTelefoneResolver (listagem de usuários-notificações).
        Schema::create('curriculo_telefone', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 20)->nullable();
            $table->string('pais', 10)->nullable();
            $table->string('numero', 30)->nullable();
            $table->string('ramal', 10)->nullable();
            $table->string('detalhe')->nullable();
            $table->unsignedBigInteger('curriculo_id');
            $table->boolean('principal')->default(false);
        });
    }

    private function registrarGates(bool $podeConfigurar = true, bool $podeAdministrar = false, bool $podePreferencias = true): void
    {
        Gate::define('configuracao_whatsapp', fn () => $podeConfigurar);
        Gate::define('administracao_clientes', fn () => $podeAdministrar);
        Gate::define('preferencias_notificacao_whatsapp', fn () => $podePreferencias);
    }

    private function criarClienteConfig(int $empresaId, bool $enviaWhatsapp): void
    {
        DB::table('cliente_configs')->insert([
            'cliente_id' => $empresaId,
            'envia_whatsapp' => $enviaWhatsapp,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Persiste um usuário real na tabela `users` (necessário para a listagem
     * Eloquent de WhatsappUsuarioNotificacaoService::listarUsuarios).
     *
     * @param array<string, mixed> $dados
     */
    private function criarUsuarioPersistido(array $dados = []): int
    {
        $attrs = array_merge([
            'nome' => 'Usuário Persistido',
            'login' => 'persistido' . uniqid('', true),
            'tipo' => 'COLABORADOR',
            'ativo' => true,
            'empresa_id' => 10,
            'created_at' => now(),
            'updated_at' => now(),
        ], $dados);

        return DB::table('users')->insertGetId($attrs);
    }

    private function criarTelefoneWhatsapp(int $curriculoId, string $numero, bool $principal = true): void
    {
        DB::table('curriculo_telefone')->insert([
            'tipo' => 'whatsapp',
            'pais' => '55',
            'numero' => $numero,
            'curriculo_id' => $curriculoId,
            'principal' => $principal,
        ]);
    }

    /** @param array<string, mixed> $dados */
    private function criarUsuario(array $dados = []): User
    {
        $attrs = array_merge([
            'id' => 1,
            'nome' => 'Usuário Teste',
            'login' => 'teste',
            'tipo' => 'ADMINISTRADOR',
            'ativo' => true,
            'empresa_id' => 10,
        ], $dados);

        $user = new User($attrs);
        $user->id = $attrs['id'];
        $user->exists = true;

        return $user;
    }
}
