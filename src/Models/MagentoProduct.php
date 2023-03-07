<?php

namespace JustBetter\MagentoProducts\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use JustBetter\MagentoProducts\Contracts\ChecksMagentoExistence;

/**
 * @property int $id
 * @property string $sku
 * @property ?string $store
 * @property bool $exists_in_magento
 * @property bool $enabled
 * @property ?array $data
 * @property ?Carbon $last_checked
 */
class MagentoProduct extends Model
{
    public $guarded = [];

    public $casts = [
        'data' => 'array',
        'exists_in_magento' => 'boolean',
        'last_checked' => 'datetime',
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

    /**
     * @deprecated Use the action ChecksMagentoExistence instead
     *
     * @codeCoverageIgnore
     */
    public static function existsInMagento(string $sku): bool
    {
        /** @var ChecksMagentoExistence $action */
        $action = app(ChecksMagentoExistence::class);

        return $action->exists($sku);
    }
}
