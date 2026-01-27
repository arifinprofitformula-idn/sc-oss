<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSilverchannelRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($this->user)],
            'whatsapp' => ['required', 'string', 'max:20'],
            'province_id' => ['required', 'string'],
            'province_name' => ['required', 'string'],
            'city_id' => ['required', 'string'],
            'city_name' => ['required', 'string'],
            'referrer_code' => ['nullable', 'string', 'exists:users,referral_code'],
            'password' => ['nullable', 'confirmed', \Illuminate\Validation\Rules\Password::defaults()],
            'nik' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'in:Laki-laki,Perempuan'],
            'religion' => ['nullable', 'string', 'in:Islam,Kristen,Katolik,Hindu,Buddha,Konghucu'],
            'marital_status' => ['nullable', 'string', 'in:Belum Menikah,Menikah,Cerai Hidup,Cerai Mati'],
            'job' => ['nullable', 'string', 'max:255'],
            'photo' => ['nullable', 'image', 'max:1024'],
        ];
    }
}
