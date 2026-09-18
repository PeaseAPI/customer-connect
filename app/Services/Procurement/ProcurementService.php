<?php

namespace App\Services\Procurement;

use App\Models\PurchaseRequest;
use App\Models\Vendor;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProcurementService
{
    // === 供应商 ===
    public function listVendors(array $filters = [], int $perPage = 15)
    {
        $query = Vendor::query();

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }
        if (!empty($filters['search'])) {
            $query->where('vendor_name', 'like', "%{$filters['search']}%");
        }
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function createVendor(array $data): Vendor
    {
        return Vendor::create($data);
    }

    public function updateVendor(Vendor $vendor, array $data): Vendor
    {
        $vendor->update($data);
        return $vendor->fresh();
    }

    public function deleteVendor(Vendor $vendor): bool
    {
        return $vendor->delete();
    }

    // === 采购申请 ===
    public function listPurchaseRequests(array $filters = [], int $perPage = 15)
    {
        $query = PurchaseRequest::with(['requester', 'vendor', 'approver']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['requested_by'])) {
            $query->where('requested_by', $filters['requested_by']);
        }
        if (!empty($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    public function createPurchaseRequest(array $data): PurchaseRequest
    {
        $data['request_number'] = 'PR-' . now()->format('Ymd') . '-' . str_pad(
            PurchaseRequest::whereDate('created_at', today())->count() + 1, 4, '0', STR_PAD_LEFT
        );

        // 计算总Amount
        $totalAmount = collect($data['items'] ?? [])->sum(function ($item) {
            return ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
        });
        $data['total_amount'] = round($totalAmount, 2);

        return PurchaseRequest::create($data);
    }

    public function updatePurchaseRequest(PurchaseRequest $request, array $data): PurchaseRequest
    {
        if (isset($data['items'])) {
            $totalAmount = collect($data['items'])->sum(function ($item) {
                return ($item['quantity'] ?? 0) * ($item['unit_price'] ?? 0);
            });
            $data['total_amount'] = round($totalAmount, 2);
        }

        $request->update($data);
        return $request->fresh();
    }

    public function deletePurchaseRequest(PurchaseRequest $request): bool
    {
        return $request->delete();
    }

    public function approvePurchaseRequest(PurchaseRequest $request, int $approverId): PurchaseRequest
    {
        if ($request->status !== 'pending') {
            throw new \Exception('Can only approve pending purchase requests');
        }

        $request->update([
            'status' => 'approved',
            'approved_by' => $approverId,
            'approved_at' => now(),
        ]);

        return $request->fresh();
    }

    public function rejectPurchaseRequest(PurchaseRequest $request, int $approverId): PurchaseRequest
    {
        if ($request->status !== 'pending') {
            throw new \Exception('Can only reject pending purchase requests');
        }

        $request->update([
            'status' => 'cancelled',
            'approved_by' => $approverId,
            'approved_at' => now(),
        ]);

        return $request->fresh();
    }
}
