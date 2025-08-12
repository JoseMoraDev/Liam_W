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

        // Parsear XML → Array
        $xml = simplexml_load_string($xmlContenido, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_decode(json_encode($xml), true);

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
