<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qr_codes', function (Blueprint $table) {
            // Allow event-level QR codes (no specific registration yet).
            $table->foreignId('registration_id')->nullable()->change();

            // Different QR use-cases.
            $table->string('type')->default('attendance')->index();

            $table->index(['event_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::table('qr_codes', function (Blueprint $table) {
            $table->dropIndex(['event_id', 'type']);
            $table->dropIndex(['type']);
            $table->dropColumn('type');

            $table->foreignId('registration_id')->nullable(false)->change();
        });
    }
};

