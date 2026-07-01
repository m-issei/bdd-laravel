<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->cascadeOnDelete();
            $table->string('title', 100);
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->unique(['survey_id', 'title']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_sections');
    }
};
