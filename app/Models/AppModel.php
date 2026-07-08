<?php

declare(strict_types=1);

namespace App\Models;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Concerns\HasVersion4Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;

abstract class AppModel extends Model
{
    use HasFactory;
    use HasVersion4Uuids;
    use SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (self $model): void {
            $model->stamp('created_by');
            $model->stamp('updated_by');
        });

        static::updating(fn (self $model) => $model->stamp('updated_by'));

        static::deleting(function (self $model): void {
            if ($model->stamp('deleted_by')) {
                $model->saveQuietly();
            }
        });
    }

    protected function stamp(string $column): bool
    {
        if ($this->isDirty($column) || ! auth()->check()) {
            return false;
        }

        if (! Schema::hasColumn($this->getTable(), $column)) {
            return false;
        }

        $this->{$column} = auth()->id();

        return true;
    }

    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
