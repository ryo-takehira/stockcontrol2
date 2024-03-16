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
        Schema::create('itemlogs', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('item_id');
            $table->string('item_name');
            // 保管場所
            $table->string('item_type');
            $table->string('user_name');
            // 所属部署
            $table->string('user_type');
            // 操作
            $table->string('operation');
            // 詳細
            $table->string('detail');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('itemlogs');
    }
};
