<?php

namespace App\Models;

use App\Enums\AudioFileStatus;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property string $id
 * @property int $user_id
 * @property string $filename
 * @property string $path
 * @property int $size
 * @property string $mime_type
 * @property float|null $duration
 * @property AudioFileStatus|string $status
 * @property string|null $error_message
 * @property \Carbon\Carbon $uploaded_at
 * @property \Carbon\Carbon|null $processed_at
 * @property array|null $metadata
 */

class AudioFile extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'filename',
        'path',
        'size',
        'mime_type',
        'duration',
        'status',
        'error_message',
        'uploaded_at',
        'processed_at',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'duration' => 'decimal:2',
            'uploaded_at' => 'datetime',
            'processed_at' => 'datetime',
            'metadata' => 'array',
        ];
    }

    /**
     * Get the file size in a human-readable format.
     */
    public function getSizeFormattedAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, 2) . ' ' . $units[$pow];
    }

    /**
     * Get the user that owns the audio file.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the transcription associated with the audio file.
     */
    public function transcription(): HasOne
    {
        return $this->hasOne(Transcription::class);
    }
}
