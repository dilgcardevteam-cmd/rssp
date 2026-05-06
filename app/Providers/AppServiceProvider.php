<?php

namespace App\Providers;

use App\Jobs\ProcessAdminActivityNotification;
use App\Models\Activity;
use App\Models\Applications;
use App\Models\EmailLog;
use App\Models\UploadedDocument;
use App\Observers\ApplicationObserver;
use App\Observers\UploadedDocumentObserver;
use Illuminate\Auth\Events\Logout;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->ensureFrameworkRuntimeDirectories();

        date_default_timezone_set(config('app.timezone', 'Asia/Manila'));

        Applications::observe(ApplicationObserver::class);
        UploadedDocument::observe(UploadedDocumentObserver::class);

        Gate::define('admin.exam.monitor', function ($admin): bool {
            return in_array((string) ($admin->role ?? ''), ['superadmin', 'admin', 'viewer'], true);
        });

        Gate::define('admin.exam.manage', function ($admin): bool {
            return in_array((string) ($admin->role ?? ''), ['superadmin', 'admin'], true);
        });

        Gate::define('admin.applicants.monitor', function ($admin): bool {
            return in_array((string) ($admin->role ?? ''), ['superadmin', 'admin', 'hr_division'], true);
        });

        Gate::define('admin.system.manage', function ($admin): bool {
            return (string) ($admin->role ?? '') === 'superadmin';
        });

        Gate::define('admin.backoffice.full', function ($admin): bool {
            return in_array((string) ($admin->role ?? ''), ['superadmin', 'admin'], true);
        });

        Event::listen(Logout::class, function (Logout $event) {
            $user = $event->user;
            if ($user) {
                activity()
                    ->causedBy($user)
                    ->event('logout')
                    ->withProperties(['section' => 'Login', 'guard' => $event->guard])
                    ->log('logged out');
            } else {
                activity()
                    ->event('logout')
                    ->withProperties(['section' => 'Login', 'guard' => $event->guard])
                    ->log('logged out');
            }
        });

        Event::listen(MessageSent::class, function (MessageSent $event) {
            $message = $event->message;
            $data = $this->sanitizeMailEventData($event->data ?? []);

            $toAddresses = $this->extractAddressList($message->getTo() ?? []);
            $recipients = collect($toAddresses)->pluck('email')->filter()->values()->all();
            $fromAddress = $this->extractFirstAddress($message->getFrom() ?? []);
            $subject = (string) ($message->getSubject() ?? '(no subject)');
            $mailer = (string) ($event->data['mailer'] ?? config('mail.default'));
            $mailableClass = isset($event->data['__laravel_mailable']) ? (string) $event->data['__laravel_mailable'] : null;
            $notificationClass = isset($event->data['__laravel_notification']) ? (string) $event->data['__laravel_notification'] : null;
            $templateName = $this->resolveTemplateName($event->data ?? [], $mailableClass, $notificationClass);
            $messageId = $this->extractMessageId($message);
            $bodyHtml = $this->stringOrNull($message->getHtmlBody());
            $bodyText = $this->stringOrNull($message->getTextBody());
            $vacancyId = $this->extractVacancyId($event->data ?? []);
            $userId = $this->extractUserId($event->data ?? []);
            $causer = auth('admin')->user() ?? auth()->user();

            $emailLogIds = [];
            foreach ($recipients as $recipientEmail) {
                $emailLog = EmailLog::create([
                    'vacancy_id' => $vacancyId,
                    'user_id' => $userId,
                    'recipient_email' => $recipientEmail,
                    'mailer' => $mailer,
                    'from_email' => $fromAddress['email'],
                    'from_name' => $fromAddress['name'],
                    'subject' => $subject,
                    'mailable_class' => $mailableClass,
                    'notification_class' => $notificationClass,
                    'template_name' => $templateName,
                    'message_id' => $messageId,
                    'body_html' => $bodyHtml,
                    'body_text' => $bodyText,
                    'metadata' => [
                        'to' => $toAddresses,
                        'cc' => $this->extractAddressList($message->getCc() ?? []),
                        'bcc' => $this->extractAddressList($message->getBcc() ?? []),
                        'mail_data' => $data,
                    ],
                    'sent_at' => now(),
                    'status' => 'sent',
                    'error_message' => null,
                ]);
                $emailLogIds[] = $emailLog->id;
            }

            activity()
                ->causedBy($causer)
                ->event('email_sent')
                ->withProperties([
                    'section' => 'Email Logs',
                    'recipients' => $recipients,
                    'subject' => $subject,
                    'mailer' => $mailer,
                    'mailable_class' => $mailableClass,
                    'notification_class' => $notificationClass,
                    'template_name' => $templateName,
                    'email_log_ids' => $emailLogIds,
                ])
                ->log('Sent email to ' . implode(', ', $recipients));
        });

        Activity::created(function (Activity $activity) {
            ProcessAdminActivityNotification::dispatch($activity->id)
                ->onConnection('database');
        });
    }

    private function ensureFrameworkRuntimeDirectories(): void
    {
        $directories = [
            storage_path('framework/cache/data'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
        ];

        foreach ($directories as $directory) {
            if (! is_dir($directory)) {
                @mkdir($directory, 0755, true);
            }
        }
    }

    private function extractAddressList(iterable $addresses): array
    {
        return collect($addresses)
            ->map(function ($address): array {
                if (is_object($address) && method_exists($address, 'getAddress')) {
                    return [
                        'email' => (string) ($address->getAddress() ?? ''),
                        'name' => method_exists($address, 'getName') ? (string) ($address->getName() ?? '') : null,
                    ];
                }

                if (is_string($address)) {
                    return ['email' => $address, 'name' => null];
                }

                return ['email' => '', 'name' => null];
            })
            ->filter(fn (array $item): bool => $item['email'] !== '')
            ->values()
            ->all();
    }

    private function extractFirstAddress(iterable $addresses): array
    {
        $first = $this->extractAddressList($addresses)[0] ?? null;

        return [
            'email' => $first['email'] ?? null,
            'name' => $first['name'] ?? null,
        ];
    }

    private function extractMessageId(object $message): ?string
    {
        $headers = method_exists($message, 'getHeaders') ? $message->getHeaders() : null;
        if ($headers === null || ! method_exists($headers, 'get')) {
            return null;
        }

        $header = $headers->get('Message-ID');
        if ($header === null) {
            return null;
        }

        return method_exists($header, 'getBodyAsString')
            ? (string) $header->getBodyAsString()
            : (string) $header;
    }

    private function resolveTemplateName(array $data, ?string $mailableClass, ?string $notificationClass): ?string
    {
        foreach (['view', 'markdown', 'template', 'html_view', 'text_view'] as $key) {
            if (isset($data[$key]) && is_string($data[$key]) && $data[$key] !== '') {
                return $data[$key];
            }
        }

        if ($notificationClass) {
            return $notificationClass;
        }

        if ($mailableClass) {
            return $mailableClass;
        }

        return null;
    }

    private function extractVacancyId(array $data): string
    {
        foreach (['vacancy_id', 'vacancyId'] as $key) {
            if (isset($data[$key])) {
                $value = $data[$key];
                if (is_scalar($value) && (string) $value !== '') {
                    return (string) $value;
                }
            }
        }

        return 'system';
    }

    private function extractUserId(array $data): int
    {
        foreach (['user_id', 'userId'] as $key) {
            if (isset($data[$key]) && is_numeric($data[$key])) {
                return (int) $data[$key];
            }
        }

        return 0;
    }

    private function sanitizeMailEventData(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if ($key === 'message') {
                continue;
            }

            if (is_scalar($value) || $value === null) {
                $sanitized[$key] = $value;
                continue;
            }

            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeMailEventData($value);
                continue;
            }

            $sanitized[$key] = is_object($value) ? get_class($value) : gettype($value);
        }

        return $sanitized;
    }

    private function stringOrNull(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            return $value;
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        if (is_object($value) && method_exists($value, '__toString')) {
            return (string) $value;
        }

        return null;
    }
}
