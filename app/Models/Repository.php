<?php

declare(strict_types=1);

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

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

    protected $fillable = [
        'name',
    ];

    public ?Carbon $syncStartedAt = null;

    protected function casts(): array
    {
        return [
            'statistics_from' => 'immutable_datetime',
            'last_synced_at' => 'datetime',
        ];
    }

    /**
     * @return array<string, array<array-key, mixed>>
     */
    public static function rules(): array
    {
        return [
            'vcs_id' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'sync_interval' => ['required', 'integer'],
            'sync_interval_hours' => ['required', 'integer', Rule::in([1, 4, 6, 12, 24])],
            'statistics_from' => ['required', 'date'],
            'vcs_instance_id' => [Rule::exists(VcsInstance::class, 'id')],
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
