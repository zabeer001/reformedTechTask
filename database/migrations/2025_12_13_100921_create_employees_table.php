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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Link to user account (optional)
            // Core Details
            $table->string('employee_id')->unique();
            $table->date('date_of_birth')->nullable();
            $table->string('phone', 20)->nullable();
            // Employment Details
            $table->string('job_title');
            $table->string('department');
            $table->date('hire_date');
            $table->decimal('salary', 10, 2);
            $table->enum('status', ['Active', 'On Leave', 'Terminated'])->default('Active');

            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
