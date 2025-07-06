<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('bot_messagings', function (Blueprint $table) {
            $table->string('image')->nullable()->after('message');
            $table->string('caption')->nullable()->after('image');
        });
    }

    public function down()
    {
        Schema::table('bot_messagings', function (Blueprint $table) {
            $table->dropColumn('image');
            $table->dropColumn('caption');
        });
    }
};
