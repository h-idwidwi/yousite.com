<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('logs_requests', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->string('method');
            $table->string('controller');
            $table->string('controller_method');
            $table->longText('request_body')->nullable();
            $table->longText('request_headers')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->ipAddress('user_ip');
            $table->string('user_agent')->nullable();
            $table->integer('response_status');
            $table->longText('response_body')->nullable();
            $table->longText('response_headers')->nullable();
            $table->timestamp('called_at');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('logs_requests');
    }
};
