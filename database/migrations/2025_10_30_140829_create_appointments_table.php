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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('provider_id')->nullable()->constrained()->onDelete('set null');
            // $table->foreignId('service_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name')->comment('Customer name');
            // $table->string('phone');
            $table->string('contact_number', 20)->nullable()->comment('Customer contact number');
            $table->string('email')->comment('Customer email');
            $table->date('appointment_date')->comment('Appointment date');
            $table->time('appointment_time')->comment('Appointment time');
            // $table->date('date');
            // $table->string('time');
            $table->enum('appointment_type', ['virtual', 'showroom'])->default('showroom')->comment('Type of appointment: Virtual or Showroom');
            $table->string('location')->nullable(); // showroom location
            $table->string('meeting_link')->nullable(); // added later by staff
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending');

            // Appointment Type (Virtual or Showroom)
            // $table->enum('appointment_type', ['virtual', 'showroom'])
            //       ->comment('Type of appointment: Virtual or Showroom');

            // Appointment Date & Time


            // Expanded Time Zone Options (India, USA, Australia + Europe Countries)
            $table->enum('time_zone', [
                'India',
                'USA',
                'Australia',
                'United Kingdom',
                'Germany',
                'France',
                'Italy',
                'Spain',
                'Netherlands',
                'Switzerland',
                'Sweden',
                'Norway',
                'Denmark',
                'Belgium',
                'Austria',
                'Ireland',
                'Finland',
                'Poland',
                'Portugal',
                'Greece'
            ])->nullable()->comment('User selected time zone or country');

            // Booking Time
            $table->string('today_time')->nullable()->comment('Time when booked');

            // Customer details
            // $table->string('name')->comment('Customer name');
            // $table->string('email')->comment('Customer email');
            // $table->string('guest_email')->nullable()->comment('Guest email (optional)');
            // $table->string('contact_number', 20)->nullable()->comment('Customer contact number');

            // Category selection
            $table->enum('category', [
                'Engagement Rings',
                'Wedding Bands',
                'Gifting Jewelry',
                'Studs',
                'Necklace',
                'Anniversary/Eternity',
                'Bracelets',
                'Haute',
                'Other'
            ])->comment('Selected jewelry category');

            // If "Other" selected, store custom input
            $table->string('other_category')->nullable()->comment('If category is Other');
            // Additional information
            $table->text('additional_information')->nullable()->comment('Additional notes from user');
            $table->timestamps();
        });
      /*   Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('provider_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('service_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->string('phone');
            $table->string('email')->nullable();
            $table->date('date');
            $table->string('time');
            $table->enum('appointment_type', ['virtual', 'showroom'])->default('showroom');
            $table->string('location')->nullable(); // showroom location
            $table->string('meeting_link')->nullable(); // added later by staff
            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending');
            $table->timestamps();
        }); */
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
