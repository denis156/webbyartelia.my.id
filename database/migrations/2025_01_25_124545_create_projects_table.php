<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('project_name');
            $table->text('description');
            $table->decimal('price', 15, 2);
            $table->enum('status', ['pending', 'approved', 'rejected', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->integer('progress')->default(0);
            $table->date('start_date')->nullable();
            $table->date('deadline')->nullable();
            $table->date('completion_date')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->text('requirements')->nullable();
            $table->json('attachment_path')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('projects');
    }
};
