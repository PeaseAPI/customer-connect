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
            throw new \Exception('Access denied to this conversation');
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

        if ($conversation->messages()->count() <= 2 && $conversation->title === 'New Conversation') {
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
            $contextStr = "Current context: " . json_encode($conversation->context, JSON_UNESCAPED_UNICODE);
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
        return "You are the Customer Connect CRM AI assistant. You can help users with:
1. Querying and understanding CRM data (clients, projects, tasks, invoices, etc.)
2. Providing business advice and best practices
3. Generating reports and analysis
4. Answering system usage questions
Please respond in a concise and professional manner.";
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
                'content' => $data['choices'][0]['message']['content'] ?? 'Sorry, unable to generate a response.',
                'usage' => $data['usage'] ?? null,
            ];
        } catch (\Exception $e) {
            return ['content' => 'AI service is temporarily unavailable. Please try again later.', 'usage' => null];
        }
    }

    protected function generateMockResponse(array $messages): string
    {
        $lastUserMsg = collect($messages)->where('role', 'user')->last();
        $question = $lastUserMsg['content'] ?? '';

        if (str_contains(strtolower($question), 'client') || str_contains(strtolower($question), 'customer')) {
            return "For client management, you can use the following API endpoints:\n- GET /api/crm/clients - Get client list\n- POST /api/crm/clients - Create a new client";
        }
        if (str_contains(strtolower($question), 'project') || str_contains(strtolower($question), 'task')) {
            return "For project/task management:\n- GET /api/pm/projects - Project list\n- GET /api/pm/tasks/calendar - Task calendar view";
        }
        return "Hello! I'm the Customer Connect CRM AI assistant. How can I help you today?";
    }
}
