<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fitur', function (Blueprint $table) {
            $table->increments('id_fitur');
            $table->string('fitur', 100);
            $table->string('acc', 1);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fitur');
    }
};
