<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
 public function up(): void
{
    Schema::create('citas', function (Blueprint $table) {
        $table->id();

        $table->string('nombre_paciente');
        $table->string('cedula_paciente'); // ✅ número de cédula obligatorio
        $table->string('email_paciente')->nullable();
        $table->string('telefono_paciente');

        $table->dateTime('fecha_hora_cita');
        $table->string('motivo_cita')->nullable();
        $table->string('estado')->default('pendiente');

        // $table->unsignedBigInteger('odontologo_id')->nullable();
        // $table->foreign('odontologo_id')->references('id')->on('odontologos')->onDelete('set null');
        

        $table->string('observaciones')->nullable();
        $table->timestamp('cancelada_en')->nullable();

        $table->timestamps();
    });
}


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
