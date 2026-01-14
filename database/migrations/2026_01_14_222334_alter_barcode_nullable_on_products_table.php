<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Postgres: allow NULLs on barcode while keeping unique index intact
        DB::statement('ALTER TABLE products ALTER COLUMN barcode DROP NOT NULL');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert: disallow NULLs on barcode
        DB::statement('ALTER TABLE products ALTER COLUMN barcode SET NOT NULL');
    }
};
