<p align="center">
  <img src="https://img.shields.io/badge/Version-1.0.0-blue.svg" alt="Version">
  <img src="https://img.shields.io/badge/License-Apache%202.0-green.svg" alt="License">
  <img src="https://img.shields.io/badge/PHP-8.3+-purple.svg" alt="PHP">
  <img src="https://img.shields.io/badge/Laravel-13-red.svg" alt="Laravel">
</p>

# Customer Connect

**Open-source CRM / HRM / Project Management SaaS Platform**

Customer Connect is a comprehensive, multi-tenant SaaS platform built with Laravel that combines CRM, HRM, and Project Management into a unified solution.

## Key Features

### CRM (Customer Relationship Management)
- **Lead Management** — Full pipeline with status tracking, source tracking, agent assignment
- **Client Management** — Comprehensive client profiles with contact details, categories, and status
- **Deal/Proposal Management** — Create proposals, track deals, convert to invoices
- **Estimate Requests** — Receive and convert estimate requests to formal estimates
- **Contact Management** — Rich contact profiles linked to clients and leads

### HRM (Human Resource Management)
- **Employee Management** — Full employee lifecycle with departments, designations, and roles
- **Attendance Tracking** — Clock in/out with shift management and late marking
- **Leave Management** — Leave types, applications, approvals, and balance tracking
- **Payroll** — Salary structure, payslip generation, and payment tracking
- **Holiday Calendar** — Company-wide and regional holiday management
- **Performance Reviews** — Goal setting, reviews, and appraisal cycles

### Project Management
- **Project Tracking** — Milestones, tasks, time logging, and budgeting
- **Task Management** — Boards, priority, due dates, assignments, pinning
- **Time Tracking** — Timelogs with project/task association and reporting
- **Milestones** — Project milestone tracking with progress indicators
- **File Management** — Project and task-level file attachments

### Finance
- **Invoicing** — Create, send, and track invoices with items and taxes
- **Estimates** — Prepare and send estimates, convert to invoices
- **Payments** — Track payments (online and offline), partial payments
- **Expenses** — Expense tracking with categories, approval workflow
- **Credit Notes** — Issue and manage credit notes
- **Tax Management** — Flexible tax configuration per company

### Contracts & Documents
- **Contract Management** — Create, sign, and track contracts with renewal alerts
- **Document Templates** — Reusable document templates

### Support
- **Ticket System** — Multi-channel support tickets with priority and groups
- **Ticket Groups** — Organize tickets by team or department

### Automation & Integration
- **Webhooks** — Event-driven notifications with delivery tracking and retry
- **API Keys** — Secure external API access with scoped permissions
- **External API** — Read-only REST API for third-party integrations
- **Custom Modules** — Build your own entities with dynamic field definitions
- **Custom Links** — Configurable navigation links
- **Activity Logs** — Comprehensive audit trail with entity-level tracking

### Reporting & Analytics
- **Dashboard** — Role-based dashboards (Admin, Employee, Client)
- **Financial Reports** — Income vs expense, revenue trends
- **Sales Reports** — Lead conversion, deal pipeline analytics
- **Task Reports** — Productivity and completion metrics
- **Attendance Reports** — Presence, absence, and overtime analytics

### Administration
- **Multi-tenancy** — Full company isolation with shared infrastructure
- **Super Admin Dashboard** — Platform-wide company management
- **Package/Subscription Management** — SaaS billing with feature tiers
- **Role & Permission** — Granular RBAC with Spatie Laravel Permission
- **Database Backup** — On-demand backup with download and restore
- **Storage Settings** — Multi-driver file storage (Local, S3, OSS, COS)
- **GDPR Compliance** — Data consent, right-to-be-forgotten, data export
- **Invoice Templates** — Customizable invoice layout with HTML/CSS editing

## Tech Stack

| Component | Technology |
|-----------|-----------|
| Backend | PHP 8.3+ / Laravel 13 |
| Database | MySQL 8.0+ |
| Authentication | Laravel Sanctum |
| Authorization | Spatie Laravel Permission |
| Queue | Laravel Horizon (Redis) |
| Real-time | Laravel Reverb (WebSocket) |
| Excel Import/Export | Maatwebsite Excel |
| Activity Logging | Spatie Activity Log |
| 2FA | PragmaRX Google2FA |

## Quick Start

```bash
git clone https://github.com/PeaseAPI/customer-connect.git
cd customer-connect
composer install
cp .env.example .env
php artisan key:generate
# Configure your database in .env, then:
php artisan migrate
php artisan db:seed
php artisan serve
```

## API

The platform provides 720+ REST API endpoints with Sanctum token authentication. Third-party integrations can use API Key authentication via `X-API-Key` header.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

Copyright 2024-2026 PeaseAPI. Licensed under the [Apache License 2.0](http://www.apache.org/licenses/LICENSE-2.0).

