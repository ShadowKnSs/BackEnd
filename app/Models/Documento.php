<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Documento extends Model
{
    protected $table = 'documentos';
    protected $primaryKey = 'idDocumento';
    public $timestamps = false;

    protected $fillable = [
        'idProceso',
        'nombreDocumento',
        'codigoDocumento',
        'tipoDocumento',
        'fechaRevision',
        'fechaVersion',
        'noRevision',
        'noCopias',
        'tiempoRetencion',
        'lugarAlmacenamiento',
        'medioAlmacenamiento',
        'disposicion',
        'responsable',
        'urlArchivo',
    ];

    /** Normaliza lo que se guarda en BD (ruta relativa) */
    public function setUrlArchivoAttribute($value)
    {
        if (empty($value)) {
            $this->attributes['urlArchivo'] = null;
            return;
        }

        // Si viene un data URL o una URL absoluta, convertir a ruta relativa
        $v = (string) $value;

        // 1) si ya es sólo ruta relativa, dejarla
        if (Str::startsWith($v, ['documentos/', 'graficas/', 'reportes/'])) {
            $this->attributes['urlArchivo'] = $v;
            return;
        }

        // 2) si viene con prefijo /storage/...
        if (Str::startsWith($v, ['storage/'])) {
            $this->attributes['urlArchivo'] = Str::after($v, 'storage/');
            return;
        }

        // 3) si es URL absoluta http/https -> extraer la parte después de /storage/
        if (Str::startsWith($v, ['http://', 'https://'])) {
            $path = parse_url($v, PHP_URL_PATH) ?? '';
            // /storage/documentos/archivo.zip -> documentos/archivo.zip
            $relative = ltrim(Str::after($path, '/storage/'), '/');
            $this->attributes['urlArchivo'] = $relative ?: null;
            return;
        }

        // Fallback: guardarlo tal cual (último recurso)
        $this->attributes['urlArchivo'] = ltrim($v, '/');
    }

    /** Devuelve SIEMPRE una URL pública https */
    public function getUrlArchivoAttribute($value)
    {
        if (empty($value))
            return null;

        $v = (string) $value;

        // si ya es http/https, forzar https por si quedó legacy
        if (Str::startsWith($v, ['http://', 'https://'])) {
            return preg_replace('#^http://#', 'https://', $v);
        }

        // es ruta relativa: construir URL pública desde el disk 'public'
        // config/filesystems.php -> 'public.url' = env('APP_URL').'/storage'
        return Storage::disk('public')->url($v); // -> https://.../storage/{ruta}
    }

}
