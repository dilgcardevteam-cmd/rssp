<?php

namespace App\Services;

use App\Models\Applications;
use App\Models\DocumentGalleryItem;
use App\Models\UploadedDocument;

class DocumentGallerySyncService
{
    public function syncFromUploadedDocument(UploadedDocument $document): void
    {
        $documentType = trim((string) $document->document_type);
        $storagePath = trim((string) $document->storage_path);

        if (!$this->isSyncable($documentType, $storagePath)) {
            return;
        }

        $this->upsertGalleryItem(
            (int) $document->user_id,
            $documentType,
            (string) ($document->original_name ?: basename($storagePath)),
            (string) ($document->stored_name ?: basename($storagePath)),
            $storagePath,
            (string) ($document->mime_type ?: 'application/octet-stream'),
            (int) ($document->file_size_8b ?? 0)
        );
    }

    public function syncApplicationLetterFromApplication(Applications $application): void
    {
        $storagePath = trim((string) ($application->file_storage_path ?? ''));
        if (!$this->isSyncable('application_letter', $storagePath)) {
            return;
        }

        $this->upsertGalleryItem(
            (int) $application->user_id,
            'application_letter',
            (string) ($application->file_original_name ?: basename($storagePath)),
            (string) ($application->file_stored_name ?: basename($storagePath)),
            $storagePath,
            'application/pdf',
            (int) ($application->file_size_8b ?? 0)
        );
    }

    private function isSyncable(string $documentType, string $storagePath): bool
    {
        if ($documentType === '' || strtoupper($documentType) === 'NOINPUT') {
            return false;
        }

        if ($storagePath === '' || strtoupper($storagePath) === 'NOINPUT') {
            return false;
        }

        return true;
    }

    private function upsertGalleryItem(
        int $userId,
        string $documentType,
        string $originalName,
        string $storedName,
        string $storagePath,
        string $mimeType,
        int $fileSize
    ): void {
        DocumentGalleryItem::updateOrCreate(
            [
                'user_id' => $userId,
                'document_type' => $documentType,
            ],
            [
                'original_name' => $originalName,
                'stored_name' => $storedName,
                'storage_path' => $storagePath,
                'mime_type' => $mimeType !== '' ? $mimeType : 'application/octet-stream',
                'file_size_8b' => max(0, $fileSize),
            ]
        );
    }
}
