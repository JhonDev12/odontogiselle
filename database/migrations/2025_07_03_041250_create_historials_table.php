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

        $table->text('procedimiento')->nullable();         // Aquí el odontólogo puede escribir lo que se le hizo
        $table->text('observaciones')->nullable();         // Observaciones generales
        $table->enum('estado_procedimiento', ['realizado', 'en progreso', 'pendiente'])->nullable();

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
