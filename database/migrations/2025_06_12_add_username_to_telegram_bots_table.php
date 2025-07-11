<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('telegraph_bots', function (Blueprint $table) {
            $table->string('username')->nullable()->after('name');
        });
    }

    public function down()
    {
        Schema::table('telegraph_bots', function (Blueprint $table) {
            $table->dropColumn('username');
        });
    }
};
