<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email');
            $table->date('birthdate')->nullable()->after('phone');
            $table->foreignId('customer_group_id')->nullable()->after('birthdate')
                ->constrained('customer_groups')->nullOnDelete();
            $table->integer('loyalty_points')->default(0)->after('customer_group_id');
            $table->string('loyalty_tier', 20)->default('bronze')->after('loyalty_points');
            $table->decimal('total_spent', 12, 2)->default(0)->after('loyalty_tier');
            $table->json('notification_preferences')->nullable()->after('total_spent');
            $table->timestamp('last_login_at')->nullable()->after('notification_preferences');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['customer_group_id']);
            $table->dropColumn([
                'phone',
                'birthdate',
                'customer_group_id',
                'loyalty_points',
                'loyalty_tier',
                'total_spent',
                'notification_preferences',
                'last_login_at',
            ]);
        });
    }
};
