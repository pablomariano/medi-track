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
        Schema::create('email_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Frecuencia de notificaciones
            $table->enum('daily_summary_frequency', ['disabled', 'daily', 'weekly', 'monthly'])
                  ->default('weekly');
            
            // Tipos de notificaciones
            $table->boolean('dose_omitted_notifications')->default(true);
            $table->boolean('adverse_effects_notifications')->default(true);
            $table->boolean('late_dose_notifications')->default(true);
            $table->boolean('treatment_change_notifications')->default(true);
            $table->boolean('appointment_reminders')->default(true);
            $table->boolean('medication_reminders')->default(false); // Solo para pacientes
            $table->boolean('adherence_reports')->default(true);
            
            // Configuraciones avanzadas
            $table->time('preferred_notification_time')->default('08:00:00');
            $table->enum('notification_urgency_level', ['all', 'high_only', 'critical_only'])
                  ->default('all');
            
            // Configuración por días de la semana
            $table->json('notification_days')->nullable(); // ['monday', 'tuesday', ...]
            
            // Email de prueba
            $table->timestamp('last_test_email_sent')->nullable();
            $table->integer('test_emails_sent_today')->default(0);
            
            $table->timestamps();
            
            // Índices
            $table->unique('user_id');
            $table->index(['daily_summary_frequency', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_preferences');
    }
}; 