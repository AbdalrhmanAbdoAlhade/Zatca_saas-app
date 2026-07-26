<?php

namespace App\Services;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use RuntimeException;

class RoleManagementService
{
    public function __construct(protected ActivityLogService $activityLog)
    {
    }

    /**
     * الأدوار المتاحة لليوزر الحالي: أدوار النظام الأساسية (زي company-owner)
     * + الأدوار المخصصة اللي شركته أنشأتها (لو company-owner)، أو أدوار
     * المنصة كلها (لو super-admin).
     */
    public function listFor(User $actingUser): Collection
    {
        if ($actingUser->hasRole('super-admin')) {
            return Role::withCount('users')->where('context', 'platform')->with('permissions')->orderBy('name')->get();
        }

        return Role::withCount('users')
            ->where('context', 'company')
            ->where(fn ($q) => $q->whereNull('company_id')->orWhere('company_id', $actingUser->company_id))
            ->with('permissions')
            ->orderBy('name')
            ->get();
    }

    public function listPermissions(): Collection
    {
        return Permission::orderBy('group')->orderBy('slug')->get()->groupBy('group');
    }

    public function create(array $data, User $actingUser): Role
    {
        $isPlatformRole = $actingUser->hasRole('super-admin');
        $companyId = $isPlatformRole ? null : $actingUser->company_id;

        $role = Role::create([
            'company_id' => $companyId,
            'context' => $isPlatformRole ? 'platform' : 'company',
            'name' => $data['name'],
            'name_ar' => $data['name_ar'] ?? null,
            'slug' => $data['slug'] ?? Str::slug($data['name']).'-'.Str::random(5),
            'description' => $data['description'] ?? null,
            'is_system' => false,
        ]);

        if (! empty($data['permission_ids'])) {
            $this->guardPermissionsAllowed($data['permission_ids'], $isPlatformRole);
            $role->permissions()->sync($data['permission_ids']);
        }

        $this->activityLog->log('created', 'roles', $role, null, [
            'name' => $role->name,
            'company_id' => $companyId,
        ], companyId: $companyId);

        return $role->load('permissions');
    }

    public function update(Role $role, array $data, User $actingUser): Role
    {
        $this->guardCanManage($role, $actingUser);

        // الأدوار الأساسية (is_system) ممنوع تغيير slug/company_id بتاعها -
        // بس المسموح تعديل الاسم والوصف والصلاحيات (وده بحذر شديد لأنه بيأثر
        // على كل اليوزرز اللي عليهم الدور ده في كل الشركات لو كان دور نظام).
        if ($role->is_system) {
            unset($data['slug']);
        }

        $role->update([
            'name' => $data['name'] ?? $role->name,
            'name_ar' => $data['name_ar'] ?? $role->name_ar,
            'slug' => $data['slug'] ?? $role->slug,
            'description' => $data['description'] ?? $role->description,
        ]);

        if (array_key_exists('permission_ids', $data)) {
            $this->guardPermissionsAllowed($data['permission_ids'], $actingUser->hasRole('super-admin'));
            $role->permissions()->sync($data['permission_ids']);
        }

        $this->activityLog->log('updated', 'roles', $role, null, $data, companyId: $role->company_id);

        return $role->fresh('permissions');
    }

    public function delete(Role $role, User $actingUser): void
    {
        $this->guardCanManage($role, $actingUser);

        if ($role->is_system) {
            throw new RuntimeException('cannot_delete_system_role');
        }

        if ($role->users()->exists()) {
            throw new RuntimeException('role_has_users_assigned');
        }

        $this->activityLog->log('deleted', 'roles', $role, ['name' => $role->name], null, companyId: $role->company_id);

        $role->delete();
    }

    /**
     * يمنع إنه يدير دور مش بتاعه: company-owner يقدر يدير بس الأدوار
     * المخصصة اللي شركته عملتها (مش أدوار النظام ومش أدوار شركة تانية).
     * super-admin يقدر يدير أي دور عام (company_id = null).
     */
    protected function guardCanManage(Role $role, User $actingUser): void
    {
        if ($actingUser->hasRole('super-admin')) {
            if ($role->context !== 'platform') {
                throw new RuntimeException('cannot_manage_company_role');
            }

            return;
        }

        if ($role->context !== 'company' || $role->company_id !== $actingUser->company_id) {
            throw new RuntimeException('cannot_manage_this_role');
        }

        if ($role->is_system) {
            throw new RuntimeException('cannot_edit_system_role');
        }
    }

    /**
     * company-owner ممنوع يحط صلاحيات admin_* على دور شركته (تسريب صلاحيات منصة).
     */
    protected function guardPermissionsAllowed(array $permissionIds, bool $isPlatformRole): void
    {
        if ($isPlatformRole) {
            return;
        }

        $hasPlatformPermission = Permission::whereIn('id', $permissionIds)
            ->where('group', 'like', 'admin_%')
            ->exists();

        if ($hasPlatformPermission) {
            throw new RuntimeException('platform_permissions_not_allowed');
        }
    }
}
