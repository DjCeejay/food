<?php

use App\Models\MenuItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->string('barcode', 32)->nullable()->unique()->after('slug');
        });

        // Backfill existing items to ensure every item has a barcode moving forward.
        MenuItem::whereNull('barcode')->chunkById(100, function ($items) {
            foreach ($items as $item) {
                $item->barcode = MenuItem::generateBarcode();
                $item->save();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn('barcode');
        });
    }
};
