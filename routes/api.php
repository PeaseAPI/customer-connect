<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\TaskController;
use App\Http\Controllers\Api\EmployeeController;
use App\Http\Controllers\Api\ClientController;
use App\Http\Controllers\Api\LeadController;
use App\Http\Controllers\Api\InvoiceController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ExpenseController;
use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\LeaveController;
use App\Http\Controllers\Api\TicketController;
use App\Http\Controllers\Api\ContractController;
use App\Http\Controllers\Api\ApprovalController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\LeaveTypeController;
use App\Http\Controllers\Api\ShiftController;
use App\Http\Controllers\Api\HolidayController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ExpenseCategoryController;
use App\Http\Controllers\Api\ContractTypeController;
use App\Http\Controllers\Api\DealController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ProposalController;
use App\Http\Controllers\Api\PipelineController;
use App\Http\Controllers\Api\NoticeController;
use App\Http\Controllers\Api\DiscussionController;
use App\Http\Controllers\Api\BankAccountController;
use App\Http\Controllers\Api\CreditNoteController;
use App\Http\Controllers\Api\SubTaskController;
use App\Http\Controllers\Api\TaskCategoryController;
use App\Http\Controllers\Api\ProjectCategoryController;
use App\Http\Controllers\Api\AppreciationController;
use App\Http\Controllers\Api\KnowledgeBaseController;
use App\Http\Controllers\Api\CompanyAddressController;
use App\Http\Controllers\Api\ContractTemplateController;
use App\Http\Controllers\Api\ContractRenewHistoryController;
use App\Http\Controllers\Api\ContractDiscussionController;
use App\Http\Controllers\Api\ContractSignatureController;
use App\Http\Controllers\Api\ClientContactController;
use App\Http\Controllers\Api\ClientNoteController;
use App\Http\Controllers\Api\ClientDocumentController;
use App\Http\Controllers\Api\ClientCategoryController;
use App\Http\Controllers\Api\ClientSubCategoryController;
use App\Http\Controllers\Api\EmployeeVisaController;
use App\Http\Controllers\Api\EmployeeDocumentExpiryController;
use App\Http\Controllers\Api\EmergencyContactController;
use App\Http\Controllers\Api\PromotionController;
use App\Http\Controllers\Api\ProductCategoryController;
use App\Http\Controllers\Api\ProductSubCategoryController;
use App\Http\Controllers\Api\StickyNoteController;
use App\Http\Controllers\Api\TaskCommentController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\RecurringInvoiceController;
use App\Http\Controllers\Api\GdprSettingController;
use App\Http\Controllers\Api\PurposeConsentController;
use App\Http\Controllers\Api\AwardController;
use App\Http\Controllers\Api\ProjectMilestoneController;
use App\Http\Controllers\Api\ProjectTimeLogController;
use App\Http\Controllers\Api\EmployeeShiftScheduleController;
use App\Http\Controllers\Api\EmployeeShiftChangeRequestController;
use App\Http\Controllers\Api\EmployeeLeaveQuotaController;
use App\Http\Controllers\Api\EstimateRequestController;
use App\Http\Controllers\Api\ExpenseRecurringController;
use App\Http\Controllers\Api\UnitTypeController;
use App\Http\Controllers\Api\TaxController;
use App\Http\Controllers\Api\OfflinePaymentMethodController;
use App\Http\Controllers\Api\InvoiceSettingController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\NotificationSettingController;
use App\Http\Controllers\Api\ProjectTemplateController;
use App\Http\Controllers\Api\EmailTemplateController;
use App\Http\Controllers\Api\EmailLogController;
use App\Http\Controllers\Api\SmsTemplateController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\DeviceTokenController;
use Illuminate\Support\Facades\Route;

// Public API (no auth required)
Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::post('send-sms-code', [AuthController::class, 'sendSmsCode']);
    Route::post('login-with-sms', [AuthController::class, 'loginWithSms']);
    Route::post('login-with-wechat', [AuthController::class, 'loginWithWechat']);
    Route::post('login-with-dingtalk', [AuthController::class, 'loginWithDingtalk']);
    Route::post('login-with-feishu', [AuthController::class, 'loginWithFeishu']);
    Route::post('login-with-wework', [AuthController::class, 'loginWithWework']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password', [AuthController::class, 'resetPassword']);
    Route::post('verify-2fa', [AuthController::class, 'verify2fa']);
    Route::post('register-with-invite', [AuthController::class, 'registerWithInvite']);
});

