<?php

use App\Models\MenuItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->string('barcode', 32)->nullable()->unique()->after('slug');
        });

        // Backfill existing rows with unique barcodes.
        $items = DB::table('menu_items')->whereNull('barcode')->get(['id']);
        foreach ($items as $item) {
            DB::table('menu_items')
                ->where('id', $item->id)
                ->update(['barcode' => MenuItem::generateBarcode()]);
        }
    }

    public function down(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropColumn('barcode');
        });
    }
};
