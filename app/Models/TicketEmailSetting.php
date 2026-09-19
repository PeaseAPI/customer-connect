<?php

namespace App\Models;

use App\Traits\HasCompanyScope;
use Illuminate\Database\Eloquent\Model;

class TicketEmailSetting extends Model
{
    use HasCompanyScope;
    protected $fillable = ['company_id','imap_host','imap_port','imap_username','imap_password','imap_encryption','smtp_host','smtp_port'];
}
