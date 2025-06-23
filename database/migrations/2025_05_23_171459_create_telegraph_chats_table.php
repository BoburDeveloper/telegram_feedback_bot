<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('telegraph_chats', function (Blueprint $table) {
            $table->id();

            // Aloqador Telegram bot (foreign key)
            $table->foreignId('telegraph_bot_id')
                ->constrained('telegraph_bots')
                ->onDelete('cascade');

            // Telegram chat ID (user, group yoki kanal)
            $table->string('chat_id');

            // Foydalanuvchi username yoki guruh nomi
            $table->string('name')->nullable();

            // Chat turi: private, group, supergroup, channel
            $table->string('type')->default('private');

            // Qo‘shimcha imkoniyatlar uchun reserved
            $table->json('extra')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('telegraph_chats');
    }
};
