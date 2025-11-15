<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

final class UpdateYieldGuidelineRequest extends FormRequest
{
    public function authorize(): bool
    {
        $guideline = $this->route('yield-guideline') ?? $this->route('yieldGuideline');
        
        if (!$guideline) {
            return false;
        }

        return $this->user()->can('update', $guideline)
            || $this->user()->hasPermissionTo('manufacturing.yield-guidelines.edit');
    }

    public function rules(): array
    {
        $guidelineId = $this->route('yield-guideline')?->id ?? $this->route('yieldGuideline')?->id;

        return [
            'from_item_id' => [
                'sometimes',
                'required',
                'exists:items,id',
                Rule::unique('yield_guidelines')->where(function ($query) {
                    return $query->where('to_item_id', $this->to_item_id);
                })->ignore($guidelineId),
            ],
            'to_item_id' => [
                'sometimes',
                'required',
                'exists:items,id',
                'different:from_item_id',
            ],
            'from_stage' => 'sometimes|required|string|in:adonan,gelondongan,kerupuk_kg',
            'to_stage' => 'sometimes|required|string|in:gelondongan,kerupuk_kg,packing',
            'yield_quantity' => 'sometimes|required|numeric|min:0.001',
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

