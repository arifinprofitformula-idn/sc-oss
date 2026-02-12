<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
        'group',
        'type',
        'description',
    ];

    /**
     * Get the value attribute.
     * Decrypt if type is 'encrypted'.
     */
    public function getValueAttribute($value)
    {
        if ($this->type === 'encrypted' && !empty($value)) {
            try {
                return Crypt::decryptString($value);
            } catch (\Exception $e) {
                return $value;
            }
        }
        return $value;
    }

    /**
     * Set the value attribute.
     * Encrypt if type is 'encrypted'.
     * Note: 'type' must be set before 'value' for this to work in one go,
     * or use update(['value' => ...]) on an existing model.
     */
    public function setValueAttribute($value)
    {
        // Check if type is set in attributes (for updates) or in this assignment
        $type = $this->attributes['type'] ?? ($this->type ?? 'text');

        if ($type === 'encrypted' && !empty($value)) {
            $this->attributes['value'] = Crypt::encryptString($value);
        } else {
            $this->attributes['value'] = $value;
        }
    }

    /**
     * Helper to get value by key statically.
     */
    public static function getValue($key, $default = null)
    {
        $setting = self::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }
}
