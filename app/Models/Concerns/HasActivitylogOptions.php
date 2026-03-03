<?php

namespace App\Models\Concerns;

use Spatie\Activitylog\LogOptions;

trait HasActivitylogOptions
{
    public function getActivitylogOptions(): LogOptions
    {
        $options = LogOptions::defaults();

        if (property_exists(static::class, 'logName') && static::$logName) {
            $options->useLogName(static::$logName);
        }

        if (property_exists(static::class, 'logFillable') && static::$logFillable) {
            $options->logFillable();
        }

        if (property_exists(static::class, 'logAttributes') && is_array(static::$logAttributes) && count(static::$logAttributes)) {
            $options->logOnly(static::$logAttributes);
        }

        if (property_exists(static::class, 'logUnguarded') && static::$logUnguarded) {
            $options->logUnguarded();
        }

        if (property_exists(static::class, 'logOnlyDirty') && static::$logOnlyDirty) {
            $options->logOnlyDirty();
        }

        if (property_exists(static::class, 'submitEmptyLogs') && static::$submitEmptyLogs === false) {
            $options->dontSubmitEmptyLogs();
        }

        return $options;
    }
}
