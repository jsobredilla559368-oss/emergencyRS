<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Incident extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'responder_id',
        'type', 
        'severity',
        'status',
        'description',
        'latitude',
        'longitude',
        'location_address',
        
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function getCredibilityScoreAttribute(): int
    {
        $score = 0;
        
        // Has a reporter?
        if ($this->reporter) {
            // Is it a registered user?
            if (!$this->reporter->is_guest) {
                $score += 30;
            }
            // Do they have a phone number?
            if ($this->reporter->phone) {
                $score += 20;
            }
        }
        
        // Is there an exact location (coordinates)?
        if ($this->latitude && $this->longitude) {
            $score += 20;
        }
        
        // Are there media attachments?
        if ($this->media && $this->media->count() > 0) {
            $score += 30;
        }
        
        return $score;
    }
    
    public function getCredibilityLabelAttribute(): string
    {
        $score = $this->credibility_score;
        if ($score >= 70) return 'High';
        if ($score >= 40) return 'Medium';
        return 'Low';
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function responder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responder_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(IncidentMedia::class);
    }

    public function statusUpdates(): HasMany
    {
        return $this->hasMany(StatusUpdate::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(IncidentNotification::class);
    }
}
