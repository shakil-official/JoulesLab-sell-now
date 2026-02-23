<?php

namespace App\Core\Route;

class UploadedFile
{
    protected string $tmpName;
    protected string $originalName;
    protected string $mimeType;
    protected int $size;
    protected int $error;

    public function __construct(string $tmpName, string $originalName, string $mimeType, int $size, int $error = UPLOAD_ERR_OK)
    {
        $this->tmpName = $tmpName;
        $this->originalName = $originalName;
        $this->mimeType = $mimeType;
        $this->size = $size;
        $this->error = $error;
    }

    public function getOriginalName(): string
    {
        return $this->originalName;
    }

    public function getTmpName(): string
    {
        return $this->tmpName;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function getError(): int
    {
        return $this->error;
    }

    public function isValid(): bool
    {
        return $this->error === UPLOAD_ERR_OK;
    }

    public function getErrorMessage(): string
    {
        return match ($this->error) {
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
            UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
            default => 'Unknown upload error.',
        };
    }

    public function move(string $directory, string $name = null): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        $targetPath = rtrim($directory, '/') . '/' . ($name ?? $this->originalName);

        return move_uploaded_file($this->tmpName, $targetPath);
    }

    public function getExtension(): string
    {
        return pathinfo($this->originalName, PATHINFO_EXTENSION);
    }

    public function getFilename(): string
    {
        return pathinfo($this->originalName, PATHINFO_FILENAME);
    }

    public function guessExtension(): string
    {
        $extension = $this->getExtension();
        
        if (empty($extension)) {
            $extension = $this->guessExtensionFromMimeType();
        }
        
        return $extension;
    }

    protected function guessExtensionFromMimeType(): string
    {
        $mimeTypes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
            'application/pdf' => 'pdf',
            'text/plain' => 'txt',
            'application/msword' => 'doc',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
            'application/vnd.ms-excel' => 'xls',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
            'application/zip' => 'zip',
            'application/json' => 'json',
            'text/csv' => 'csv',
        ];

        return $mimeTypes[$this->mimeType] ?? 'bin';
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mimeType, 'image/');
    }

    public function getSizeInKB(): float
    {
        return round($this->size / 1024, 2);
    }

    public function getSizeInMB(): float
    {
        return round($this->size / 1024 / 1024, 2);
    }
}
