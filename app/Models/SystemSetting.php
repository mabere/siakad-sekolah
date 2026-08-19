<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $guarded = ['id'];

    public function getValueAttribute(?string $value): mixed
    {
        return $value === null ? null : json_decode($value, true);
    }

    public function setValueAttribute(mixed $value): void
    {
        $encoded = json_encode($value);
        $this->attributes['value'] = $encoded === false ? 'null' : $encoded;
    }
}
