<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEquipmentTable extends Migration
{
    public function up()
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('serial_number')->unique();
            $table->date('purchase_date');
            $table->decimal('purchase_cost', 10, 2);
            $table->integer('warranty_period')->nullable(); // in months
            $table->enum('status', ['available', 'assigned', 'maintenance', 'retired'])->default('available');
            $table->unsignedBigInteger('location')->nullable(); // department_id
            $table->unsignedBigInteger('assigned_to')->nullable(); // user_id
            $table->timestamp('assigned_date')->nullable();
            $table->timestamps();

            $table->foreign('location')->references('id')->on('departments')->onDelete('set null');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('equipment');
    }
} 