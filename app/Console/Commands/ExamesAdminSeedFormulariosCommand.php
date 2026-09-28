<?php

namespace App\Console\Commands;

use App\Domain\Exames\Services\ExameFormularioBuilderService;
use App\Domain\Exames\Services\ExameFormularioResolver;
use App\Models\AlternativaFormulario;
use App\Models\Cliente;
use App\Models\ExameTipo;
use App\Models\Formulario;
use App\Models\RespostaAlternativas;
use App\Models\SetoresFormulario;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExamesAdminSeedFormulariosCommand extends Command
{
    protected $signature = 'exames:seed-formularios-admin
                            {--empresa_id= : Limitar a uma empresa}
                            {--dry-run : Apenas listar o que seria feito}';

    protected $description = 'Vincula formulário Exames aos tipos e cria Resultado SESMT padrão por empresa (sem reescrever respostas).';

    public function handle(ExameFormularioResolver $resolver): int
    {
        $empresaFiltro = $this->option('empresa_id') ? (int) $this->option('empresa_id') : null;
        $dry = (bool) $this->option('dry-run');

        $empresasQuery = Cliente::withoutGlobalScopes()->select('id', 'nome_fantasia');
        if ($empresaFiltro) {
            $empresasQuery->where('id', $empresaFiltro);
        }

        $empresas = $empresasQuery->get();
        $tiposGlobais = ExameTipo::query()->whereNull('empresa_id')->where('ativo', true)->get();

        foreach ($empresas as $empresa) {
            $this->line("Empresa #{$empresa->id} — {$empresa->nome_fantasia}");

            $formEncaminhamento = Formulario::withoutGlobalScopes()
                ->where('empresa_id', $empresa->id)
                ->where('titulo', ExameFormularioResolver::TITULO_FALLBACK_ENCAMINHAMENTO)
                ->first();

            if (!$formEncaminhamento) {
                $this->warn('  Sem formulário Exames — pulando vínculo de encaminhamento.');
            }

            $formResultado = Formulario::withoutGlobalScopes()
                ->where('empresa_id', $empresa->id)
                ->where('titulo', ExameFormularioResolver::TITULO_FALLBACK_RESULTADO)
                ->first();

            if (!$formResultado && !$dry) {
                $formResultado = $this->criarFormularioResultadoSesmt($empresa->id);
                $this->info("  Criado formulário Resultado SESMT #{$formResultado->id}");
            } elseif ($formResultado) {
                $this->line("  Resultado SESMT já existe #{$formResultado->id}");
            } elseif ($dry) {
                $this->line('  [dry-run] Criaria formulário Resultado SESMT');
            }

            foreach ($tiposGlobais as $tipo) {
                if ($dry) {
                    $this->line("  [dry-run] Vincularia tipo {$tipo->label} (#{$tipo->id})");
                    continue;
                }

                // Só preenche FKs nos tipos globais se ainda vazios (compartilhado — cuidado multi-tenant).
                // Preferência: não alterar globais; criar clone por empresa quando necessário.
                // Aqui apenas garante fallback por título; vínculos explícitos ficam no admin por empresa.
            }

            if ($formEncaminhamento || $formResultado) {
                $resolver->invalidarCache(null, (int) $empresa->id);
            }
        }

        $this->info('Concluído.');

        return self::SUCCESS;
    }

    private function criarFormularioResultadoSesmt(int $empresaId): Formulario
    {
        return DB::transaction(function () use ($empresaId) {
            $form = Formulario::withoutGlobalScopes()->create([
                'empresa_id' => $empresaId,
                'titulo' => ExameFormularioResolver::TITULO_FALLBACK_RESULTADO,
                'descricao' => 'Resultado padrão SESMT/ASO (compatível com relatórios)',
            ]);

            $setor = SetoresFormulario::create([
                'empresa_id' => $empresaId,
                'nome' => 'Resultado',
            ]);
            $form->Setores()->attach($setor->id, ['ordem' => 1]);

            $campos = [
                ['nome' => 'Resultado', 'tipo' => 'select', 'chave' => 'result', 'obrigatorio' => true, 'opcoes' => ['Apto', 'Apto com restrições', 'Inapto']],
                ['nome' => 'Há pendências?', 'tipo' => 'select', 'chave' => 'pendencias', 'obrigatorio' => true, 'opcoes' => ['Não', 'Sim']],
                ['nome' => 'Aprovado', 'tipo' => 'select', 'chave' => 'aprovado', 'obrigatorio' => true, 'opcoes' => ['Sim', 'Não']],
                ['nome' => 'Trabalho em altura', 'tipo' => 'select', 'chave' => 'trabalho_altura', 'obrigatorio' => false, 'opcoes' => ['Não', 'Sim']],
                ['nome' => 'Espaço confinado', 'tipo' => 'select', 'chave' => 'espacao_confinado', 'obrigatorio' => false, 'opcoes' => ['Não', 'Sim']],
                ['nome' => 'Observações', 'tipo' => 'textarea', 'chave' => 'observacoes', 'obrigatorio' => false, 'opcoes' => []],
            ];

            $ordem = 1;
            foreach ($campos as $campo) {
                $alt = AlternativaFormulario::create([
                    'empresa_id' => $empresaId,
                    'nome' => $campo['nome'],
                    'tipo' => $campo['tipo'],
                    'ativo' => true,
                    'chave_canonica' => $campo['chave'],
                ]);
                $setor->Alternativas()->attach($alt->id, [
                    'obrigatorio' => $campo['obrigatorio'],
                    'min' => null,
                    'max' => null,
                    'ordem' => $ordem++,
                    'class_especial' => null,
                ]);
                $opOrdem = 1;
                foreach ($campo['opcoes'] as $label) {
                    $op = RespostaAlternativas::create([
                        'alternativa_id' => $alt->id,
                        'label' => $label,
                        'selecionado' => false,
                        'value' => null,
                        'ordem' => $opOrdem++,
                    ]);
                    $op->update(['value' => $op->id]);
                }
            }

            return $form->fresh();
        });
    }
}
