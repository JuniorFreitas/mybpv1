<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForcedPasswordResetFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('require_password_reset')->default(false)->comment('Habilita/desabilita reset forçado de senha');
            $table->integer('password_reset_days')->nullable()->comment('Quantidade de dias para forçar reset de senha');
            $table->timestamp('password_changed_at')->nullable()->comment('Data da última alteração de senha');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['require_password_reset', 'password_reset_days', 'password_changed_at']);
        });
    }
}
