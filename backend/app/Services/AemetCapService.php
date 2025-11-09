<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class AemetCapService
{
    protected $baseUrl;
    protected $apiKey;

    public function __construct()
    {
        $this->baseUrl = config('services.aemet.base_url');
        $this->apiKey = config('services.aemet.api_key');
    }

    public function obtenerAvisoCap($endpoint)
    {
        // Primera llamada para obtener URL del XML CAP
        $urlPrimera = "{$this->baseUrl}/{$endpoint}?api_key={$this->apiKey}";
        $respuestaJson = Http::get($urlPrimera);

        if (!$respuestaJson->ok()) {
            return ['error' => 'Error al obtener la URL del aviso CAP'];
        }

        $urlXml = $respuestaJson->json()['datos'] ?? null;
        if (!$urlXml) {
            return ['error' => 'No se encontró la URL del XML CAP'];
        }

        // Segunda llamada para descargar el XML
        $xmlContenido = Http::get($urlXml)->body();

        // Normalizar a UTF-8 (AEMET puede devolver ISO-8859-1 o incluir BOM/bytes no válidos)
        if (!mb_detect_encoding($xmlContenido, 'UTF-8', true)) {
            $xmlContenido = mb_convert_encoding($xmlContenido, 'UTF-8', 'ISO-8859-1, Windows-1252');
        }
        // Eliminar BOM y caracteres de control no imprimibles al inicio
        $xmlContenido = preg_replace('/^\xEF\xBB\xBF/', '', $xmlContenido);
        $xmlContenido = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/u', '', $xmlContenido);

        // Parsear XML → Array con manejo de errores
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlContenido, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($xml === false) {
            $errors = libxml_get_errors();
            libxml_clear_errors();
            return [
                'error' => 'Error al parsear el XML CAP',
                'parser_errors' => array_map(fn($e) => $e->message, $errors)
            ];
        }
        $json = json_decode(json_encode($xml), true);

        // Normalizar recursivamente todas las cadenas a UTF-8 por seguridad
        $normalize = function (&$item) {
            if (is_string($item) && !mb_detect_encoding($item, 'UTF-8', true)) {
                $item = mb_convert_encoding($item, 'UTF-8', 'ISO-8859-1, Windows-1252');
            }
        };
        if (is_array($json)) {
            array_walk_recursive($json, $normalize);
        }

        // Filtrar datos útiles
        return [
            'identificador' => $json['identifier'] ?? null,
            'emisor'        => $json['sender'] ?? null,
            'fecha_inicio'  => $json['info']['effective'] ?? null,
            'fecha_fin'     => $json['info']['expires'] ?? null,
            'evento'        => $json['info']['event'] ?? null,
            'nivel'         => $json['info']['parameter']['value'] ?? null,
            'descripcion'   => $json['info']['description'] ?? null,
            'instrucciones' => $json['info']['instruction'] ?? null,
            'web'           => $json['info']['web'] ?? null
        ];
    }
}
