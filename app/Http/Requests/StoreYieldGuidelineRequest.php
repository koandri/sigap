<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class StoreYieldGuidelineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\YieldGuideline::class)
            || $this->user()->hasPermissionTo('manufacturing.yield-guidelines.create');
    }

    public function rules(): array
    {
        return [
            'from_item_id' => [
                'required',
                'exists:items,id',
                Rule::unique('yield_guidelines')->where(function ($query) {
                    return $query->where('to_item_id', $this->to_item_id);
                }),
            ],
            'to_item_id' => [
                'required',
                'exists:items,id',
                'different:from_item_id',
            ],
            'from_stage' => 'required|string|in:adonan,gelondongan,kerupuk_kg',
            'to_stage' => 'required|string|in:gelondongan,kerupuk_kg,packing',
            'yield_quantity' => 'required|numeric|min:0.001',
        ];
    }

    public function messages(): array
    {
        return [
            'from_item_id.required' => 'From item is required.',
            'from_item_id.unique' => 'A yield guideline already exists for this item pair.',
            'to_item_id.required' => 'To item is required.',
            'to_item_id.different' => 'To item must be different from from item.',
            'from_stage.required' => 'From stage is required.',
            'from_stage.in' => 'From stage must be one of: adonan, gelondongan, kerupuk_kg.',
            'to_stage.required' => 'To stage is required.',
            'to_stage.in' => 'To stage must be one of: gelondongan, kerupuk_kg, packing.',
            'yield_quantity.required' => 'Yield quantity is required.',
            'yield_quantity.min' => 'Yield quantity must be greater than 0.',
        ];
    }
}

