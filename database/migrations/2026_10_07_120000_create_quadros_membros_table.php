<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quadros_membros', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('quadro_id');
            $table->unsignedBigInteger('user_id');
            $table->string('papel', 20)->default('membro'); // dono|membro
            $table->timestamps();

            $table->unique(['quadro_id', 'user_id'], 'quadros_membros_quadro_user_unique');
            $table->index('user_id', 'quadros_membros_user_id_index');
            $table->index(['quadro_id', 'papel'], 'quadros_membros_quadro_papel_index');

            $table->foreign('quadro_id')
                ->references('id')
                ->on('quadros')
                ->onDelete('cascade');
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });

        $this->backfillMembros();
    }

    public function down(): void
    {
        Schema::dropIfExists('quadros_membros');
    }

    private function backfillMembros(): void
    {
        $now = now();

        // 1) Criador do quadro vira dono
        $quadros = DB::table('quadros')
            ->select(['id', 'user_id', 'empresa_id'])
            ->whereNotNull('user_id')
            ->get();

        foreach ($quadros as $quadro) {
            DB::table('quadros_membros')->updateOrInsert(
                [
                    'quadro_id' => $quadro->id,
                    'user_id' => $quadro->user_id,
                ],
                [
                    'papel' => 'dono',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );
        }

        // 2) Users da mesma empresa com habilidade weekly_report entram como membro
        $habilidadeId = DB::table('habilidades')->where('nome', 'weekly_report')->value('id');
        if (!$habilidadeId) {
            return;
        }

        $papelIdsComWeekly = DB::table('papeis_habilidades')
            ->where('habilidade_id', $habilidadeId)
            ->pluck('papel_id')
            ->unique()
            ->values()
            ->all();

        if ($papelIdsComWeekly === []) {
            return;
        }

        foreach ($quadros as $quadro) {
            $userIds = DB::table('users')
                ->where('empresa_id', $quadro->empresa_id)
                ->where('ativo', true)
                ->whereNull('deleted_at')
                ->whereIn('grupo_id', $papelIdsComWeekly)
                ->pluck('id');

            foreach ($userIds as $userId) {
                if ((int) $userId === (int) $quadro->user_id) {
                    continue;
                }

                $exists = DB::table('quadros_membros')
                    ->where('quadro_id', $quadro->id)
                    ->where('user_id', $userId)
                    ->exists();

                if ($exists) {
                    continue;
                }

                DB::table('quadros_membros')->insert([
                    'quadro_id' => $quadro->id,
                    'user_id' => $userId,
                    'papel' => 'membro',
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
};
