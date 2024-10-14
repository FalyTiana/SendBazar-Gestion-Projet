<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('taches', function (Blueprint $table) {
            $table->string('date_debut')->nullable()->change();
            $table->string('date_fin')->nullable()->change();
        });
    }

    public function down(): void {
        Schema::table('taches', function (Blueprint $table) {
            $table->date('date_debut')->nullable()->change();
            $table->date('date_fin')->nullable()->change();
        });
    }
};
