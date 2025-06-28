<?php

use App\Services\Facades\Option;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Option::set('max_texture_width', 8192);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
