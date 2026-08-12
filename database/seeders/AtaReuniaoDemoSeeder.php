<?php

namespace Database\Seeders;

use App\Models\AtaReuniao;
use App\Models\AtaReuniaoAcesso;
use App\Models\AtaReuniaoAcao;
use App\Models\AtaReuniaoAssunto;
use App\Models\AtaReuniaoParticipante;
use App\Models\AtaReuniaoTipo;
use App\Models\AreaEtiqueta;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AtaReuniaoDemoSeeder extends Seeder
{
    public function run(): void
    {
        $empresa = User::firstOrCreate([
            'login' => 'empresa.atas.demo@mybp.com.br',
        ], [
            'nome' => 'Empresa Demo Atas',
            'password' => Hash::make('demo123456'),
            'tipo' => User::EMPRESA,
            'ativo' => true,
            'temp' => false,
            'empresa_id' => null,
        ]);

        $usuarios = collect(range(1, 10))->map(function (int $index) use ($empresa) {
            return User::firstOrCreate([
                'login' => "usuario.atas.demo{$index}@mybp.com.br",
            ], [
                'nome' => "Usuario Demo Atas {$index}",
                'password' => Hash::make('demo123456'),
                'tipo' => $index === 1 ? User::ADMINISTRADOR : User::FUNCIONARIO,
                'ativo' => true,
                'temp' => false,
                'empresa_id' => $empresa->id,
            ]);
        });

        $areas = collect(['Qualidade', 'Operacoes', 'Projetos', 'RH', 'Seguranca'])->map(function (string $nome, int $index) use ($empresa, $usuarios) {
            return AreaEtiqueta::withoutGlobalScopes()->firstOrCreate([
                'empresa_id' => $empresa->id,
                'label' => $nome,
            ], [
                'ativo' => true,
                'gestor_id' => $usuarios[$index]->id,
            ]);
        });

        $statusAtas = [
            AtaReuniao::STATUS_RASCUNHO,
            AtaReuniao::STATUS_EM_ELABORACAO,
            AtaReuniao::STATUS_AGUARDANDO_APROVACAO,
            AtaReuniao::STATUS_APROVADA,
            AtaReuniao::STATUS_PUBLICADA,
            AtaReuniao::STATUS_AJUSTES_SOLICITADOS,
            AtaReuniao::STATUS_ENCERRADA,
            AtaReuniao::STATUS_CANCELADA,
        ];

        $atas = collect(range(1, 8))->map(function (int $index) use ($empresa, $usuarios, $areas, $statusAtas) {
            $data = Carbon::now()->subDays(12 - $index)->setTime(9, 0);
            $ata = AtaReuniao::withoutGlobalScopes()->firstOrCreate([
                'empresa_id' => $empresa->id,
                'codigo' => 'ATA-DEMO-2026-' . str_pad((string) $index, 6, '0', STR_PAD_LEFT),
            ], [
                'uuid_publico' => (string) Str::uuid(),
                'quem_cadastrou' => $usuarios[0]->id,
                'organizador_id' => $usuarios[0]->id,
                'redator_id' => $usuarios[1]->id,
                'titulo' => "Reuniao Demo {$index}",
                'objetivo' => "Demonstrar fluxo de ata e pendencias {$index}.",
                'status' => $statusAtas[$index - 1],
                'nivel_acesso' => $index % 2 === 0 ? 'participantes' : 'privada',
                'classificacao_confidencialidade' => $index % 3 === 0 ? 'confidencial' : 'uso_interno',
                'area_etiqueta_id' => $areas[($index - 1) % $areas->count()]->id,
                'local' => $index % 2 === 0 ? 'Teams' : 'Sala de reuniao',
                'data_inicio' => $data,
                'data_fim' => $data->copy()->addHour(),
                'empresa_id' => $empresa->id,
                'versao_atual' => in_array($statusAtas[$index - 1], [AtaReuniao::STATUS_APROVADA, AtaReuniao::STATUS_PUBLICADA, AtaReuniao::STATUS_ENCERRADA], true) ? '1.0' : '0.1',
                'aprovada_em' => in_array($statusAtas[$index - 1], [AtaReuniao::STATUS_APROVADA, AtaReuniao::STATUS_PUBLICADA, AtaReuniao::STATUS_ENCERRADA], true) ? now()->subDays(3) : null,
                'bloqueada_em' => in_array($statusAtas[$index - 1], [AtaReuniao::STATUS_APROVADA, AtaReuniao::STATUS_PUBLICADA, AtaReuniao::STATUS_ENCERRADA], true) ? now()->subDays(3) : null,
            ]);

            AtaReuniaoAcesso::withoutGlobalScopes()->updateOrCreate([
                'empresa_id' => $empresa->id,
                'ata_reuniao_id' => $ata->id,
                'user_id' => $usuarios[0]->id,
                'papel' => AtaReuniaoAcesso::PAPEL_PROPRIETARIO,
            ], ['origem' => 'demo']);

            AtaReuniaoAssunto::firstOrCreate(['ata_reuniao_id' => $ata->id, 'assunto' => "Pauta principal {$index}"]);
            AtaReuniaoTipo::firstOrCreate(['ata_reuniao_id' => $ata->id, 'tipo' => 'comentarios'], ['observacao' => "Discussao registrada na ata demo {$index}."]);

            foreach ($usuarios->slice(0, 4) as $usuario) {
                AtaReuniaoParticipante::firstOrCreate([
                    'ata_reuniao_id' => $ata->id,
                    'user_id' => $usuario->id,
                ], [
                    'nome' => $usuario->nome,
                    'funcao' => $usuario->tipo,
                ]);
            }

            return $ata;
        });

        $statusPendencias = ['nao_iniciada', 'em_andamento', 'aguardando_terceiro', 'aguardando_validacao', 'concluida', 'atrasada', 'reprogramada'];
        foreach (range(1, 25) as $index) {
            $ata = $atas[($index - 1) % $atas->count()];
            $responsavel = $usuarios[$index % $usuarios->count()];
            $status = $statusPendencias[($index - 1) % count($statusPendencias)];
            $prazo = match (true) {
                $status === 'atrasada' => now()->subDays($index % 5 + 1),
                $status === 'concluida' => now()->subDays(2),
                $index % 4 === 0 => now()->addDays(2),
                default => now()->addDays(($index % 10) + 1),
            };

            AtaReuniaoAcao::firstOrCreate([
                'empresa_id' => $empresa->id,
                'ata_reuniao_id' => $ata->id,
                'titulo' => "Pendencia demo {$index}",
            ], [
                'descricao' => "Descricao da pendencia demo {$index}.",
                'responsavel' => $responsavel->nome,
                'responsavel_id' => $responsavel->id,
                'email' => $responsavel->login,
                'acao' => "Executar acao demo {$index}",
                'prazo' => $prazo->format('d/m/Y'),
                'continuo' => false,
                'status' => $status,
                'prioridade' => $index % 3 === 0 ? 'alta' : 'media',
                'criado_por' => $usuarios[0]->id,
                'percentual_conclusao' => $status === 'concluida' ? 100 : ($index % 5) * 20,
                'data_conclusao' => $status === 'concluida' ? now()->subDay() : null,
            ]);
        }
    }
}
