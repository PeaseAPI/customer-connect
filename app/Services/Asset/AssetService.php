<?php

namespace App\Services\Asset;

use App\Models\Asset;
use App\Models\AssetMaintenanceRecord;
use Illuminate\Support\Facades\DB;

class AssetService
{
    public function list(array $filters = [], int $perPage = 15)
    {
        $query = Asset::with(['allocatedUser']);

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('asset_name', 'like', "%{$filters['search']}%")
                  ->orWhere('asset_code', 'like', "%{$filters['search']}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function create(array $data): Asset
    {
        return Asset::create($data);
    }

    public function update(Asset $asset, array $data): Asset
    {
        $asset->update($data);
        return $asset->fresh();
    }

    public function delete(Asset $asset): bool
    {
        return $asset->delete();
    }

    /**
     * Assign asset给用户
     */
    public function allocate(Asset $asset, int $userId): Asset
    {
        return DB::transaction(function () use ($asset, $userId) {
            $asset->update([
                'allocated_to' => $userId,
                'allocated_date' => now(),
                'return_date' => null,
                'status' => 'allocated',
            ]);
            return $asset->fresh();
        });
    }

    /**
     * 归还资产
     */
    public function returnAsset(Asset $asset): Asset
    {
        return DB::transaction(function () use ($asset) {
            $asset->update([
                'allocated_to' => null,
                'return_date' => now(),
                'status' => 'available',
            ]);
            return $asset->fresh();
        });
    }

    /**
     * 计算折旧
     */
    public function calculateDepreciation(Asset $asset): array
    {
        if (!$asset->purchase_date || !$asset->useful_life_months) {
            return ['current_value' => $asset->current_value, 'depreciation' => 0];
        }

        $monthsUsed = $asset->purchase_date->diffInMonths(now());
        $monthlyDepreciation = $asset->purchase_cost / $asset->useful_life_months;
        $totalDepreciation = min($monthlyDepreciation * $monthsUsed, $asset->purchase_cost);
        $currentValue = max($asset->purchase_cost - $totalDepreciation, 0);

        $asset->update(['current_value' => $currentValue]);

        return [
            'current_value' => round($currentValue, 2),
            'total_depreciation' => round($totalDepreciation, 2),
            'months_used' => $monthsUsed,
        ];
    }

    /**
     * 添加维护记录
     */
    public function addMaintenanceRecord(Asset $asset, array $data): AssetMaintenanceRecord
    {
        return AssetMaintenanceRecord::create(array_merge($data, [
            'company_id' => $asset->company_id,
            'asset_id' => $asset->id,
        ]));
    }

    public function listMaintenanceRecords(Asset $asset, int $perPage = 15)
    {
        return $asset->maintenanceRecords()->orderBy('created_at', 'desc')->paginate($perPage);
    }
}
