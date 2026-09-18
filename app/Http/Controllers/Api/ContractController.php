<?php

namespace App\Http\Controllers\Api;

use App\Models\Contract;
use App\Services\CRM\ContractService;
use Illuminate\Http\Request;

class ContractController extends BaseApiController
{
    public function __construct(protected ContractService $contractService) {}

    public function index(Request $request)
    {
        $contracts = $this->contractService->list($request->all(), $request->per_page ?? 15);
        return $this->paginated($contracts);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id' => 'required|exists:users,id',
            'subject' => 'required|string|max:191',
            'contract_type_id' => 'nullable|exists:contract_types,id',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'value' => 'nullable|numeric',
            'currency_id' => 'nullable|exists:currencies,id',
            'description' => 'nullable|string',
            'project_id' => 'nullable|exists:projects,id',
            'company_address_id' => 'nullable|exists:company_addresses,id',
        ]);

        $validated['company_id'] = $request->attributes->get('company_id');
        $validated['created_by'] = $request->user()->id;

        $contract = $this->contractService->create($validated);

        return $this->success($contract->load(['client', 'contractType', 'currency', 'creator']), '合同创建成功', 201);
    }

    public function show(Contract $contract)
    {
        return $this->success($contract->load([
            'client', 'contractType', 'currency', 'creator', 'project',
            'signature', 'discussions.creator', 'renewHistory.creator',
        ]));
    }

    public function update(Request $request, Contract $contract)
    {
        $validated = $request->validate([
            'subject' => 'sometimes|string|max:191',
            'status' => 'sometimes|in:draft,active,expired,canceled',
            'start_date' => 'sometimes|date',
            'end_date' => 'nullable|date|after:start_date',
            'value' => 'nullable|numeric',
            'currency_id' => 'nullable|exists:currencies,id',
            'description' => 'nullable|string',
            'contract_type_id' => 'nullable|exists:contract_types,id',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        if (isset($validated['status'])) {
            $contract = $this->contractService->changeStatus($contract, $validated['status']);
            unset($validated['status']);
        }

        if (!empty($validated)) {
            $contract = $this->contractService->update($contract, $validated);
        }

        return $this->success($contract->load(['client', 'contractType', 'currency']), '更新成功');
    }

    public function destroy(Contract $contract)
    {
        $this->contractService->delete($contract);
        return $this->success(null, '删除成功');
    }

    public function renew(Request $request, Contract $contract)
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'amount' => 'nullable|numeric',
            'note' => 'nullable|string',
        ]);

        $contract = $this->contractService->renew($contract, $validated, $request->user()->id);

        return $this->success($contract->load(['renewHistory']), '续约成功');
    }

    /**
     * 上传合同文件
     */
    public function uploadFile(Request $request, Contract $contract)
    {
        $validated = $request->validate([
            'file' => 'required|file|max:20480',
        ]);

        $uploadedFile = $validated['file'];
        $path = $uploadedFile->store("contract_files/{$contract->id}", 'local');

        $file = $contract->files()->create([
            'company_id' => $contract->company_id,
            'user_id' => $request->user()->id,
            'filename' => $uploadedFile->getClientOriginalName(),
            'hashname' => basename($path),
            'size' => $uploadedFile->getSize(),
            'disk' => 'local',
            'path' => $path,
        ]);

        return $this->success($file, '文件上传成功', 201);
    }

    /**
     * 获取合同文件列表
     */
    public function listFiles(Contract $contract)
    {
        return $this->success($contract->files()->with('user')->orderBy('created_at', 'desc')->get(), '获取成功');
    }

    /**
     * 删除合同文件
     */
    public function deleteFile(Contract $contract, $fileId)
    {
        $file = $contract->files()->where('id', $fileId)->first();
        if (!$file) {
            return $this->error('文件不存在', 404);
        }

        \Illuminate\Support\Facades\Storage::disk($file->disk)->delete($file->path);
        $file->delete();

        return $this->success(null, '文件删除成功');
    }

    /**
     * 添加合同讨论
     */
    public function addDiscussion(Request $request, Contract $contract)
    {
        $validated = $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        $discussion = $contract->discussions()->create([
            'company_id' => $contract->company_id,
            'content' => $validated['content'],
            'created_by' => $request->user()->id,
        ]);

        return $this->success($discussion->load('creator'), '讨论添加成功', 201);
    }

    /**
     * 获取合同讨论列表
     */
    public function listDiscussions(Contract $contract)
    {
        return $this->success(
            $contract->discussions()->with('creator')->orderBy('created_at', 'desc')->paginate(15),
            '获取成功'
        );
    }
}
