<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting authorization
| channels that your application supports. The given channel
| authorization callbacks are used to check if an authenticated
| user can listen to the channel.
|
*/

// 用户私有通知频道
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// 公司频道 - 只有该公司成员可以监听
Broadcast::channel('company.{companyId}', function ($user, $companyId) {
    return (int) $user->company_id === (int) $companyId;
});

// 聊天室频道 - 只有参与者可以监听
Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    return $user->chats()->where('chat_id', $chatId)->exists();
});

// 项目频道 - 只有项目成员可以监听
Broadcast::channel('project.{projectId}', function ($user, $projectId) {
    return $user->projects()->where('project_id', $projectId)->exists()
        || $user->company->projects()->where('projects.id', $projectId)->exists();
});
