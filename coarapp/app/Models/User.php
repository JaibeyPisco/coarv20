<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Configuracion\EmpresaModel;
use App\Models\Configuracion\PermisoModel;
use App\Models\Configuracion\RolModel;
use Carbon\CarbonInterface;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property-read int $id
 * @property-read string $email
 * @property-read CarbonInterface|null $email_verified_at
 * @property-read string $password
 * @property-read string|null $remember_token
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'usuario';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'usuario',
        'email',
        'password',
        'nombre',
        'apellido',
        'tipo',
        'tipo_persona',
        'id_personal',
        'id_estudiante',
        'id_rol',
        'imagen',
        'fl_ver_informacion_privada',
        'fl_suspendido',
        'id_empresa',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'integer',
            'nombre' => 'string',
            'apellido' => 'string',
            'email' => 'string',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'remember_token' => 'string',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<EmpresaModel, User>
     */
    public function empresa(): BelongsTo
    {
        return $this->belongsTo(EmpresaModel::class, 'id_empresa');
    }

    /**
     * @return BelongsTo<RolModel, User>
     */
    public function rol(): BelongsTo
    {
        return $this->belongsTo(RolModel::class, 'id_rol');
    }

    /**
     * Obtener permisos del usuario a través de su rol
     *
     * @return array<int, array{menu: string, view: bool, new: bool, edit: bool, delete: bool}>
     */
    protected function getPermisosAttribute(): array
    {
        // Si es SUPER ADMINISTRADOR o SUPER USUARIO, retornar array vacío (tiene todos los permisos)
        if ($this->tipo === 'SUPER ADMINISTRADOR' || $this->tipo === 'SUPER USUARIO') {
            return [];
        }

        // Si no tiene rol, no tiene permisos
        if (! $this->id_rol) {
            return [];
        }

        // Obtener permisos del rol
        $permisos = PermisoModel::query()
            ->where('id_rol', $this->id_rol)
            ->get()
            ->map(fn (PermisoModel $permiso): array => [
                'menu' => $permiso->menu,
                'view' => (bool) $permiso->view,
                'new' => (bool) $permiso->new,
                'edit' => (bool) $permiso->edit,
                'delete' => (bool) $permiso->delete,
            ])
            ->all();

        return $permisos;
    }

    protected function getNameAttribute(): string
    {
        return mb_trim(sprintf('%s %s', $this->nombre ?? '', $this->apellido ?? '')) ?: ($this->email ?? '');
    }

    protected function getInitialsAttribute(): string
    {
        $parts = array_filter([$this->nombre, $this->apellido]);

        if ($parts === []) {
            return Str::upper(Str::substr($this->email ?? '?', 0, 2));
        }

        return Str::upper(collect($parts)
            ->map(static fn (string $value): string => Str::substr($value, 0, 1))
            ->take(2)
            ->implode(''));
    }

    protected function getAvatarUrlAttribute(): ?string
    {
        if (blank($this->imagen)) {
            return null;
        }

        $path = (string) $this->imagen;

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        return asset(mb_ltrim($path, '/'));
    }
}
