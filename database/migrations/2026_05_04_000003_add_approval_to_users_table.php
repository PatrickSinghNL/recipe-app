<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_approved')->default(false)->after('password');
            $table->boolean('is_admin')->default(false)->after('is_approved');
        });

        // Auto-approve existing users and make the first one an admin
        $firstUser = \DB::table('users')->first();
        if ($firstUser) {
            \DB::table('users')->where('id', $firstUser->id)->update([
                'is_approved' => true,
                'is_admin' => true,
            ]);
            // Approve others but don't make them admins
            \DB::table('users')->where('id', '!=', $firstUser->id)->update([
                'is_approved' => true,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_approved', 'is_admin']);
        });
    }
};
