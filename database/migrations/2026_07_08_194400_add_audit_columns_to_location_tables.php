<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['countries', 'cities'] as $table) {
            Schema::table($table, function (Blueprint $table): void {
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->unsignedBigInteger('deleted_by')->nullable();
                $table->softDeletes();
            });
        }
    }

    public function down(): void
    {
        foreach (['countries', 'cities'] as $table) {
            Schema::table($table, function (Blueprint $table): void {
                $table->dropSoftDeletes();
                $table->dropColumn(['created_by', 'updated_by', 'deleted_by']);
            });
        }
    }
};
