<?php

namespace App\Support;

use App\Models\AuditLogs;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogger
{
    public static function log(string $action, array $context = [], ?User $actor = null): void
    {
        $actor ??= Auth::user();
        $request = request();

        AuditLogs::create([
            'user_id' => $actor?->id,
            'name' => $actor?->name ?? $context['name'] ?? 'System',
            'email' => $actor?->email ?? $context['email'] ?? null,
            'role' => $actor?->role ?? $context['role'] ?? null,
            'action' => $action,
            'module' => $context['module'] ?? null,
            'description' => $context['description'] ?? null,
            'target_type' => $context['target_type'] ?? null,
            'target_id' => $context['target_id'] ?? null,
            'before_values' => $context['before_values'] ?? null,
            'after_values' => $context['after_values'] ?? null,
            'severity' => $context['severity'] ?? 'info',
            'method' => $context['method'] ?? $request?->method(),
            'ipAddress' => $context['ip_address'] ?? $request?->ip(),
            'userAgent' => $context['user_agent'] ?? $request?->userAgent(),
        ]);
    }

    public static function logModelCreated(Model $model, string $module, ?string $description = null): void
    {
        self::log(strtolower($module) . '_created', [
            'module' => $module,
            'description' => $description ?? $module . ' record created.',
            'target_type' => $model::class,
            'target_id' => $model->getKey(),
            'after_values' => self::visibleAttributes($model->getAttributes()),
            'severity' => 'info',
        ]);
    }

    public static function logModelUpdated(Model $model, string $module, ?string $description = null): void
    {
        $changes = $model->getChanges();
        unset($changes['updated_at']);

        if ($changes === []) {
            return;
        }

        $before = [];
        foreach (array_keys($changes) as $field) {
            $before[$field] = $model->getOriginal($field);
        }
        $before = self::visibleAttributes($before);
        $after = self::visibleAttributes($changes);

        if ($before === [] && $after === []) {
            return;
        }

        self::log(strtolower($module) . '_updated', [
            'module' => $module,
            'description' => $description ?? $module . ' record updated.',
            'target_type' => $model::class,
            'target_id' => $model->getKey(),
            'before_values' => $before,
            'after_values' => $after,
            'severity' => 'info',
        ]);
    }

    public static function logModelDeleted(Model $model, string $module, ?string $description = null): void
    {
        self::log(strtolower($module) . '_deleted', [
            'module' => $module,
            'description' => $description ?? $module . ' record deleted.',
            'target_type' => $model::class,
            'target_id' => $model->getKey(),
            'before_values' => self::visibleAttributes($model->getOriginal()),
            'severity' => 'danger',
        ]);
    }

    private static function visibleAttributes(array $attributes): array
    {
        return collect($attributes)
            ->except(['password', 'remember_token', 'avatar'])
            ->all();
    }
}
