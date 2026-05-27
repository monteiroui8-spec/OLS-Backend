<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Adiciona campos de dados pessoais à tabela users:
 *  - bi_number      : número do Bilhete de Identidade (Angola)
 *  - birth_date     : data de nascimento
 *  - gender         : sexo (M / F / outro)
 *  - nationality    : nacionalidade (texto livre)
 *  - address        : morada completa
 *  - province       : província / estado
 *  - marital_status : estado civil
 *  - guardian_name  : nome do encarregado (útil para menores)
 *  - guardian_phone : telefone do encarregado
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('bi_number', 20)->nullable()->after('cpf');
            $table->date('birth_date')->nullable()->after('bi_number');
            $table->enum('gender', ['M', 'F', 'outro'])->nullable()->after('birth_date');
            $table->string('nationality', 80)->nullable()->default('Angolana')->after('gender');
            $table->text('address')->nullable()->after('nationality');
            $table->string('province', 80)->nullable()->after('address');
            $table->enum('marital_status', ['solteiro', 'casado', 'divorciado', 'viuvo', 'outro'])->nullable()->after('province');
            $table->string('guardian_name', 160)->nullable()->after('marital_status');
            $table->string('guardian_phone', 30)->nullable()->after('guardian_name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'bi_number', 'birth_date', 'gender', 'nationality',
                'address', 'province', 'marital_status',
                'guardian_name', 'guardian_phone',
            ]);
        });
    }
};
