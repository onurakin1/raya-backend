<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SipButtons extends Model
{
    use HasFactory;
    public $timestamps = false;
    protected $connection = 'mysql2';
    protected $table = 'fop2buttons';
    protected $primaryKey = 'id';
    protected $fillable = ['context_id', 'exclude', 'sortorder', 'type', 'device', 'privacy', 'label', 'group', 'exten', 'email', 'context', 'mailbox', 'channel', 'queuechannel', 'orginatechannel', 'customastdb', 'spyoptions', 'external', 'accountcode', 'tags', 'extenvoicemail', 'queuecontext', 'server', 'cssclass', 'originatevariables', 'autoanswerheader', 'sip_username', 'sip_password'];
}
