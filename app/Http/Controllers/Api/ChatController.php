<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\StoreChatRequest;
use App\Http\Requests\UpdateChatRequest;
use App\Http\Requests\SendMessageRequest;
use App\Http\Requests\AddChatParticipantsRequest;
use App\Models\Chat;
use App\Services\Chat\ChatService;
use Illuminate\Http\Request;

class ChatController extends BaseApiController
{
    public function __construct(protected ChatService $chatService) {}

    public function index(Request $request)
    {
        $chats = $this->chatService->list($request->user()->id, $request->per_page ?? 15);
        return $this->paginated($chats);
    }

    public function store(StoreChatRequest $request)
    {
        $v = $request->validated();
        $v['created_by'] = $request->user()->id;
        $v['company_id'] = $request->attributes->get('company_id');
        $participantIds = $v['participant_ids'];
        unset($v['participant_ids']);
        $chat = $this->chatService->create($v, $participantIds);
        return $this->success($chat->load(['participants', 'creator']), '聊天创建成功', 201);
    }

    public function show(Chat $chat)
    {
        return $this->success($chat->load(['participants', 'creator', 'messages.user']));
    }

    public function update(UpdateChatRequest $request, Chat $chat)
    {
        $v = $request->validated();
        $chat = $this->chatService->update($chat, $v);
        return $this->success($chat->load(['participants', 'creator']), '更新成功');
    }

    public function destroy(Chat $chat)
    {
        $this->chatService->delete($chat);
        return $this->success(null, '删除成功');
    }

    public function messages(Request $request, Chat $chat)
    {
        $messages = $chat->messages()->with(['user'])->latest()->paginate($request->per_page ?? 50);
        return $this->paginated($messages);
    }

    public function sendMessage(SendMessageRequest $request, Chat $chat)
    {
        $v = $request->validated();
        $v['user_id'] = $request->user()->id;
        $v['company_id'] = $chat->company_id;
        $message = $this->chatService->sendMessage($chat, $v);
        return $this->success($message->load(['user']), '消息发送成功', 201);
    }

    public function addParticipants(AddChatParticipantsRequest $request, Chat $chat)
    {
        $v = $request->validated();
        $chat = $this->chatService->addParticipants($chat, $v['participant_ids']);
        return $this->success($chat->load(['participants']), '参与者添加成功');
    }

    public function removeParticipant(Request $request, Chat $chat, $userId)
    {
        $this->chatService->removeParticipant($chat, $userId);
        return $this->success(null, '参与者移除成功');
    }
}
