<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->string('status', 20)->default('pending')->after('comment');
            $table->text('admin_reply')->nullable()->after('status');
            $table->timestamp('admin_replied_at')->nullable()->after('admin_reply');
            $table->boolean('is_verified_purchase')->default(false)->after('admin_replied_at');
        });

        // Migrate existing data: is_approved=true -> approved, false -> rejected
        DB::table('reviews')->where('is_approved', true)->update(['status' => 'approved']);
        DB::table('reviews')->where('is_approved', false)->update(['status' => 'rejected']);

        // Copy is_verified to is_verified_purchase
        DB::table('reviews')->where('is_verified', true)->update(['is_verified_purchase' => true]);

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropColumn(['is_approved', 'is_verified']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('reviews', function (Blueprint $table) {
            $table->boolean('is_approved')->default(true)->after('comment');
            $table->boolean('is_verified')->default(false)->after('is_approved');
        });

        DB::table('reviews')->where('status', 'approved')->update(['is_approved' => true]);
        DB::table('reviews')->where('status', '!=', 'approved')->update(['is_approved' => false]);
        DB::table('reviews')->where('is_verified_purchase', true)->update(['is_verified' => true]);

        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropColumn(['status', 'admin_reply', 'admin_replied_at', 'is_verified_purchase']);
        });
    }
};