// Employee invite acceptance (no auth required)
Route::post('accept-employee-invite', [AuthController::class, 'acceptEmployeeInvite']);

// ===== API Key Auth Routes (via X-API-Key header) =====
Route::middleware(['api_key', 'company'])->prefix('external')->group(function () {
    Route::get('me', function (\Illuminate\Http\Request $request) {
        return response()->json([
            'user' => $request->user(),
            'company_id' => $request->attributes->get('company_id'),
        ]);
    });
    Route::apiResource('clients', \App\Http\Controllers\Api\ClientController::class)->only(['index', 'show']);
    Route::apiResource('projects', \App\Http\Controllers\Api\ProjectController::class)->only(['index', 'show']);
    Route::apiResource('invoices', \App\Http\Controllers\Api\InvoiceController::class)->only(['index', 'show']);
    Route::apiResource('leads', \App\Http\Controllers\Api\LeadController::class)->only(['index', 'show']);
});

// Authenticated API
Route::middleware(['auth:sanctum', 'company', 'subscription'])->group(function () {
    Route::prefix('user')->group(function () {
        Route::get('profile', [AuthController::class, 'profile']);
        Route::put('profile', [AuthController::class, 'updateProfile']);
        Route::put('password', [AuthController::class, 'changePassword']);
        Route::post('logout', [AuthController::class, 'logout']);
        Route::post('logout-all', [AuthController::class, 'logoutAll']);

        // Two-factor authentication
        Route::post('2fa/enable', [AuthController::class, 'enable2fa']);
        Route::post('2fa/confirm', [AuthController::class, 'confirm2fa']);
        Route::post('2fa/disable', [AuthController::class, 'disable2fa']);

        // Email verification
        Route::post('email/send-verification', [AuthController::class, 'sendEmailVerification']);
        Route::post('email/verify', [AuthController::class, 'verifyEmail']);

        // Multi-company switching
        Route::get('companies', [AuthController::class, 'companies']);
        Route::post('switch-company', [AuthController::class, 'switchCompany']);

        // Invite registration
        Route::post('invite', [AuthController::class, 'inviteUser']);
    });

    // Admin manual email verification
    Route::post('users/{user}/verify-email', [AuthController::class, 'adminVerifyEmail']);
        Route::get('dashboard', [DashboardController::class, 'index']);
    Route::get('dashboard/chart', [DashboardController::class, 'chartData']);
    Route::apiResource('companies', CompanyController::class);

    // HRM Module
    Route::middleware('module:hrm')->prefix('hrm')->group(function () {
        Route::apiResource('employees', EmployeeController::class);
        Route::post('employees/invite', [EmployeeController::class, 'invite']);
        Route::post('employees/import', [EmployeeController::class, 'import']);
        Route::apiResource('departments', \App\Http\Controllers\Api\DepartmentController::class);
        Route::apiResource('designations', \App\Http\Controllers\Api\DesignationController::class);
        Route::apiResource('attendances', AttendanceController::class);
        Route::post('attendances/clock-in', [AttendanceController::class, 'clockIn']);
        Route::post('attendances/clock-out', [AttendanceController::class, 'clockOut']);
        Route::apiResource('leaves', LeaveController::class);
        Route::post('leaves/{leave}/approve', [LeaveController::class, 'approve']);
                Route::post('leaves/{leave}/reject', [LeaveController::class, 'reject']);
        Route::apiResource('leave-types', LeaveTypeController::class);
        Route::apiResource('shifts', ShiftController::class);
                Route::apiResource('holidays', HolidayController::class);
                Route::apiResource('appreciations', AppreciationController::class);
        Route::apiResource('promotions', PromotionController::class);
        Route::apiResource('document-expiries', EmployeeDocumentExpiryController::class)->only(['index', 'show', 'update', 'destroy']);
        Route::apiResource('employees.visas', EmployeeVisaController::class);
                Route::apiResource('employees.emergency-contacts', EmergencyContactController::class);
        // Awards
        Route::apiResource('awards', AwardController::class);
        // Employee Shift Schedules
        Route::apiResource('employees.shift-schedules', EmployeeShiftScheduleController::class);
        // Shift Change Requests
        Route::apiResource('shift-change-requests', EmployeeShiftChangeRequestController::class)->except(['update']);
        Route::post('shift-change-requests/{shiftChangeRequest}/approve', [EmployeeShiftChangeRequestController::class, 'approve']);
        Route::post('shift-change-requests/{shiftChangeRequest}/reject', [EmployeeShiftChangeRequestController::class, 'reject']);
        // Employee Leave Quotas
        Route::apiResource('employees.leave-quotas', EmployeeLeaveQuotaController::class);
        Route::post('employees/{employee}/leave-quotas/{leaveQuota}/adjust', [EmployeeLeaveQuotaController::class, 'adjust']);
    });

    // CRM Module
    Route::middleware('module:crm')->prefix('crm')->group(function () {
                Route::apiResource('clients', ClientController::class);
        Route::get('clients/export', [ClientController::class, 'export']);
        Route::post('clients/import', [ClientController::class, 'import']);
        Route::post('clients/batch-delete', [ClientController::class, 'batchDelete']);
        Route::post('clients/batch-change-category', [ClientController::class, 'batchChangeCategory']);
        Route::post('clients/batch-change-status', [ClientController::class, 'batchChangeStatus']);
                Route::apiResource('leads', LeadController::class);
        Route::post('leads/{lead}/convert', [LeadController::class, 'convert']);
        Route::get('leads/export', [LeadController::class, 'export']);
        Route::post('leads/import', [LeadController::class, 'import']);
        Route::get('lead-stages', [LeadController::class, 'stages']);
        Route::apiResource('leads.follow-ups', \App\Http\Controllers\Api\LeadFollowUpController::class)->only(['index', 'store', 'update', 'destroy']);
                Route::apiResource('contacts', \App\Http\Controllers\Api\LeadContactController::class);
                Route::apiResource('products', ProductController::class);
        Route::apiResource('product-categories', ProductCategoryController::class);
        Route::apiResource('product-categories.sub-categories', ProductSubCategoryController::class);
        Route::apiResource('client-categories', ClientCategoryController::class);
        Route::apiResource('client-categories.sub-categories', ClientSubCategoryController::class);
        Route::apiResource('clients.contacts', ClientContactController::class);
        Route::apiResource('clients.notes', ClientNoteController::class);
        Route::apiResource('clients.documents', ClientDocumentController::class);
        Route::apiResource('deals', DealController::class);
        Route::post('deals/{deal}/change-stage', [DealController::class, 'changeStage']);
        Route::post('deals/{deal}/notes', [DealController::class, 'storeNote']);
        Route::get('deals/{deal}/history', [DealController::class, 'history']);
        Route::apiResource('pipelines', PipelineController::class);
        Route::post('pipelines/{pipeline}/stages', [PipelineController::class, 'storeStage']);
        Route::put('pipeline-stages/{stage}', [PipelineController::class, 'updateStage']);
        Route::delete('pipeline-stages/{stage}', [PipelineController::class, 'destroyStage']);
        Route::apiResource('proposals', ProposalController::class);
        Route::post('proposals/{proposal}/send', [ProposalController::class, 'send']);
        Route::post('proposals/{proposal}/convert-to-invoice', [ProposalController::class, 'convertToInvoice']);
        // Estimate Requests
        Route::apiResource('estimate-requests', EstimateRequestController::class);
        Route::post('estimate-requests/{estimateRequest}/convert', [EstimateRequestController::class, 'convert']);
    });

    // PM Module
    Route::middleware('module:pm')->prefix('pm')->group(function () {
        Route::apiResource('projects', ProjectController::class);
        Route::post('projects/{project}/members', [ProjectController::class, 'addMember']);
        Route::delete('projects/{project}/members/{user}', [ProjectController::class, 'removeMember']);
                Route::apiResource('projects.tasks', TaskController::class);
        Route::post('tasks/reorder', [TaskController::class, 'reorder']);
        Route::post('tasks/{task}/toggle-pin', [TaskController::class, 'togglePin']);
        Route::get('tasks/calendar', [TaskController::class, 'calendar']);
        Route::post('tasks/{task}/files', [TaskController::class, 'uploadFile']);
        Route::get('tasks/{task}/files', [TaskController::class, 'listFiles']);
        Route::delete('tasks/{task}/files/{fileId}', [TaskController::class, 'deleteFile']);
        // Project Templates
        Route::apiResource('project-templates', ProjectTemplateController::class);
        Route::post('projects/{project}/create-template', [ProjectTemplateController::class, 'createFromProject']);
        Route::post('project-templates/{projectTemplate}/create-project', [ProjectTemplateController::class, 'createProject']);
        Route::apiResource('milestones', \App\Http\Controllers\Api\MilestoneController::class);
        Route::apiResource('timelogs', \App\Http\Controllers\Api\TimelogController::class);
        Route::apiResource('task-categories', TaskCategoryController::class);
        Route::apiResource('project-categories', ProjectCategoryController::class);
        Route::apiResource('tasks.comments', TaskCommentController::class);
        Route::get('tasks/{task}/sub-tasks', [SubTaskController::class, 'index']);
        Route::post('tasks/{task}/sub-tasks', [SubTaskController::class, 'store']);
        Route::put('sub-tasks/{subTask}', [SubTaskController::class, 'update']);
        Route::delete('sub-tasks/{subTask}', [SubTaskController::class, 'destroy']);
        // Project Milestones
        Route::apiResource('projects.milestones', ProjectMilestoneController::class);
        // Project Time Logs
        Route::apiResource('projects.time-logs', ProjectTimeLogController::class);
        Route::post('projects/{project}/time-logs/{timeLog}/start-break', [ProjectTimeLogController::class, 'startBreak']);
        Route::post('projects/{project}/time-logs/{timeLog}/breaks/{breakLog}/end', [ProjectTimeLogController::class, 'endBreak']);
    });

    // Finance Module
    Route::middleware('module:finance')->prefix('finance')->group(function () {
                Route::apiResource('invoices', InvoiceController::class);
                Route::post('invoices/{invoice}/send', [InvoiceController::class, 'send']);
        Route::post('invoices/{invoice}/cancel', [InvoiceController::class, 'cancel']);
        Route::post('invoices/{invoice}/record-payment', [InvoiceController::class, 'recordPayment']);
        Route::apiResource('invoices.payments', PaymentController::class)->only(['index', 'store']);
        Route::apiResource('payments', PaymentController::class);
        Route::apiResource('expenses', ExpenseController::class);
        Route::post('expenses/{expense}/approve', [ExpenseController::class, 'approve']);
        Route::post('expenses/{expense}/reject', [ExpenseController::class, 'reject']);
                Route::post('expenses/batch-approve', [ExpenseController::class, 'batchApprove']);
        Route::post('expenses/import', [ExpenseController::class, 'import']);
        Route::get('expenses/export', [ExpenseController::class, 'export']);
        Route::apiResource('expense-categories', ExpenseCategoryController::class);
                Route::apiResource('contracts', ContractController::class);
                Route::post('contracts/{contract}/renew', [ContractController::class, 'renew']);
        Route::post('contracts/{contract}/files', [ContractController::class, 'uploadFile']);
        Route::get('contracts/{contract}/files', [ContractController::class, 'listFiles']);
        Route::delete('contracts/{contract}/files/{fileId}', [ContractController::class, 'deleteFile']);
        Route::post('contracts/{contract}/discussions', [ContractController::class, 'addDiscussion']);
        Route::get('contracts/{contract}/discussions', [ContractController::class, 'listDiscussions']);
        Route::apiResource('contract-types', ContractTypeController::class);
                Route::apiResource('estimates', \App\Http\Controllers\Api\EstimateController::class);
        Route::post('estimates/{estimate}/send', [\App\Http\Controllers\Api\EstimateController::class, 'send']);
                Route::apiResource('credit-notes', CreditNoteController::class);
        Route::apiResource('bank-accounts', BankAccountController::class);
        Route::apiResource('contract-templates', ContractTemplateController::class);
        Route::apiResource('contracts.renew-histories', ContractRenewHistoryController::class);
        Route::apiResource('contracts.discussions', ContractDiscussionController::class);
                Route::apiResource('contracts.signatures', ContractSignatureController::class)->only(['index', 'store', 'show', 'destroy']);
        Route::apiResource('orders', OrderController::class);
        // Recurring Invoices
        Route::apiResource('recurring-invoices', RecurringInvoiceController::class);
        // Recurring Expenses
        Route::apiResource('expense-recurrings', ExpenseRecurringController::class);
        // Unit Types
        Route::apiResource('unit-types', UnitTypeController::class);
        // Tax Rates
        Route::apiResource('taxes', TaxController::class);
        // Offline Payment Methods
        Route::apiResource('offline-payment-methods', OfflinePaymentMethodController::class);
        // Invoice Settings
        Route::prefix('invoice-settings')->group(function () {
            Route::get('/', [InvoiceSettingController::class, 'show']);
            Route::put('/', [InvoiceSettingController::class, 'update']);
        });
    });

        // Tickets
    Route::apiResource('tickets', TicketController::class);
    Route::apiResource('tickets.replies', \App\Http\Controllers\Api\TicketReplyController::class);

        // Approvals
    Route::get('approvals/pending', [ApprovalController::class, 'pending']);
    Route::apiResource('approvals', ApprovalController::class);
    Route::post('approvals/{approvalRequest}/approve', [ApprovalController::class, 'approve']);
    Route::post('approvals/{approvalRequest}/reject', [ApprovalController::class, 'reject']);

        // Calendar Events
        Route::apiResource('events', EventController::class);
    Route::post('events/{event}/participants', [EventController::class, 'addParticipants']);
    Route::delete('events/{event}/participants/{user}', [EventController::class, 'removeParticipant']);

    // Notifications
        Route::apiResource('notifications', NotificationController::class)->only(['index', 'update', 'destroy']);
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllRead']);
    Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);

    // Notification Settings
    Route::get('notification-settings', [NotificationSettingController::class, 'index']);
    Route::put('notification-settings', [NotificationSettingController::class, 'update']);
    Route::get('notification-settings/company', [NotificationSettingController::class, 'companySettings']);
    Route::post('notification-settings/reset', [NotificationSettingController::class, 'reset']);

    // Notices
    Route::apiResource('notices', NoticeController::class);
    Route::post('notices/{notice}/mark-read', [NoticeController::class, 'markAsRead']);

    // Discussions
    Route::apiResource('discussions', DiscussionController::class);
    Route::get('discussions/{discussion}/replies', [DiscussionController::class, 'replies']);
    Route::post('discussions/{discussion}/replies', [DiscussionController::class, 'storeReply']);
    Route::post('discussions/{discussion}/replies/{reply}/mark-solution', [DiscussionController::class, 'markSolution']);

    // Company Addresses
    Route::apiResource('company-addresses', CompanyAddressController::class);

    // Sticky Notes
    Route::apiResource('sticky-notes', StickyNoteController::class);

    // Knowledge Base
    Route::apiResource('knowledge-bases', KnowledgeBaseController::class);

    // Reports
    Route::prefix('reports')->group(function () {
        Route::get('finance', [ReportController::class, 'finance']);
        Route::get('tasks', [ReportController::class, 'tasks']);
        Route::get('timelogs', [ReportController::class, 'timelogs']);
        Route::get('attendance', [ReportController::class, 'attendance']);
        Route::get('sales', [ReportController::class, 'sales']);
        Route::get('expenses', [ReportController::class, 'expenses']);
        Route::get('leaves', [ReportController::class, 'leaves']);
    });

        // Settings
    Route::prefix('settings')->group(function () {
        Route::get('/', [SettingController::class, 'index']);
        Route::get('organisation', [SettingController::class, 'getOrganisation']);
        Route::put('organisation', [SettingController::class, 'updateOrganisation']);
        Route::get('gdpr', [SettingController::class, 'getGdpr']);
        Route::put('gdpr', [SettingController::class, 'updateGdpr']);
        Route::get('notifications', [SettingController::class, 'getNotifications']);
        Route::put('notifications/{type}', [SettingController::class, 'updateNotification']);
        Route::get('module/{module}', [SettingController::class, 'getModuleSetting']);
        Route::put('module/{module}', [SettingController::class, 'updateModuleSetting']);
        Route::apiResource('custom-fields', \App\Http\Controllers\Api\CustomFieldController::class);
        Route::apiResource('tax-settings', \App\Http\Controllers\Api\TaxSettingController::class);
    });

        // ===== GDPR & Compliance =====

    // GDPR Settings
    Route::prefix('gdpr')->group(function () {
        Route::get('settings', [GdprSettingController::class, 'show']);
        Route::put('settings', [GdprSettingController::class, 'update']);
    });

    // Consent purposes & data removal requests
    Route::apiResource('purpose-consents', PurposeConsentController::class);
    Route::post('purpose-consents/{purposeConsent}/consent-user', [PurposeConsentController::class, 'consentUser']);
    Route::post('purpose-consents/{purposeConsent}/consent-lead', [PurposeConsentController::class, 'consentLead']);
    Route::get('removal-requests', [PurposeConsentController::class, 'removalRequests']);
    Route::post('removal-requests', [PurposeConsentController::class, 'storeRemovalRequest']);
    Route::post('removal-requests/{removalRequest}/approve', [PurposeConsentController::class, 'approveRemovalRequest']);
    Route::post('removal-requests/{removalRequest}/reject', [PurposeConsentController::class, 'rejectRemovalRequest']);
    Route::get('lead-removal-requests', [PurposeConsentController::class, 'leadRemovalRequests']);
        Route::post('lead-removal-requests', [PurposeConsentController::class, 'storeLeadRemovalRequest']);

    // Teams
    Route::apiResource('teams', TeamController::class);
    Route::post('teams/{team}/add-members', [TeamController::class, 'addMembers']);
    Route::delete('teams/{team}/members/{userId}', [TeamController::class, 'removeMember']);

    // Chat
    Route::apiResource('chats', ChatController::class);
    Route::get('chats/{chat}/messages', [ChatController::class, 'messages']);
    Route::post('chats/{chat}/messages', [ChatController::class, 'sendMessage']);
    Route::post('chats/{chat}/add-participants', [ChatController::class, 'addParticipants']);
        Route::delete('chats/{chat}/participants/{userId}', [ChatController::class, 'removeParticipant']);
        Route::get('chats/search', [ChatController::class, 'search']);

    // Salary Management
    Route::middleware('module:salary')->prefix('salary')->group(function () {
        Route::apiResource('structures', \App\Http\Controllers\Api\SalaryStructureController::class);
        Route::apiResource('payslips', \App\Http\Controllers\Api\PayslipController::class)->only(['index', 'show']);
        Route::post('payslips/generate', [\App\Http\Controllers\Api\PayslipController::class, 'generate']);
        Route::post('payslips/batch-generate', [\App\Http\Controllers\Api\PayslipController::class, 'batchGenerate']);
        Route::post('payslips/{payslip}/send', [\App\Http\Controllers\Api\PayslipController::class, 'send']);
    });

    // Asset Management
    Route::middleware('module:asset')->prefix('assets')->group(function () {
        Route::apiResource('items', \App\Http\Controllers\Api\AssetController::class);
        Route::post('items/{asset}/allocate', [\App\Http\Controllers\Api\AssetController::class, 'allocate']);
        Route::post('items/{asset}/return', [\App\Http\Controllers\Api\AssetController::class, 'returnAsset']);
        Route::get('items/{asset}/depreciation', [\App\Http\Controllers\Api\AssetController::class, 'calculateDepreciation']);
        Route::post('items/{asset}/maintenance', [\App\Http\Controllers\Api\AssetController::class, 'addMaintenanceRecord']);
        Route::get('items/{asset}/maintenance', [\App\Http\Controllers\Api\AssetController::class, 'listMaintenanceRecords']);
    });

        // Procurement
    Route::middleware('module:procurement')->prefix('procurement')->group(function () {
        Route::apiResource('vendors', \App\Http\Controllers\Api\VendorController::class);
        Route::apiResource('purchase-requests', \App\Http\Controllers\Api\PurchaseRequestController::class);
        Route::post('purchase-requests/{purchaseRequest}/approve', [\App\Http\Controllers\Api\PurchaseRequestController::class, 'approve']);
        Route::post('purchase-requests/{purchaseRequest}/reject', [\App\Http\Controllers\Api\PurchaseRequestController::class, 'reject']);
    });

    // ===== Storage Settings =====
    Route::prefix('storage-settings')->group(function () {
        Route::get('/', [\App\Http\Controllers\Api\StorageSettingController::class, 'show']);
        Route::put('/', [\App\Http\Controllers\Api\StorageSettingController::class, 'update']);
        Route::post('test-connection', [\App\Http\Controllers\Api\StorageSettingController::class, 'testConnection']);
    });

    // ===== Database Backup =====
    Route::apiResource('database-backups', \App\Http\Controllers\Api\DatabaseBackupController::class)->except(['update']);
    Route::get('database-backups/{databaseBackup}/download', [\App\Http\Controllers\Api\DatabaseBackupController::class, 'download']);
    Route::post('database-backups/{databaseBackup}/restore', [\App\Http\Controllers\Api\DatabaseBackupController::class, 'restore']);

    // ===== API Key Management =====
    Route::apiResource('api-keys', \App\Http\Controllers\Api\ApiKeyController::class)->except(['update']);
    Route::post('api-keys/{apiKey}/revoke', [\App\Http\Controllers\Api\ApiKeyController::class, 'revoke']);

    // ===== Custom Modules =====
    Route::apiResource('custom-modules', \App\Http\Controllers\Api\CustomModuleController::class);
    Route::get('custom-modules/{customModule}/records', [\App\Http\Controllers\Api\CustomModuleController::class, 'listRecords']);
    Route::post('custom-modules/{customModule}/records', [\App\Http\Controllers\Api\CustomModuleController::class, 'storeRecord']);
    Route::put('custom-modules/{customModule}/records/{record}', [\App\Http\Controllers\Api\CustomModuleController::class, 'updateRecord']);
    Route::delete('custom-modules/{customModule}/records/{record}', [\App\Http\Controllers\Api\CustomModuleController::class, 'destroyRecord']);

    // ===== Custom Links =====
    Route::apiResource('custom-links', \App\Http\Controllers\Api\CustomLinkController::class);
    Route::get('custom-links/active', [\App\Http\Controllers\Api\CustomLinkController::class, 'activeLinks']);
    Route::post('custom-links/reorder', [\App\Http\Controllers\Api\CustomLinkController::class, 'reorder']);

        // ===== Invoice Templates =====
    Route::apiResource('invoice-templates', \App\Http\Controllers\Api\InvoiceTemplateController::class);
    Route::post('invoice-templates/{invoiceTemplate}/set-default', [\App\Http\Controllers\Api\InvoiceTemplateController::class, 'setDefault']);
    Route::get('invoice-templates/{invoiceTemplate}/preview', [\App\Http\Controllers\Api\InvoiceTemplateController::class, 'preview']);

    // ===== AI Assistant =====
    Route::prefix('ai')->group(function () {
        Route::get('conversations', [\App\Http\Controllers\Api\AiAssistantController::class, 'index']);
        Route::post('conversations', [\App\Http\Controllers\Api\AiAssistantController::class, 'store']);
        Route::get('conversations/{conversation}', [\App\Http\Controllers\Api\AiAssistantController::class, 'show']);
        Route::post('conversations/{conversation}/chat', [\App\Http\Controllers\Api\AiAssistantController::class, 'chat']);
        Route::delete('conversations/{conversation}', [\App\Http\Controllers\Api\AiAssistantController::class, 'destroy']);
        Route::post('quick-chat', [\App\Http\Controllers\Api\AiAssistantController::class, 'quickChat']);
    });

    // ===== Activity Logs =====
    Route::get('activity-logs', [\App\Http\Controllers\Api\ActivityLogController::class, 'index']);
    Route::get('activity-logs/{subjectType}/{subjectId}', [\App\Http\Controllers\Api\ActivityLogController::class, 'forSubject']);
    Route::post('activity-logs/cleanup', [\App\Http\Controllers\Api\ActivityLogController::class, 'cleanup']);

        // ===== Webhook =====
    Route::apiResource('webhooks', \App\Http\Controllers\Api\WebhookController::class);
    Route::get('webhooks/{webhook}/deliveries', [\App\Http\Controllers\Api\WebhookController::class, 'deliveries']);
    Route::post('webhook-deliveries/{delivery}/retry', [\App\Http\Controllers\Api\WebhookController::class, 'retryDelivery']);

    // ===== Email Templates =====
    Route::apiResource('email-templates', EmailTemplateController::class);
    Route::post('email-templates/{emailTemplate}/render', [EmailTemplateController::class, 'render']);

    // ===== Email Logs =====
    Route::apiResource('email-logs', EmailLogController::class)->only(['index', 'show', 'destroy']);
    Route::post('email-logs/{emailLog}/resend', [EmailLogController::class, 'resend']);
    Route::post('email-logs/cleanup', [EmailLogController::class, 'cleanup']);

    // ===== SMS Templates =====
    Route::apiResource('sms-templates', SmsTemplateController::class);
    Route::post('sms-templates/{smsTemplate}/render', [SmsTemplateController::class, 'render']);

    // ===== Countries & Currencies =====
    Route::apiResource('countries', CountryController::class)->only(['index', 'show']);
    Route::apiResource('currencies', CurrencyController::class)->only(['index', 'show', 'update']);

    // ===== Device Tokens (Push Notifications) =====
    Route::apiResource('device-tokens', DeviceTokenController::class)->only(['index', 'store', 'destroy']);
});

