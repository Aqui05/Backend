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
        Schema::create('data', function (Blueprint $table) {
            $table->id();
            $table->boolean('male');
            $table->integer('age');
            $table->boolean('currentSmoker');
            $table->float('cigsPerDay');
            $table->float('BPMeds');
            $table->boolean('diabetes');
            $table->float('totChol');
            $table->float('sysBP');
            $table->float('diaBP');
            $table->float('BMI');
            $table->float('heartRate');
            $table->float('glucose');
            $table->string('Risk')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('data');
    }
};
