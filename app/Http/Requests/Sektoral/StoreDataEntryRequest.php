<?php

namespace App\Http\Requests\Sektoral;

use App\Rules\WithinKecamatanBoundary;
use Illuminate\Foundation\Http\FormRequest;

class StoreDataEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->canWrite();
    }

    protected function prepareForValidation(): void
    {
        if (auth()->check() && !auth()->user()->isSuperAdmin()) {
            $this->merge(['kecamatan_id' => auth()->user()->kecamatan_id]);
        }
    }

    public function rules(): array
    {
        return [
            'kecamatan_id' => 'required|exists:kecamatans,id',
            'village_id' => 'nullable|exists:villages,id',
            'indicator_id' => 'required|exists:indicators,id',
            'year' => 'required|integer|min:2000|max:2100',
            'value' => 'nullable|numeric',
            'latitude' => ['nullable', 'numeric', 'between:-90,90', new WithinKecamatanBoundary((int) $this->input('kecamatan_id', auth()->user()->kecamatan_id))],
            'longitude' => 'nullable|numeric|between:-180,180',
            'metadata' => 'nullable|array',
        ];
    }
}
