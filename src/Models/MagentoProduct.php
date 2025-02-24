<?php

namespace JustBetter\MagentoProducts\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $sku
 * @property ?string $store
 * @property bool $exists_in_magento
 * @property bool $enabled
 * @property ?array $data
 * @property ?Carbon $last_checked
 * @property ?string $checksum
 * @property bool $retrieved
 * @property ?Carbon $created_at
 * @property ?Carbon $updated_at
 */
class MagentoProduct extends Model
{
    public $guarded = [];

    public $casts = [
        'data' => 'array',
        'exists_in_magento' => 'boolean',
        'last_checked' => 'datetime',
        'retrieved' => 'boolean',
    ];

    public static function findBySku(string $sku, ?string $store = null): ?static
    {
        /** @var ?static $item */
        $item = static::query()
            ->where('sku', '=', $sku)
            ->when($store !== null, fn (Builder $builder) => $builder->where('store', '=', $store))
            ->first();

        return $item;
    }
}
