<?php

namespace App\Http\Requests\Sektoral;

use App\Rules\WithinKecamatanBoundary;
use Illuminate\Foundation\Http\FormRequest;

class StoreFacilityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->canWrite();
    }

    protected function prepareForValidation(): void
    {
        // Super admin wajib memilih kecamatan; operator dikunci ke kecamatannya
        if (auth()->check() && !auth()->user()->isSuperAdmin()) {
            $this->merge(['kecamatan_id' => auth()->user()->kecamatan_id]);
        }
    }

    public function rules(): array
    {
        return [
            'kecamatan_id' => 'required|exists:kecamatans,id',
            'village_id' => 'nullable|exists:villages,id',
            'name' => 'required|string|max:150',
            'type' => 'required|string|max:50',
            'modul' => 'required|in:M-03,M-04,M-05,M-06,M-07,M-08',
            'address' => 'nullable|string',
            'latitude' => ['required', 'numeric', 'between:-90,90', new WithinKecamatanBoundary((int) $this->input('kecamatan_id', auth()->user()->kecamatan_id))],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy_meters' => 'nullable|numeric|min:0',
            'location_source' => 'nullable|in:map_click,gps,manual_input,geocoding',
            'status' => 'nullable|in:active,inactive,pending',
            'metadata' => 'nullable|array',
        ];
    }

    public function messages(): array
    {
        return [
            'latitude.required' => 'Koordinat lokasi wajib ditentukan melalui peta.',
            'longitude.required' => 'Koordinat lokasi wajib ditentukan melalui peta.',
            'latitude.between' => 'Latitude harus antara -90 sampai 90.',
            'longitude.between' => 'Longitude harus antara -180 sampai 180.',
        ];
    }
}
