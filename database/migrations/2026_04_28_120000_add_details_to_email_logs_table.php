<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('email_logs', function (Blueprint $table) {
            $table->string('mailer')->nullable()->after('recipient_email');
            $table->string('from_email')->nullable()->after('mailer');
            $table->string('from_name')->nullable()->after('from_email');
            $table->string('subject')->nullable()->after('from_name');
            $table->string('mailable_class')->nullable()->after('subject');
            $table->string('notification_class')->nullable()->after('mailable_class');
            $table->string('template_name')->nullable()->after('notification_class');
            $table->string('message_id')->nullable()->after('template_name');
            $table->longText('body_html')->nullable()->after('message_id');
            $table->longText('body_text')->nullable()->after('body_html');
            $table->json('metadata')->nullable()->after('body_text');
            $table->timestamp('sent_at')->nullable()->after('metadata');

            $table->index('subject');
            $table->index('message_id');
            $table->index('sent_at');
            $table->index('notification_class');
            $table->index('mailable_class');
            $table->index(['recipient_email', 'sent_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('email_logs', function (Blueprint $table) {
            $table->dropIndex(['recipient_email', 'sent_at']);
            $table->dropIndex(['mailable_class']);
            $table->dropIndex(['notification_class']);
            $table->dropIndex(['sent_at']);
            $table->dropIndex(['message_id']);
            $table->dropIndex(['subject']);

            $table->dropColumn([
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
            ]);
        });
    }
};
