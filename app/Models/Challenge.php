<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\ChallengeActivityType;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

/**
 * Attributes
 * @property int $id
 * @property string $name
 * @property ?string $description
 * @property Carbon $active_from
 * @property Carbon $active_to
 *
 * Relationships
 * @property Collection<int, ChallengeActivity> $activities
 * @property Collection<int, Badge> $badges
 */
class Challenge extends Model
{
    /** @use HasFactory<\Database\Factories\ChallengeFactory> */
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'name',
        'description',
        'active_from',
        'active_to',
    ];

    protected function casts(): array
    {
        return [
            'active_from' => 'datetime',
            'active_to' => 'datetime',
        ];
    }

    /**
     * @return array<string, array<array-key, mixed>>
     */
    public static function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'active_from' => ['required', 'date', 'after:today'],
            'active_to' => ['required', 'date', 'after:active_from'],
            'activities' => ['required', 'array', 'min:1'],
            'activities.*.type' => ['required', 'distinct', Rule::enum(ChallengeActivityType::class)],
            'activities.*.needed_actions_count' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * @return array<string, array<array-key, mixed>>
     */
    public static function messages(): array
    {
        return [
            'activities.*.type.required' => __('Activity type is required.'),
            'activities.*.type.distinct' => __('Each activity type must be unique.'),
        ];
    }

    /**
     * @return HasMany<ChallengeActivity, $this>
     */
    public function activities(): HasMany
    {
        return $this->hasMany(ChallengeActivity::class);
    }

    /**
     * @return HasMany<Badge, $this>
     */
    public function badges(): HasMany
    {
        return $this->hasMany(Badge::class);
    }
}
