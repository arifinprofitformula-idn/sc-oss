<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('gender')->nullable()->after('phone');
            $table->string('birth_place')->nullable()->after('gender');
            $table->date('birth_date')->nullable()->after('birth_place');
            $table->string('job')->nullable()->after('birth_date');
            $table->string('religion')->nullable()->after('job');
            $table->string('marital_status')->nullable()->after('religion');
        });

        // Migrate data from user_profiles to users
        $profiles = DB::table('user_profiles')->get();
        foreach ($profiles as $profile) {
            DB::table('users')->where('id', $profile->user_id)->update([
                'gender' => $profile->gender,
                'birth_place' => $profile->birth_place,
                'birth_date' => $profile->birth_date,
                'job' => $profile->job,
                'religion' => $profile->religion,
                'marital_status' => $profile->marital_status,
                // Also migrate photo if users.profile_picture is empty and profile.photo_path exists
                'profile_picture' => DB::raw("COALESCE(profile_picture, '{$profile->photo_path}')"),
            ]);
        }

        Schema::dropIfExists('user_profiles');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-create user_profiles table
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('gender')->nullable();
            $table->string('birth_place')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('photo_path')->nullable();
            $table->string('job')->nullable();
            $table->string('religion')->nullable();
            $table->string('marital_status')->nullable();
            $table->timestamps();
        });

        // Restore data from users to user_profiles
        $users = DB::table('users')->get();
        foreach ($users as $user) {
            DB::table('user_profiles')->insert([
                'user_id' => $user->id,
                'gender' => $user->gender,
                'birth_place' => $user->birth_place,
                'birth_date' => $user->birth_date,
                'photo_path' => $user->profile_picture,
                'job' => $user->job,
                'religion' => $user->religion,
                'marital_status' => $user->marital_status,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'gender',
                'birth_place',
                'birth_date',
                'job',
                'religion',
                'marital_status',
                'bank_name',
                'bank_account_no',
                'bank_account_name',
                'social_media',
            ]);
        });
    }
};
