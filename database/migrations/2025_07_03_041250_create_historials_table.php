<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up()
{
    Schema::create('historials', function (Blueprint $table) {
        $table->id();

        $table->string('cedula_paciente', 12);
        $table->string('nombre_paciente', 255);
        $table->string('telefono_paciente', 15);
        $table->string('email_paciente')->nullable();

        $table->date('fecha_cita');
        $table->time('hora_cita');
        $table->enum('estado_cita', ['pendiente', 'confirmada', 'cancelada'])->default('pendiente');

        $table->text('procedimiento')->nullable();         
        $table->text('observaciones')->nullable();        
        $table->enum('estado_procedimiento', ['realizado', 'en progreso', 'pendiente'])->nullable();

      
        $table->text('motivo_consulta')->nullable();
        $table->text('antecedentes_personales')->nullable();
        $table->text('antecedentes_familiares')->nullable();
        $table->text('antecedentes_quirurgicos')->nullable();
        $table->text('medicacion_actual')->nullable();
        $table->text('alergias')->nullable();

        $table->boolean('fuma')->default(false);
        $table->boolean('consume_alcohol')->default(false);
        $table->boolean('bruxismo')->default(false);
        $table->text('higiene_oral')->nullable(); 

        $table->text('examen_clinico')->nullable();
        $table->text('diagnostico')->nullable();
        $table->text('plan_tratamiento')->nullable();

        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historials');
    }
};
