<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 35);
            $table->string('surname', 100);
            $table->string('gender', 2)->nullable();
            $table->year('birth_year')->nullable();
            $table->string('email') -> unique();
            $table->string('password');
            $table->string('cel', 100)->nullable();
            $table->string('tel', 100)->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('role', 30);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('clients');
    }
}
