<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
<<<<<<< HEAD
            $table->string('name')->unique();//Tên đăng nhập
            $table->string('userName')->nullable();//Tên của users
            $table->string('password');
            $table->date('DateOfBirth')->nullable();
            $table->string('current job')->nullable();
            $table->boolean('isAdmin')->nullable();
=======
            $table->string('name');//Tên đăng nhập
            $table->string('username');//Tên của users
            $table->string('password');
            $table->date('birth_date');
            $table->string('current_job');
            $table->boolean('is_admin')->nullable();
>>>>>>> eedac998706109e877d1d6b5885a84d7947dc7f1
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
        Schema::dropIfExists('users');
    }
}
