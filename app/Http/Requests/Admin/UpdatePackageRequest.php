<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePackageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'image' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048', // 2MB
                function ($attribute, $value, $fail) {
                    if ($value && $value instanceof \Illuminate\Http\UploadedFile && $value->isValid()) {
                        // Fix ValueError: Path must not be empty
                        $path = $value->getRealPath();
                        if (!$path) {
                             return; 
                        }

                        $image = @getimagesize($path);
                        if ($image) {
                            $width = $image[0];
                            $height = $image[1];
                            $ratio = $width / $height;
                            
                            // Tolerance for floating point comparison (aligned with frontend 0.05)
                            $epsilon = 0.05;
                            
                            $is1x1 = abs($ratio - 1) < $epsilon;
                            $is3x4 = abs($ratio - 0.75) < $epsilon;

                            if (!$is1x1 && !$is3x4) {
                                $fail('Rasio aspek gambar harus 1:1 atau 3:4.');
                            }
                        }
                    }
                },
            ],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'weight' => ['required', 'integer', 'min:1', 'max:30000'],
            'original_price' => ['nullable', 'numeric', 'min:0'],
            'duration_days' => ['nullable', 'integer', 'min:1'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'benefits' => ['nullable', 'array'],
            'benefits.*' => ['string', 'max:255'],
            'is_active' => ['boolean'],
            'commission_type' => ['required', 'string', 'in:percentage,fixed'],
            'commission_value' => ['required', 'numeric', 'min:0'],
            'products' => ['nullable', 'array'],
            'products.*.id' => ['required', 'exists:products,id'],
            'products.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }
}
