<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Attributes
 * @property int $id
 * @property string $vcs_id
 * @property string $name
 * @property int $sync_interval
 * @property CarbonImmutable $statistics_from
 * @property ?Carbon $last_synced_at
 *
 * Foreign keys
 * @property int $vcs_instance_id
 *
 * Relationships
 * @property VcsInstance $vcsInstance
 */
class Repository extends Model
{
    /** @use HasFactory<\Database\Factories\RepositoryFactory> */
    use HasFactory;

    public $timestamps = false;

    public ?Carbon $syncStartedAt = null;

    protected function casts(): array
    {
        return [
            'statistics_from' => 'immutable_datetime',
            'last_synced_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<VcsInstance, $this>
     */
    public function vcsInstance(): BelongsTo
    {
        return $this->belongsTo(VcsInstance::class);
    }
}
