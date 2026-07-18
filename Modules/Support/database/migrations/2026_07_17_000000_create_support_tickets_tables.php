<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('support_tickets')) {
            Schema::create('support_tickets', function (Blueprint $table) {
                $table->increments('id');
                $table->string('ticket_number')->unique();
                $table->unsignedInteger('business_id');
                $table->unsignedInteger('user_id');
                $table->string('order_id')->nullable();
                $table->string('category');
                $table->string('status')->default('Open');
                $table->timestamps();

                $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        if (! Schema::hasTable('support_messages')) {
            Schema::create('support_messages', function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('ticket_id');
                $table->unsignedInteger('user_id');
                $table->text('message');
                $table->timestamps();

                $table->foreign('ticket_id')->references('id')->on('support_tickets')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // Seed default demo data if table is empty
        if (DB::table('support_tickets')->count() === 0) {
            $business = DB::table('business')->first();
            $adminUser = DB::table('users')->where('user_type', 'admin')->first() ?: DB::table('users')->first();
            $customerUser = DB::table('users')->where('user_type', 'customer')->first() ?: DB::table('users')->first();

            if ($business && $customerUser) {
                // Ticket 1: Open
                $ticket1Id = DB::table('support_tickets')->insertGetId([
                    'ticket_number' => 'TKT-8241',
                    'business_id' => $business->id,
                    'user_id' => $customerUser->id,
                    'order_id' => 'ORD-100201',
                    'category' => 'Delivery Issue',
                    'status' => 'Open',
                    'created_at' => now()->subDays(2),
                    'updated_at' => now()->subDays(2),
                ]);

                DB::table('support_messages')->insert([
                    [
                        'ticket_id' => $ticket1Id,
                        'user_id' => $customerUser->id,
                        'message' => 'The package has not arrived yet. Tracking says it is still in the warehouse.',
                        'created_at' => now()->subDays(2),
                        'updated_at' => now()->subDays(2),
                    ]
                ]);

                // Ticket 2: Resolved
                $ticket2Id = DB::table('support_tickets')->insertGetId([
                    'ticket_number' => 'TKT-3912',
                    'business_id' => $business->id,
                    'user_id' => $customerUser->id,
                    'order_id' => 'ORD-100202',
                    'category' => 'Billing Inquiry',
                    'status' => 'Resolved',
                    'created_at' => now()->subDays(5),
                    'updated_at' => now()->subDays(4),
                ]);

                DB::table('support_messages')->insert([
                    [
                        'ticket_id' => $ticket2Id,
                        'user_id' => $customerUser->id,
                        'message' => 'Double charged for the order shipping. Please review.',
                        'created_at' => now()->subDays(5),
                        'updated_at' => now()->subDays(5),
                    ],
                    [
                        'ticket_id' => $ticket2Id,
                        'user_id' => $adminUser ? $adminUser->id : $customerUser->id,
                        'message' => 'Hello! We reviewed the transaction and refunded the duplicate charge. Let us know if you need anything else.',
                        'created_at' => now()->subDays(4),
                        'updated_at' => now()->subDays(4),
                    ]
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('support_messages');
        Schema::dropIfExists('support_tickets');
    }
};
