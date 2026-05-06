<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'vacancy_id',
        'user_id',
        'recipient_email',
        'mailer',
        'from_email',
        'from_name',
        'subject',
        'mailable_class',
        'notification_class',
        'template_name',
        'message_id',
        'body_html',
        'body_text',
        'metadata',
        'sent_at',
        'status',
        'error_message',
    ];

    protected $casts = [
        'metadata' => 'array',
        'sent_at' => 'datetime',
    ];
}
