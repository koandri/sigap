<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Item;
use App\Models\ItemCategory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

final class ItemDropdownService
{
    /**
     * Base helper to convert an item query into id => label pairs.
     */
    private function toSelectOptions(Builder $query): Collection
    {
        /** @var Collection<int, Item> $items */
        $items = $query
            ->orderBy('name')
            ->get();

        return $items->mapWithKeys(
            static fn (Item $item): array => [
                $item->id => $item->label,
            ]
        );
    }

    /**
     * Active items for a single category id.
     */
    public function forCategoryId(int $categoryId, bool $onlyActive = true): Collection
    {
        $query = Item::query()->where('item_category_id', $categoryId);

        if ($onlyActive) {
            $query->active();
        }

        return $this->toSelectOptions($query);
    }

    /**
     * Active items for multiple category ids.
     */
    public function forCategoryIds(array $categoryIds, bool $onlyActive = true): Collection
    {
        $query = Item::query()->whereIn('item_category_id', $categoryIds);

        if ($onlyActive) {
            $query->active();
        }

        return $this->toSelectOptions($query);
    }

    /**
     * Dough items (Adonan) used in recipes or production plans.
     */
    public function forDoughItems(bool $onlyWithRecipes = false): Collection
    {
        $doughCategory = ItemCategory::where('name', 'like', '%Adonan%')->first();

        if (!$doughCategory) {
            return collect();
        }

        $query = Item::query()
            ->where('item_category_id', $doughCategory->id)
            ->active();

        if ($onlyWithRecipes) {
            $query->whereHas('recipes', static function (Builder $builder): void {
                $builder->where('is_active', true);
            });
        }

        return $this->toSelectOptions($query);
    }

    /**
     * Ingredient items for recipes / production planning.
     *
     * Currently: Bahan Baku Lainnya, Ikan, Tepung, Udang.
     */
    public function forIngredientItems(): Collection
    {
        $ingredientCategories = ItemCategory::whereIn('name', [
            'Bahan Baku Lainnya',
            'Ikan',
            'Tepung',
            'Udang',
        ])->pluck('id');

        if ($ingredientCategories->isEmpty()) {
            return collect();
        }

        return $this->forCategoryIds($ingredientCategories->all());
    }

    /**
     * Gelondongan items used in Step 2 of production plans.
     */
    public function forGelondonganItems(): Collection
    {
        $categoryIds = ItemCategory::where('name', 'like', '%Gelondongan%')->pluck('id');

        if ($categoryIds->isEmpty()) {
            return collect();
        }

        return $this->forCategoryIds($categoryIds->all());
    }

    /**
     * Kerupuk Kg / finished product items.
     */
    public function forKerupukKgItems(): Collection
    {
        $categoryIds = ItemCategory::where(function ($q): void {
            $q->where('name', 'like', '%Kerupuk Kg%')
                ->orWhere('name', 'like', '%Finished Products%');
        })->pluck('id');

        if ($categoryIds->isEmpty()) {
            return collect();
        }

        return $this->forCategoryIds($categoryIds->all());
    }

    /**
     * Kerupuk Pack items.
     */
    public function forKerupukPackItems(): Collection
    {
        $categoryIds = ItemCategory::where('name', 'like', '%Kerupuk Pack%')->pluck('id');

        if ($categoryIds->isEmpty()) {
            return collect();
        }

        return $this->forCategoryIds($categoryIds->all());
    }
}