// ===== Account Management (auth + company context) =====
Route::middleware(['auth:sanctum', 'company'])->prefix('account')->group(function () {
    Route::get('profile', [\App\Http\Controllers\Api\Account\ProfileController::class, 'show']);
    Route::put('profile', [\App\Http\Controllers\Api\Account\ProfileController::class, 'update']);
    Route::put('profile/password', [\App\Http\Controllers\Api\Account\ProfileController::class, 'changePassword']);
    Route::put('profile/preferences', [\App\Http\Controllers\Api\Account\ProfileController::class, 'updatePreferences']);
    Route::get('company', [\App\Http\Controllers\Api\Account\CompanyController::class, 'show']);
    Route::put('company', [\App\Http\Controllers\Api\Account\CompanyController::class, 'update']);
});

// ===== Super Admin Routes (super admin only) =====
Route::middleware(['auth:sanctum', 'super_admin'])->prefix('super-admin')->group(function () {
    Route::apiResource('companies', \App\Http\Controllers\Api\SuperAdmin\CompanyController::class);
    Route::post('companies/{company}/activate', [\App\Http\Controllers\Api\SuperAdmin\CompanyController::class, 'activate']);
    Route::post('companies/{company}/suspend', [\App\Http\Controllers\Api\SuperAdmin\CompanyController::class, 'suspend']);
    Route::apiResource('packages', \App\Http\Controllers\Api\SuperAdmin\PackageController::class);
    Route::apiResource('subscriptions', \App\Http\Controllers\Api\SuperAdmin\SubscriptionController::class);
    Route::post('subscriptions/{subscription}/renew', [\App\Http\Controllers\Api\SuperAdmin\SubscriptionController::class, 'renew']);
    Route::apiResource('users', \App\Http\Controllers\Api\SuperAdmin\UserController::class);
    Route::post('users/{user}/reset-password', [\App\Http\Controllers\Api\SuperAdmin\UserController::class, 'resetPassword']);
});

// ===== Payment Callback Routes (no auth) =====
Route::prefix('payment')->group(function () {
    Route::post('alipay/notify', [\App\Http\Controllers\Api\PaymentCallbackController::class, 'alipayNotify']);
    Route::get('alipay/return', [\App\Http\Controllers\Api\PaymentCallbackController::class, 'alipayReturn']);
    Route::post('wechat/notify', [\App\Http\Controllers\Api\PaymentCallbackController::class, 'wechatNotify']);
});

