<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Also resolves circular FK dependencies:
     *  - business.owner_id → users.id
     *  - contacts.created_by → users.id
     */
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->increments('id');

                if (! Schema::hasColumn('users', 'user_type')) {
                    $table->string('user_type', 50)->nullable();
                }

                if (! Schema::hasColumn('users', 'first_name')) {
                    $table->string('first_name', 191);
                }

                if (! Schema::hasColumn('users', 'last_name')) {
                    $table->string('last_name', 191)->nullable();
                }

                if (! Schema::hasColumn('users', 'username')) {
                    $table->string('username', 191)->unique();
                }

                if (! Schema::hasColumn('users', 'email')) {
                    $table->string('email', 191)->nullable();
                }

                if (! Schema::hasColumn('users', 'password')) {
                    $table->string('password');
                }

                if (! Schema::hasColumn('users', 'business_id')) {
                    $table->unsignedInteger('business_id')->nullable();
                }

                if (! Schema::hasColumn('users', 'crm_contact_id')) {
                    $table->unsignedInteger('crm_contact_id')->nullable();
                }

                if (! Schema::hasColumn('users', 'location_id')) {
                    $table->integer('location_id')->nullable();
                }

                if (! Schema::hasColumn('users', 'status')) {
                    $table->enum('status', ['active', 'inactive', 'terminated'])->default('active');
                }

                if (! Schema::hasColumn('users', 'allow_login')) {
                    $table->tinyInteger('allow_login')->default(1);
                }

                if (! Schema::hasColumn('users', 'is_cmmsn_agnt')) {
                    $table->tinyInteger('is_cmmsn_agnt')->default(0);
                }

                if (! Schema::hasColumn('users', 'cmmsn_percent')) {
                    $table->decimal('cmmsn_percent', 5, 2)->default(0);
                }

                // HR / essentials fields
                if (! Schema::hasColumn('users', 'essentials_department_id')) {
                    $table->integer('essentials_department_id')->nullable();
                }

                if (! Schema::hasColumn('users', 'essentials_designation_id')) {
                    $table->integer('essentials_designation_id')->nullable();
                }

                if (! Schema::hasColumn('users', 'essentials_salary')) {
                    $table->decimal('essentials_salary', 20, 2)->nullable();
                }

                if (! Schema::hasColumn('users', 'dob')) {
                    $table->date('dob')->nullable();
                }

                if (! Schema::hasColumn('users', 'gender')) {
                    $table->string('gender', 20)->nullable();
                }

                if (! Schema::hasColumn('users', 'marital_status')) {
                    $table->string('marital_status', 50)->nullable();
                }

                if (! Schema::hasColumn('users', 'blood_group')) {
                    $table->string('blood_group', 10)->nullable();
                }

                if (! Schema::hasColumn('users', 'address_line_1')) {
                    $table->string('address_line_1', 191)->nullable();
                }

                if (! Schema::hasColumn('users', 'address_line_2')) {
                    $table->string('address_line_2', 191)->nullable();
                }

                if (! Schema::hasColumn('users', 'city')) {
                    $table->string('city', 191)->nullable();
                }

                if (! Schema::hasColumn('users', 'state')) {
                    $table->string('state', 191)->nullable();
                }

                if (! Schema::hasColumn('users', 'country')) {
                    $table->string('country', 191)->nullable();
                }

                if (! Schema::hasColumn('users', 'zip_code')) {
                    $table->string('zip_code', 20)->nullable();
                }

                if (! Schema::hasColumn('users', 'bank_details')) {
                    $table->text('bank_details')->nullable();
                }

                if (! Schema::hasColumn('users', 'id_proof_type')) {
                    $table->string('id_proof_type', 191)->nullable();
                }

                if (! Schema::hasColumn('users', 'id_proof_number')) {
                    $table->string('id_proof_number', 191)->nullable();
                }

                if (! Schema::hasColumn('users', 'custom_field_1')) {
                    $table->string('custom_field_1', 191)->nullable();
                }

                if (! Schema::hasColumn('users', 'custom_field_2')) {
                    $table->string('custom_field_2', 191)->nullable();
                }

                if (! Schema::hasColumn('users', 'custom_field_3')) {
                    $table->string('custom_field_3', 191)->nullable();
                }

                if (! Schema::hasColumn('users', 'custom_field_4')) {
                    $table->string('custom_field_4', 191)->nullable();
                }

                $table->rememberToken();
                $table->softDeletes();
                $table->timestamps();

                $table->foreign('business_id')->references('id')->on('business')->onDelete('cascade');
                $table->foreign('crm_contact_id')->references('id')->on('contacts')->onDelete('cascade');
            });
        }

        // Resolve circular dependency: add owner_id FK on business → users
        if (Schema::hasTable('business') && Schema::hasColumn('business', 'owner_id')) {
            $hasFK = collect(Schema::getForeignKeys('business'))->contains(fn ($fk) => $fk['name'] === 'business_owner_id_foreign');
            if (! $hasFK) {
                Schema::table('business', function (Blueprint $table) {
                    $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
                });
            }
        }

        // Resolve circular dependency: add created_by FK on contacts → users
        if (Schema::hasTable('contacts') && Schema::hasColumn('contacts', 'created_by')) {
            $hasFK = collect(Schema::getForeignKeys('contacts'))->contains(fn ($fk) => $fk['name'] === 'contacts_created_by_foreign');
            if (! $hasFK) {
                Schema::table('contacts', function (Blueprint $table) {
                    $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('contacts') && Schema::hasColumn('contacts', 'created_by')) {
            Schema::table('contacts', function (Blueprint $table) {
                $table->dropForeign(['created_by']);
            });
        }

        if (Schema::hasTable('business') && Schema::hasColumn('business', 'owner_id')) {
            Schema::table('business', function (Blueprint $table) {
                $table->dropForeign(['owner_id']);
            });
        }

        Schema::dropIfExists('users');
    }
};
