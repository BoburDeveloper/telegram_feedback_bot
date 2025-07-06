<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('bot_messagings', function (Blueprint $table) {
            $table->id();

            // Aloqador Telegram bot (foreign key)
            $table->foreignId('telegraph_bot_id')
                ->constrained('telegraph_bots')
                ->onDelete('cascade');

            // Foydalanuvchi username yoki guruh nomi
            $table->text('message')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('bot_messagings');
    }
};
