<?php

namespace App\Http\Controllers\Api;

use App\Services\Ai\AiAssistantService;
use Illuminate\Http\Request;

class AiAssistantController extends BaseApiController
{
    public function __construct(protected AiAssistantService $aiService) {}

    /**
     * 获取对话列表
     */
    public function index(Request $request)
    {
        $conversations = $this->aiService->listConversations(
            $request->user()->id,
            $request->per_page ?? 15
        );
        return $this->paginated($conversations);
    }

    /**
     * 创建新对话
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'model' => 'nullable|string|max:50',
            'context' => 'nullable|array',
        ]);

        $conversation = $this->aiService->createConversation(
            $request->user()->id,
            $validated['model'] ?? 'gpt-3.5-turbo',
            $validated['context'] ?? []
        );

        return $this->success($conversation, '对话创建成功', 201);
    }

    /**
     * 查看对话详情（含消息）
     */
    public function show(Request $request, int $conversation)
    {
        $conv = \App\Models\AiConversation::where('id', $conversation)
            ->where('user_id', $request->user()->id)
            ->with('messages')
            ->firstOrFail();

        return $this->success($conv);
    }

    /**
     * 发送消息
     */
    public function chat(Request $request, int $conversation)
    {
        $validated = $request->validate([
            'message' => 'required|string|max:5000',
        ]);

        $message = $this->aiService->chat(
            $conversation,
            $validated['message'],
            $request->user()->id
        );

        return $this->success($message->load('conversation'), 'Sent successfully');
    }

    /**
     * 快速问答
     */
    public function quickChat(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string|max:2000',
            'model' => 'nullable|string|max:50',
        ]);

        $answer = $this->aiService->quickChat(
            $validated['question'],
            $validated['model'] ?? 'gpt-3.5-turbo'
        );

        return $this->success(['answer' => $answer]);
    }

    /**
     * 删除对话
     */
    public function destroy(Request $request, int $conversation)
    {
        $this->aiService->deleteConversation($conversation, $request->user()->id);
        return $this->success(null, '对话已删除');
    }
}
