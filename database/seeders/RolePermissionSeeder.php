<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 清除缓存
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 创建权限 - 按模块分组
        $modules = [
            'hrm' => ['view', 'create', 'update', 'delete', 'manage'],
            'crm' => ['view', 'create', 'update', 'delete', 'manage'],
            'pm' => ['view', 'create', 'update', 'delete', 'manage'],
            'finance' => ['view', 'create', 'update', 'delete', 'manage'],
            'ticket' => ['view', 'create', 'update', 'delete', 'manage'],
            'approval' => ['view', 'create', 'approve', 'reject'],
            'setting' => ['view', 'update', 'manage'],
            'report' => ['view', 'export'],
            'notification' => ['view', 'manage'],
        ];

        $permissions = [];
        foreach ($modules as $module => $actions) {
            foreach ($actions as $action) {
                $permissions[] = "{$module}_{$action}";
            }
        }

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // 创建角色并分配权限
        $roles = [
            'super-admin' => $permissions, // 全部权限
            'admin' => $permissions,       // 全部权限
            'hr-manager' => array_merge(
                $this->getModulePermissions('hrm'),
                $this->getModulePermissions('approval', ['view', 'approve', 'reject']),
                $this->getModulePermissions('report'),
                $this->getModulePermissions('notification'),
            ),
            'sales-manager' => array_merge(
                $this->getModulePermissions('crm'),
                $this->getModulePermissions('finance', ['view', 'create', 'update']),
                $this->getModulePermissions('approval', ['view', 'create']),
                $this->getModulePermissions('report'),
                $this->getModulePermissions('notification'),
            ),
            'project-manager' => array_merge(
                $this->getModulePermissions('pm'),
                $this->getModulePermissions('approval', ['view', 'create']),
                $this->getModulePermissions('report'),
                $this->getModulePermissions('notification'),
            ),
            'finance-manager' => array_merge(
                $this->getModulePermissions('finance'),
                $this->getModulePermissions('approval', ['view', 'approve', 'reject']),
                $this->getModulePermissions('report'),
                $this->getModulePermissions('notification'),
            ),
            'employee' => array_merge(
                $this->getModulePermissions('hrm', ['view', 'update']),
                $this->getModulePermissions('pm', ['view', 'create', 'update']),
                $this->getModulePermissions('ticket', ['view', 'create']),
                $this->getModulePermissions('approval', ['view', 'create']),
                $this->getModulePermissions('notification', ['view']),
            ),
            'client' => array_merge(
                $this->getModulePermissions('ticket', ['view', 'create']),
                $this->getModulePermissions('finance', ['view']),
                $this->getModulePermissions('notification', ['view']),
            ),
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($rolePermissions);
        }

        $this->command->info('已创建 ' . count($permissions) . ' 个权限和 ' . count($roles) . ' 个角色');
    }

    private function getModulePermissions(string $module, array $actions = null): array
    {
        if ($actions) {
            return array_map(fn($action) => "{$module}_{$action}", $actions);
        }
        return array_map(fn($action) => "{$module}_{$action}",
            match($module) {
                'approval' => ['view', 'create', 'approve', 'reject'],
                'report' => ['view', 'export'],
                'notification' => ['view', 'manage'],
                default => ['view', 'create', 'update', 'delete', 'manage'],
            }
        );
    }
}
