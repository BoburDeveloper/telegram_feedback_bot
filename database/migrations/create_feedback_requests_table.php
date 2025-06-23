<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('feedback_requests', function (Blueprint $table) {
            $table->id();

            // Foydalanuvchi ismi va username
            $table->string('full_name')->nullable();
            $table->string('username')->nullable();

            // Foydalanuvchi chat_id (Telegram ID)
            $table->string('chat_id');

            // Xabar matni
            $table->text('message')->nullable();

            // Aloqador Telegram bot (agar bir nechta bo‘lsa)
            $table->foreignId('bot_id')
                  ->constrained('telegraph_bots')
                  ->onDelete('cascade');

            // Javob berilganmi (false default)
            $table->boolean('is_answered')->default(false);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback_requests');
    }
};
