<?php

use App\Support\PpdbPermissions;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ppdb_selection_results', function (Blueprint $table): void {
            $table->timestamp('invalidated_at')->nullable()->after('finalized_by');
            $table->foreignId('invalidated_by')->nullable()->after('invalidated_at')->constrained('users')->nullOnDelete();
            $table->text('invalidation_reason')->nullable()->after('invalidated_by');
            $table->dropUnique('ppdb_selection_results_ppdb_application_id_unique');
            $table->index(['ppdb_application_id', 'invalidated_at']);
        });

        $permission = Permission::findOrCreate(PpdbPermissions::CANCEL_FINALIZATION, 'web');
        foreach (['Super Admin', 'Admin Sekolah'] as $roleName) {
            Role::findOrCreate($roleName, 'web')->givePermissionTo($permission);
        }
    }

    public function down(): void
    {
        foreach (['Super Admin', 'Admin Sekolah'] as $roleName) {
            $role = Role::query()->where('name', $roleName)->where('guard_name', 'web')->first();
            $role?->revokePermissionTo(PpdbPermissions::CANCEL_FINALIZATION);
        }

        Permission::query()
            ->where('name', PpdbPermissions::CANCEL_FINALIZATION)
            ->where('guard_name', 'web')
            ->delete();

        Schema::table('ppdb_selection_results', function (Blueprint $table): void {
            $table->dropIndex(['ppdb_application_id', 'invalidated_at']);
            $table->dropForeign(['invalidated_by']);
            $table->dropColumn(['invalidated_at', 'invalidated_by', 'invalidation_reason']);
            $table->unique('ppdb_application_id');
        });
    }
};
