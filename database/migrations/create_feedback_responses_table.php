<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {

    public function up(): void
    {
       Schema::create('feedback_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('feedback_request_id')->constrained()->onDelete('cascade');
            $table->string('response_text');
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('feedback_responses');
    }
};
