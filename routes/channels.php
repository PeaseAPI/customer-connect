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

// Private user notification channel
Broadcast::channel('user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});

// Company channel - only company members can listen
Broadcast::channel('company.{companyId}', function ($user, $companyId) {
    return (int) $user->company_id === (int) $companyId;
});

// Chat room channel - only participants can listen
Broadcast::channel('chat.{chatId}', function ($user, $chatId) {
    return $user->chats()->where('chat_id', $chatId)->exists();
});

// Project channel - only project members can listen
Broadcast::channel('project.{projectId}', function ($user, $projectId) {
    return $user->projects()->where('project_id', $projectId)->exists()
        || $user->company->projects()->where('projects.id', $projectId)->exists();
});
