<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_name',
        'invoice_name',
        'invoice_logo',
        'file_type',
        'prefix',
        'number_of_digit',
        'numbering_type',
        'start_number',
        'last_invoice_number',
        'header_text',
        'header_title',
        'footer_text',
        'footer_title',
        'preview_invoice',
        'size',
        'primary_color',
        'secondary_color',
        'text_color',
        'company_logo',
        'logo_height',
        'logo_width',
        'is_default',
        'status',
        'invoice_date_format',
        'show_column',
        'extra',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'status' => 'boolean',
            'start_number' => 'integer',
            'last_invoice_number' => 'integer',
            'show_column' => 'array',
            'extra' => 'array',
        ];
    }

    // =============================
    // Relationships
    // =============================
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // =============================
    // Scopes
    // =============================
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeBySize($query, string $size)
    {
        return $query->where('size', $size);
    }

    // =============================
    // Static Methods
    // =============================
    public static function getDefaultForSize(?string $size = null): ?self
    {
        $query = static::query()->default()->active();

        if ($size) {
            $query->bySize($size);
        }

        return $query->first();
    }
}
