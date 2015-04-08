<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateYandexDiskAccessTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
        Schema::create('yandex_access', function(Blueprint $table) {
            $table->increments('id');
            $table->string('access_token');
            $table->unsignedInteger('user_id');
            $table->string('uid');//yandex's user id
            $table->string('name')->default('Yandex Disk');
        });
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
        Schema::dropIfExists('yandex_access');
	}

}
