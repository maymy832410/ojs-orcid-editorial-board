<?php

namespace APP\plugins\generic\orcidEditorialBoard\Schema;

use APP\plugins\generic\orcidEditorialBoard\OrcidEditorialBoardPlugin;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class EditorialBoardSchemaMigration extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            if (!Schema::hasTable('editorial_board_members')) {
                OrcidEditorialBoardPlugin::log('Creating table editorial_board_members');
                Schema::create('editorial_board_members', function (Blueprint $table) {
                    $table->bigIncrements('id');
                    $table->bigInteger('context_id');
                    $table->string('role', 255);
                    $table->string('email', 255);
                    $table->string('full_name', 255);
                    $table->string('scopus_id', 255)->nullable();
                    $table->string('orcid_id', 255)->nullable();
                    $table->string('google_scholar', 500)->nullable();
                    $table->string('photo_url', 500)->nullable();
                    $table->string('affiliation', 500)->nullable();
                    $table->integer('sort_order')->default(0);
                    $table->string('consent_token', 64)->nullable()->unique();
                    $table->dateTime('consent_token_expires')->nullable();
                    $table->dateTime('employment_added_at')->nullable();
                    $table->boolean('orcid_verified')->default(false);
                    $table->dateTime('orcid_verified_cached_at')->nullable();

                    $table->index(['context_id'], 'editorial_board_members_context');
                    $table->index(['orcid_id'], 'editorial_board_members_orcid');
                });
            }

            // Add new columns if they do not exist (for upgrades on existing installs)
            if (!Schema::hasColumn('editorial_board_members', 'country')) {
                OrcidEditorialBoardPlugin::log('Altering editorial_board_members to add country/openalex columns');
                Schema::table('editorial_board_members', function (Blueprint $table) {
                    $table->string('country', 2)->default('')->after('affiliation');
                });
            }
            if (!Schema::hasColumn('editorial_board_members', 'openalex_id')) {
                Schema::table('editorial_board_members', function (Blueprint $table) {
                    $table->string('openalex_id', 255)->nullable()->after('country');
                });
            }
            if (!Schema::hasColumn('editorial_board_members', 'openalex_keywords')) {
                Schema::table('editorial_board_members', function (Blueprint $table) {
                    $table->text('openalex_keywords')->nullable()->after('openalex_id');
                });
            }
            if (!Schema::hasColumn('editorial_board_members', 'openalex_fetched_at')) {
                Schema::table('editorial_board_members', function (Blueprint $table) {
                    $table->dateTime('openalex_fetched_at')->nullable()->after('openalex_keywords');
                });
            }
            if (!Schema::hasColumn('editorial_board_members', 'openalex_payload')) {
                Schema::table('editorial_board_members', function (Blueprint $table) {
                    $table->longText('openalex_payload')->nullable()->after('openalex_fetched_at');
                });
            }
            if (!Schema::hasColumn('editorial_board_members', 'openalex_affiliation')) {
                Schema::table('editorial_board_members', function (Blueprint $table) {
                    $table->text('openalex_affiliation')->nullable()->after('openalex_payload');
                });
            }
            if (!Schema::hasColumn('editorial_board_members', 'openalex_country')) {
                Schema::table('editorial_board_members', function (Blueprint $table) {
                    $table->string('openalex_country', 2)->nullable()->after('openalex_affiliation');
                });
            }

            // COI fields
            if (!Schema::hasColumn('editorial_board_members', 'coi_status')) {
                Schema::table('editorial_board_members', function (Blueprint $table) {
                    $table->string('coi_status', 20)->default('pending')->after('openalex_country');
                });
            }
            if (!Schema::hasColumn('editorial_board_members', 'coi_text')) {
                Schema::table('editorial_board_members', function (Blueprint $table) {
                    $table->text('coi_text')->nullable()->after('coi_status');
                });
            }
            if (!Schema::hasColumn('editorial_board_members', 'coi_declared_at')) {
                Schema::table('editorial_board_members', function (Blueprint $table) {
                    $table->dateTime('coi_declared_at')->nullable()->after('coi_text');
                });
            }
            if (!Schema::hasColumn('editorial_board_members', 'coi_token')) {
                Schema::table('editorial_board_members', function (Blueprint $table) {
                    $table->string('coi_token', 64)->nullable()->after('coi_declared_at');
                });
            }
            if (!Schema::hasColumn('editorial_board_members', 'coi_token_expires_at')) {
                Schema::table('editorial_board_members', function (Blueprint $table) {
                    $table->dateTime('coi_token_expires_at')->nullable()->after('coi_token');
                });
            }

            // Tenure fields
            if (!Schema::hasColumn('editorial_board_members', 'tenure_start')) {
                Schema::table('editorial_board_members', function (Blueprint $table) {
                    $table->date('tenure_start')->nullable()->after('coi_token_expires_at');
                });
            }
            if (!Schema::hasColumn('editorial_board_members', 'tenure_end')) {
                Schema::table('editorial_board_members', function (Blueprint $table) {
                    $table->date('tenure_end')->nullable()->after('tenure_start');
                });
            }
            if (!Schema::hasColumn('editorial_board_members', 'tenure_status')) {
                Schema::table('editorial_board_members', function (Blueprint $table) {
                    $table->string('tenure_status', 20)->default('active')->after('tenure_end');
                });
            }

            // Visibility toggle
            if (!Schema::hasColumn('editorial_board_members', 'is_visible')) {
                Schema::table('editorial_board_members', function (Blueprint $table) {
                    $table->boolean('is_visible')->default(true)->after('tenure_status');
                });
            }

            // Reminder tracking
            if (!Schema::hasColumn('editorial_board_members', 'last_reminder_sent_at')) {
                Schema::table('editorial_board_members', function (Blueprint $table) {
                    $table->dateTime('last_reminder_sent_at')->nullable()->after('is_visible');
                });
            }

            // ORCID Public API fields
            if (!Schema::hasColumn('editorial_board_members', 'orcid_access_token')) {
                Schema::table('editorial_board_members', function (Blueprint $table) {
                    $table->text('orcid_access_token')->nullable()->after('last_reminder_sent_at');
                });
            }
            if (!Schema::hasColumn('editorial_board_members', 'orcid_auth_name')) {
                Schema::table('editorial_board_members', function (Blueprint $table) {
                    $table->string('orcid_auth_name', 255)->nullable()->after('orcid_access_token');
                });
            }

            // Dispute / false-claim fields
            if (!Schema::hasColumn('editorial_board_members', 'status')) {
                Schema::table('editorial_board_members', function (Blueprint $table) {
                    $table->string('status', 30)->default('active')->after('orcid_auth_name');
                });
            }
            if (!Schema::hasColumn('editorial_board_members', 'report_token')) {
                Schema::table('editorial_board_members', function (Blueprint $table) {
                    $table->string('report_token', 64)->nullable()->after('status');
                });
            }
            if (!Schema::hasColumn('editorial_board_members', 'report_token_expires_at')) {
                Schema::table('editorial_board_members', function (Blueprint $table) {
                    $table->dateTime('report_token_expires_at')->nullable()->after('report_token');
                });
            }

            // Dispute window: 7-day window for member to dispute admin changes
            if (!Schema::hasColumn('editorial_board_members', 'dispute_expires_at')) {
                Schema::table('editorial_board_members', function (Blueprint $table) {
                    $table->dateTime('dispute_expires_at')->nullable()->after('report_token_expires_at');
                });
            }

            // Widen columns for encrypted data (v1.3 encryption support)
            // Encrypted values are "enc:" + base64(IV + ciphertext) ~200+ chars.
            if (Schema::hasColumn('editorial_board_members', 'email')) {
                Schema::table('editorial_board_members', function (Blueprint $table) {
                    $table->text('email')->nullable()->change();
                });
            }
            if (Schema::hasColumn('editorial_board_members', 'orcid_id')) {
                Schema::table('editorial_board_members', function (Blueprint $table) {
                    $table->text('orcid_id')->nullable()->change();
                });
            }

            // Previous ORCID: preserved when admin changes ORCID so original owner can still dispute
            if (!Schema::hasColumn('editorial_board_members', 'previous_orcid_id')) {
                Schema::table('editorial_board_members', function (Blueprint $table) {
                    $table->text('previous_orcid_id')->nullable()->after('dispute_expires_at');
                });
            }

            // Optional disputes log table
            if (!Schema::hasTable('editorial_board_disputes')) {
                OrcidEditorialBoardPlugin::log('Creating table editorial_board_disputes');
                Schema::create('editorial_board_disputes', function (Blueprint $table) {
                    $table->bigIncrements('id');
                    $table->bigInteger('member_id');
                    $table->string('orcid', 50)->nullable();
                    $table->string('type', 20); // dispute, remove
                    $table->text('details')->nullable();
                    $table->dateTime('created_at');

                    $table->index(['member_id'], 'editorial_board_disputes_member');
                });
            }
        });
    }

    public function down(): void
    {
        DB::transaction(function () {
            OrcidEditorialBoardPlugin::log('Dropping tables editorial_board_members and editorial_board_disputes (if exist)');
            Schema::dropIfExists('editorial_board_disputes');
            Schema::dropIfExists('editorial_board_members');
        });
    }
}
