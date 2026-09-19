<?php

namespace App\Providers;

use App\Events\AppreciationGiven;
use App\Events\BirthdayReminder;
use App\Events\ChatMention;
use App\Events\ContractExpiringSoon;
use App\Events\ContractSignedNotify;
use App\Events\ContractStatusChanged;
use App\Events\DailyScheduleReminder;
use App\Events\DealStatusChanged;
use App\Events\DiscussionCreated;
use App\Events\DiscussionReplyAdded;
use App\Events\EstimateAccepted;
use App\Events\EstimateDeclined;
use App\Events\EventInviteSent;
use App\Events\EventReminderSent;
use App\Events\InvoiceReminder;
use App\Events\InvoiceReminderAfter;
use App\Events\InvoiceUpdated;
use App\Events\InvoiceCreated;
use App\Events\LateClockInRecorded;
use App\Events\LeaveRequested;
use App\Events\LeaveStatusChanged;
use App\Events\LeadConverted;
use App\Events\NewChatMessage;
use App\Events\NewCompanyRegistered;
use App\Events\NewExpenseRecurring;
use App\Events\NewInvoiceRecurring;
use App\Events\NewNotice;
use App\Events\NewOrderPlaced;
use App\Events\NewProjectMember;
use App\Events\NewProposalCreated;
use App\Events\NewUserRegistered;
use App\Events\OrderUpdatedNotify;
use App\Events\PaymentReceived;
use App\Events\ProjectNoteAdded;
use App\Events\ProjectReminder;
use App\Events\PromotionAdded;
use App\Events\SubTaskCompleted;
use App\Events\TaskAssigned;
use App\Events\TaskCommentAdded;
use App\Events\TaskCommentMention;
use App\Events\TaskReminder;
use App\Events\TicketReplyAdded;
use App\Events\TicketStatusChanged;
use App\Events\TwoFactorCodeSent;
use App\Events\WeeklyTimesheetSubmitted;
use App\Listeners\SendAppreciationGivenNotification;
use App\Listeners\SendBirthdayReminderNotification;
use App\Listeners\SendChatMentionNotification;
use App\Listeners\SendContractExpiringSoonNotification;
use App\Listeners\SendContractSignedNotifyNotification;
use App\Listeners\SendContractStatusNotification;
use App\Listeners\SendDailyScheduleReminderNotification;
use App\Listeners\SendDealStatusChangedNotification;
use App\Listeners\SendDiscussionCreatedNotification;
use App\Listeners\SendDiscussionReplyAddedNotification;
use App\Listeners\SendEstimateAcceptedNotification;
use App\Listeners\SendEstimateDeclinedNotification;
use App\Listeners\SendEventInviteSentNotification;
use App\Listeners\SendEventReminderSentNotification;
use App\Listeners\SendInvoiceReminderAfterNotification;
use App\Listeners\SendInvoiceReminderNotification;
use App\Listeners\SendInvoiceUpdatedNotification;
use App\Listeners\SendLateClockInRecordedNotification;
use App\Listeners\SendLeaveRequestNotification;
use App\Listeners\SendLeaveStatusNotification;
use App\Listeners\SendNewChatMessageNotification;
use App\Listeners\SendNewCompanyRegisteredNotification;
use App\Listeners\SendNewExpenseRecurringNotification;
use App\Listeners\SendNewInvoiceRecurringNotification;
use App\Listeners\SendNewNoticeNotification;
use App\Listeners\SendNewOrderPlacedNotification;
use App\Listeners\SendNewProjectMemberNotification;
use App\Listeners\SendNewProposalCreatedNotification;
use App\Listeners\SendNewUserRegisteredNotification;
use App\Listeners\SendOrderUpdatedNotifyNotification;
use App\Listeners\SendPaymentReceivedNotification;
use App\Listeners\SendProjectNoteAddedNotification;
use App\Listeners\SendProjectReminderNotification;
use App\Listeners\SendPromotionAddedNotification;
use App\Listeners\SendSubTaskCompletedNotification;
use App\Listeners\SendTaskAssignedNotification;
use App\Listeners\SendTaskCommentAddedNotification;
use App\Listeners\SendTaskCommentMentionNotification;
use App\Listeners\SendTaskReminderNotification;
use App\Listeners\SendTicketReplyAddedNotification;
use App\Listeners\SendTicketStatusChangedNotification;
use App\Listeners\SendTwoFactorCodeSentNotification;
use App\Listeners\SendWeeklyTimesheetSubmittedNotification;
use App\Models\Contract;
use App\Models\Task;
use App\Observers\ContractObserver;
use App\Observers\TaskObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        TaskAssigned::class => [SendTaskAssignedNotification::class],
        TaskReminder::class => [SendTaskReminderNotification::class],
        TaskCommentAdded::class => [SendTaskCommentAddedNotification::class],
        TaskCommentMention::class => [SendTaskCommentMentionNotification::class],
        SubTaskCompleted::class => [SendSubTaskCompletedNotification::class],
        ProjectReminder::class => [SendProjectReminderNotification::class],
        ProjectNoteAdded::class => [SendProjectNoteAddedNotification::class],
        NewProjectMember::class => [SendNewProjectMemberNotification::class],
        InvoiceReminder::class => [SendInvoiceReminderNotification::class],
        InvoiceReminderAfter::class => [SendInvoiceReminderAfterNotification::class],
        InvoiceUpdated::class => [SendInvoiceUpdatedNotification::class],
        NewInvoiceRecurring::class => [SendNewInvoiceRecurringNotification::class],
        NewExpenseRecurring::class => [SendNewExpenseRecurringNotification::class],
        LeaveRequested::class => [SendLeaveRequestNotification::class],
        LeaveStatusChanged::class => [SendLeaveStatusNotification::class],
        ContractStatusChanged::class => [SendContractStatusNotification::class],
        ContractExpiringSoon::class => [SendContractExpiringSoonNotification::class],
        ContractSignedNotify::class => [SendContractSignedNotifyNotification::class],
        PaymentReceived::class => [SendPaymentReceivedNotification::class],
        NewNotice::class => [SendNewNoticeNotification::class],
        NewChatMessage::class => [SendNewChatMessageNotification::class],
        ChatMention::class => [SendChatMentionNotification::class],
        AppreciationGiven::class => [SendAppreciationGivenNotification::class],
        PromotionAdded::class => [SendPromotionAddedNotification::class],
        BirthdayReminder::class => [SendBirthdayReminderNotification::class],
        DailyScheduleReminder::class => [SendDailyScheduleReminderNotification::class],
        EventReminderSent::class => [SendEventReminderSentNotification::class],
        EventInviteSent::class => [SendEventInviteSentNotification::class],
        LateClockInRecorded::class => [SendLateClockInRecordedNotification::class],
        DealStatusChanged::class => [SendDealStatusChangedNotification::class],
        NewProposalCreated::class => [SendNewProposalCreatedNotification::class],
        NewOrderPlaced::class => [SendNewOrderPlacedNotification::class],
        OrderUpdatedNotify::class => [SendOrderUpdatedNotifyNotification::class],
        TicketStatusChanged::class => [SendTicketStatusChangedNotification::class],
        TicketReplyAdded::class => [SendTicketReplyAddedNotification::class],
        DiscussionCreated::class => [SendDiscussionCreatedNotification::class],
        DiscussionReplyAdded::class => [SendDiscussionReplyAddedNotification::class],
        NewUserRegistered::class => [SendNewUserRegisteredNotification::class],
        NewCompanyRegistered::class => [SendNewCompanyRegisteredNotification::class],
        EstimateAccepted::class => [SendEstimateAcceptedNotification::class],
        EstimateDeclined::class => [SendEstimateDeclinedNotification::class],
        WeeklyTimesheetSubmitted::class => [SendWeeklyTimesheetSubmittedNotification::class],
        TwoFactorCodeSent::class => [SendTwoFactorCodeSentNotification::class],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        Task::observe(TaskObserver::class);
        Contract::observe(ContractObserver::class);
    }
}
