<?php

namespace App\Services\Ai;

use App\Models\AiConversation;
use App\Models\AiMessage;
use Illuminate\Http\Client\Factory as Http;
use Illuminate\Support\Facades\Context;

class AiAssistantService
{
    public function __construct(protected Http $http) {}

    public function createConversation(int $userId, string $model = 'gpt-3.5-turbo', array $context = []): AiConversation
    {
        $companyId = Context::get('current_company_id');
        return AiConversation::create([
            'company_id' => $companyId,
            'user_id' => $userId,
            'model' => $model,
            'context' => $context,
        ]);
    }

    public function chat(int $conversationId, string $userMessage, int $userId): AiMessage
    {
        $conversation = AiConversation::findOrFail($conversationId);

        if ($conversation->user_id !== $userId) {
            throw new \Exception('无权访问此对话');
        }

        AiMessage::create([
            'ai_conversation_id' => $conversationId,
            'role' => 'user',
            'content' => $userMessage,
        ]);

        $messages = $this->buildMessages($conversation);
        $response = $this->callAiApi($conversation->model, $messages);

        $assistantMessage = AiMessage::create([
            'ai_conversation_id' => $conversationId,
            'role' => 'assistant',
            'content' => $response['content'],
            'metadata' => [
                'model' => $conversation->model,
                'usage' => $response['usage'] ?? null,
            ],
        ]);

        if ($conversation->messages()->count() <= 2 && $conversation->title === '新对话') {
            $conversation->update([
                'title' => mb_substr($userMessage, 0, 50) . (mb_strlen($userMessage) > 50 ? '...' : ''),
            ]);
        }

        return $assistantMessage;
    }

    public function quickChat(string $question, string $model = 'gpt-3.5-turbo'): string
    {
        $messages = [
            ['role' => 'system', 'content' => $this->getSystemPrompt()],
            ['role' => 'user', 'content' => $question],
        ];
        $response = $this->callAiApi($model, $messages);
        return $response['content'];
    }

    public function listConversations(int $userId, int $perPage = 15)
    {
        return AiConversation::where('user_id', $userId)
            ->orderByDesc('updated_at')
            ->paginate($perPage);
    }

    public function deleteConversation(int $conversationId, int $userId): bool
    {
        $conversation = AiConversation::where('id', $conversationId)
            ->where('user_id', $userId)
            ->firstOrFail();
        $conversation->delete();
        return true;
    }

    protected function buildMessages(AiConversation $conversation): array
    {
        $messages = [
            ['role' => 'system', 'content' => $this->getSystemPrompt()],
        ];

        if ($conversation->context) {
            $contextStr = "当前上下文: " . json_encode($conversation->context, JSON_UNESCAPED_UNICODE);
            $messages[] = ['role' => 'system', 'content' => $contextStr];
        }

        $historyMessages = $conversation->messages()
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->reverse()
            ->values();

        foreach ($historyMessages as $msg) {
            $messages[] = ['role' => $msg->role, 'content' => $msg->content];
        }

        return $messages;
    }

    protected function getSystemPrompt(): string
    {
        return "你是 KHT CRM 系统的 AI 助手。你可以帮助用户：
1. 查询和理解 CRM 系统中的数据（客户、项目、任务、发票等）
2. 提供业务建议和最佳实践
3. 协助生成报告和分析
4. 解答系统使用问题
请用简洁专业的中文回答。";
    }

    protected function callAiApi(string $model, array $messages): array
    {
        $apiKey = config('services.openai.api_key');

        if (!$apiKey) {
            return ['content' => $this->generateMockResponse($messages), 'usage' => ['total_tokens' => 0]];
        }

        try {
            $response = $this->http->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => $model,
                'messages' => $messages,
                'max_tokens' => 2000,
                'temperature' => 0.7,
            ]);

            $data = $response->json();
            return [
                'content' => $data['choices'][0]['message']['content'] ?? '抱歉，无法生成回复。',
                'usage' => $data['usage'] ?? null,
            ];
        } catch (\Exception $e) {
            return ['content' => 'AI 服务暂时不可用，请稍后再试。', 'usage' => null];
        }
    }

    protected function generateMockResponse(array $messages): string
    {
        $lastUserMsg = collect($messages)->where('role', 'user')->last();
        $question = $lastUserMsg['content'] ?? '';

        if (str_contains($question, '客户')) {
            return "关于客户管理，您可以通过以下 API 端点操作：\n- GET /api/crm/clients - 获取客户列表\n- POST /api/crm/clients - 创建新客户";
        }
        if (str_contains($question, '项目') || str_contains($question, '任务')) {
            return "关于项目/任务管理：\n- GET /api/pm/projects - 项目列表\n- GET /api/pm/tasks/calendar - 任务日历视图";
        }
        return "您好！我是 KHT CRM 的 AI 助手。请告诉我您需要什么帮助？";
    }
}
