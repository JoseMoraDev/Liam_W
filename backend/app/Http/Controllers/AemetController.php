<?php

namespace App\Http\Controllers;

use Exception;
use ZipArchive;
use SimpleXMLElement;
use DateTimeImmutable;
use Illuminate\Http\Request;
use App\Services\AemetCapService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use App\Models\Endpoint;
use App\Models\UbicacionEndpointUsuario;

class AemetController extends Controller
{
    protected function getToken()
    {
        $tokenRecord = DB::table('tokens')->where('shortcode', 'aem')->first();
        return $tokenRecord ? $tokenRecord->token : null;
    }

    /**
     * Método genérico para llamar a cualquier endpoint de AEMET
     * que siga el sistema de doble llamada.
     */
    protected function fetchAemetData($endpoint)
    {
        $token = $this->getToken();
        if (!$token) {
            return response()->json(['error' => 'Token AEMET no encontrado'], 500);
        }

        $base_url = 'https://opendata.aemet.es/opendata/api';

        try {
            // 1ª llamada para obtener las URLs
            $initialResponse = Http::timeout(15)->get($base_url . $endpoint, [
                'api_key' => $token,
            ]);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'error' => 'No se pudo conectar con AEMET (fase inicial)'.'',
                'message' => $e->getMessage(),
            ], 502);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Fallo inesperado llamando a AEMET (fase inicial)'.'',
                'message' => $e->getMessage(),
            ], 502);
        }

        if ($initialResponse->failed()) {
            $body = $initialResponse->body();
            $snippet = mb_substr(strip_tags((string)$body), 0, 400);
            return response()->json([
                'error' => 'Error en llamada inicial AEMET',
                'details' => $snippet,
                'status' => $initialResponse->status(),
            ], 502);
        }

        $body = $initialResponse->json();
        if (!is_array($body)) {
            $snippet = mb_substr(strip_tags((string)$initialResponse->body()), 0, 400);
            return response()->json(['error' => 'Respuesta no JSON de AEMET en fase inicial', 'details' => $snippet], 502);
        }
        if (!isset($body['datos'])) {
            return response()->json(['error' => 'Respuesta inesperada de AEMET: falta "datos"'], 502);
        }

        // 2ª llamada para obtener los datos reales
        try {
            $dataResponse = Http::timeout(20)->get($body['datos']);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'error' => 'No se pudo conectar con AEMET (fase datos)'.'',
                'message' => $e->getMessage(),
            ], 502);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Fallo inesperado llamando a AEMET (fase datos)'.'',
                'message' => $e->getMessage(),
            ], 502);
        }

        if ($dataResponse->failed()) {
            $body2 = $dataResponse->body();
            $snippet2 = mb_substr(strip_tags((string)$body2), 0, 400);
            return response()->json([
                'error' => 'Error al obtener datos reales de AEMET',
                'details' => $snippet2,
                'status' => $dataResponse->status(),
            ], 502);
        }

        // AEMET a veces devuelve JSON y otras veces texto
        $raw = (string)$dataResponse->body();
        // Forzar UTF-8 si no lo es
        if (!mb_detect_encoding($raw, 'UTF-8', true)) {
            $raw = mb_convert_encoding($raw, 'UTF-8', 'ISO-8859-1, Windows-1252');
        }
        $decoded = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return response()->json($decoded, 200, ['Content-Type' => 'application/json; charset=UTF-8']);
        } else {
            // Evitar devolver HTML crudo: si parece HTML, devolver mensaje acotado
            if (stripos($raw, '<html') !== false) {
                $snippet = mb_substr(strip_tags($raw), 0, 400);
                return response()->json([
                    'warning' => 'AEMET devolvió HTML en lugar de JSON',
                    'preview' => $snippet,
                ], 502);
            }
            return response($raw, 200)->header('Content-Type', 'text/plain; charset=UTF-8');
        }
    }

    /**
     * Predicción nivológica
     */
    public function prediccionNivologica(Request $request, $area_nivologica)
    {
        if (!in_array($area_nivologica, ['0', '1'])) {
            return response()->json(['error' => 'Parámetro area_nivologica inválido. Solo 0 o 1'], 400);
        }

        $endpoint = "/prediccion/especifica/nivologica/{$area_nivologica}";

        $response = $this->fetchAemetData($endpoint);

        if ($response->status() !== 200) {
            return $response;
        }

        $contenido = $response->getContent();
        // Conversión de ISO-8859-1 a UTF-8 si es texto plano
        $contentType = $response->headers->get('Content-Type');
        if (str_contains($contentType, 'text/plain')) {
            $contenido = mb_convert_encoding($contenido, 'UTF-8', 'ISO-8859-1');
        } else {
            $contenido = json_decode($contenido, true);
        }

        $zona = $area_nivologica === '0' ? 'Pirineo Catalán' : 'Pirineo Navarro y Aragonés';

        return response()->json([
            'zona' => $zona,
            'boletin' => $contenido,
        ]);
    }

    /**
     * Predicción específica para playas
     */
    public function prediccionPlaya($id_playa)
    {
        try {
            $apiKey = env('AEMET_API_KEY');
            $baseUrl = "https://opendata.aemet.es/opendata/api/prediccion/especifica/playa/{$id_playa}?api_key={$apiKey}";

            // Cliente Guzzle con timeout y User-Agent
            $client = new \GuzzleHttp\Client([
                'timeout' => 20,
                'headers' => [
                    'User-Agent' => 'AEMET-Client/1.0 (+https://tuapp.local)'
                ]
            ]);

            // Cache: servir datos recientes si existen
            $cacheKey = "aemet.playa." . $id_playa;
            if (Cache::has($cacheKey)) {
                return response()->json(Cache::get($cacheKey), 200, [], JSON_UNESCAPED_UNICODE);
            }

            // 1ª llamada con reintentos básicos (manejo 429)
            $attempts = 0; $step1 = null; $initialResponse = null;
            do {
                $attempts++;
                try {
                    $initialResponse = $client->get($baseUrl);
                } catch (\GuzzleHttp\Exception\RequestException $e) {
                    $initialResponse = $e->getResponse();
                    if (!$initialResponse) throw $e;
                }
                if ($initialResponse->getStatusCode() == 429) {
                    $retryAfter = (int)($initialResponse->getHeaderLine('Retry-After') ?: 2);
                    sleep(min(max($retryAfter, 1), 5));
                } else {
                    break;
                }
            } while ($attempts < 3);

            // Evitar consumir el stream con getContents repetidas veces
            $initialRaw = (string)$initialResponse->getBody();
            $step1 = json_decode($initialRaw, true);

            if ($step1 === null) {
                // Respuesta no JSON (posible HTML 401/429/5xx). Devolver snippet para depurar
                $snippet = mb_substr(strip_tags($initialRaw), 0, 300);
                return response()->json([
                    'error' => 'Respuesta inicial de AEMET no es JSON válido.',
                    'status' => $initialResponse->getStatusCode(),
                    'preview' => $snippet,
                ], 502);
            }

            if (!isset($step1['datos'])) {
                return response()->json([
                    'error' => 'No se encontró la URL de datos en la respuesta de AEMET.',
                    'respuesta' => $step1,
                    'status' => $initialResponse->getStatusCode(),
                ], 502);
            }

            // 2ª llamada: obtener los datos reales (también con reintentos)
            $attempts = 0; $dataResponse = null;
            do {
                $attempts++;
                try {
                    $dataResponse = $client->get($step1['datos']);
                } catch (\GuzzleHttp\Exception\RequestException $e) {
                    $resp = $e->getResponse();
                    if ($resp && $resp->getStatusCode() == 429) {
                        $retryAfter = (int)($resp->getHeaderLine('Retry-After') ?: 2);
                        sleep(min(max($retryAfter, 1), 5));
                        continue;
                    }
                    throw $e;
                }
                if ($dataResponse->getStatusCode() == 429) {
                    $retryAfter = (int)($dataResponse->getHeaderLine('Retry-After') ?: 2);
                    sleep(min(max($retryAfter, 1), 5));
                } else {
                    break;
                }
            } while ($attempts < 3);
            $rawBody = $dataResponse->getBody()->getContents();

            // Forzar UTF-8 (AEMET suele devolver ISO-8859-1)
            $utf8Body = mb_convert_encoding($rawBody, 'UTF-8', 'ISO-8859-1');

            // Decodificar JSON
            $json = json_decode($utf8Body, true);

            // Siempre devolver JSON
            if (json_last_error() === JSON_ERROR_NONE) {
                // Cachear 10 minutos para evitar rate limit
                Cache::put($cacheKey, $json, now()->addMinutes(10));
                return response()->json($json, 200, [], JSON_UNESCAPED_UNICODE);
            } else {
                $payload = [
                    'warning' => 'Respuesta de AEMET no era JSON válido, se devolvió texto convertido a UTF-8',
                    'raw' => $utf8Body,
                ];
                Cache::put($cacheKey, $payload, now()->addMinutes(5));
                return response()->json($payload, 200);
            }
        } catch (\Exception $e) {
            // Fallback a cache si existe
            $cacheKey = "aemet.playa." . $id_playa;
            if (Cache::has($cacheKey)) {
                return response()->json(array_merge(['cached' => true], Cache::get($cacheKey)), 200, [], JSON_UNESCAPED_UNICODE);
            }
            return response()->json([
                'error' => 'Error obteniendo la predicción de playa',
                'message' => $e->getMessage(),
            ], 500);
        }
    }



    /**
     * Predicción específica para montaña
     */
    public function prediccionMontana(Request $request, $area_montana, $dia_montana)
    {
        try {
            Log::debug('[AEMET][Montaña] inicio', ['area' => $area_montana, 'dia' => $dia_montana, 'q' => $request->query()]);
            $token = $this->getToken();
            if (!$token) {
                return response()->json(['error' => 'Token AEMET no encontrado'], 500);
            }

        // Lista de áreas válidas
        $areasValidas = [
            'peu1' => 'Picos de Europa',
            'nav1' => 'Pirineo Navarro',
            'arn1' => 'Pirineo Aragonés',
            'cat1' => 'Pirineo Catalán',
            'rio1' => 'Ibérica Riojana',
            'arn2' => 'Ibérica Aragonesa',
            'mad2' => 'Sierras de Guadarrama y Somosierra',
            'gre1' => 'Sierra de Gredos',
            'nev1' => 'Sierra Nevada',
        ];

        // Permitir 'auto' para usar el area_code guardado en preferencias de usuario
        if ($area_montana === 'auto') {
            $uid = $request->query('user_id');
            $pref = null;
            if ($uid) {
                $pref = \App\Models\UserLocationPref::where('user_id', $uid)->first();
            }
            if (!$pref) {
                $pref = \App\Models\UserLocationPref::orderByDesc('updated_at')->first();
            }
            if ($pref && !empty($pref->area_code)) {
                $area_montana = $pref->area_code;
                Log::debug('[AEMET][Montaña] resuelta area por auto', ['area' => $area_montana]);
            }
        }

        if (!array_key_exists($area_montana, $areasValidas)) {
            return response()->json(['error' => 'Parámetro area_montana inválido', 'area_montana' => $area_montana], 400);
        }

        if (!in_array($dia_montana, ['0', '1', '2', '3'])) {
            return response()->json(['error' => 'Parámetro dia_montana inválido'], 400);
        }

        $base_url = 'https://opendata.aemet.es/opendata/api';
        // Requerido por AEMET en este despliegue: usar 'montaña' con tilde
        $endpoint = "/prediccion/especifica/montaña/pasada/area/{$area_montana}/dia/{$dia_montana}";

        // 1ª llamada
        Log::debug('[AEMET][Montaña] endpoint', ['endpoint' => $endpoint]);
        $response = Http::timeout(15)->get($base_url . $endpoint, [
            'api_key' => $token,
        ]);

        if ($response->failed()) {
            Log::error('[AEMET][Montaña] fallo llamada inicial', ['status' => $response->status(), 'body' => $response->body()]);
            return response()->json(['error' => 'Error en llamada inicial AEMET', 'details' => $response->body()], 500);
        }

        $body = $response->json();
        if ($body === null) {
            Log::error('[AEMET][Montaña] cuerpo no JSON en primera llamada', ['raw' => $response->body()]);
        }

        if (!isset($body['datos'])) {
            return response()->json(['error' => 'Respuesta inesperada de AEMET, falta "datos"'], 500);
        }

        // 2ª llamada
        $dataResponse = Http::timeout(20)->get($body['datos']);

        if ($dataResponse->failed()) {
            Log::error('[AEMET][Montaña] fallo datos reales', ['status' => $dataResponse->status(), 'body' => $dataResponse->body()]);
            return response()->json(['error' => 'Error al obtener datos reales de AEMET', 'details' => $dataResponse->body()], 500);
        }

        // Normalizar codificación a UTF-8 solo si es necesario
        $raw = $dataResponse->body();
        if (!mb_detect_encoding($raw, 'UTF-8', true)) {
            $raw = mb_convert_encoding($raw, 'UTF-8', 'ISO-8859-1, Windows-1252');
        }

        // Intentar decodificar JSON (puede venir como texto plano)
        $decoded = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $boletin = $decoded;
            // Normalizar recursivamente strings a UTF-8 por seguridad
            $normalize = function (&$item) {
                if (is_string($item) && !mb_detect_encoding($item, 'UTF-8', true)) {
                    $item = mb_convert_encoding($item, 'UTF-8', 'ISO-8859-1, Windows-1252');
                }
            };
            array_walk_recursive($boletin, $normalize);
        } else {
            // Si no es JSON válido, devolver como texto UTF-8
            $text = $raw;
            if (!mb_detect_encoding($text, 'UTF-8', true)) {
                $text = mb_convert_encoding($text, 'UTF-8', 'ISO-8859-1, Windows-1252');
            }
            $boletin = $text;
        }

        $payload = [
            'zona' => $areasValidas[$area_montana],
            'dia' => $dia_montana,
            'boletin' => $boletin
        ];
        // Normalizar payload completo a UTF-8 por seguridad
        $normalize = function (&$item) {
            if (is_string($item) && !mb_detect_encoding($item, 'UTF-8', true)) {
                $item = mb_convert_encoding($item, 'UTF-8', 'ISO-8859-1, Windows-1252');
            }
        };
        array_walk_recursive($payload, $normalize);

        return response()->json($payload, 200, ['Content-Type' => 'application/json; charset=UTF-8'], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        } catch (\Throwable $e) {
            Log::error('[AEMET][Montaña] excepción inesperada', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return response()->json(['error' => 'Error inesperado en prediccionMontana', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Predicción temperatura nivel del mar en .png
     */
    public function temperaturaSuperficieMar(Request $request)
    {
        $token = $this->getToken();
        if (!$token) {
            return response()->json(['error' => 'Token AEMET no encontrado'], 500);
        }

        $base_url = 'https://opendata.aemet.es/opendata/api';
        $endpoint = '/satelites/producto/sst';

        // 1ª llamada para obtener la URL firmada
        $response = Http::timeout(10)->get($base_url . $endpoint, [
            'api_key' => $token,
        ]);

        if ($response->failed()) {
            return response()->json([
                'error' => 'Error en llamada inicial AEMET',
                'details' => $response->body()
            ], 500);
        }

        $body = $response->json();

        if (!isset($body['datos'])) {
            return response()->json([
                'error' => 'Respuesta inesperada de AEMET, falta "datos"',
                'raw' => $body
            ], 500);
        }

        $imageUrl = $body['datos'];

        // 2ª llamada para obtener el PNG real
        $imageResponse = Http::timeout(15)->get($imageUrl);
        if ($imageResponse->failed()) {
            return response()->json([
                'error' => 'Error al obtener imagen SST',
                'details' => $imageResponse->body()
            ], 500);
        }

        // Validar que realmente devuelve una imagen
        $contentType = $imageResponse->header('Content-Type');
        if (!str_starts_with($contentType, 'image/')) {
            return response()->json([
                'error' => 'Respuesta inesperada al obtener la imagen',
                'content_type' => $contentType,
                'body' => $imageResponse->body(),
            ], 500);
        }

        // ¿Quiere inline base64?
        $inline = $request->query('inline', false);

        $result = [
            'url' => $imageUrl,   // enlace directo para usar en <img src="">
        ];

        if ($inline) {
            $result['base64'] = 'data:image/png;base64,' . base64_encode($imageResponse->body());
        }

        return response()->json($result);
    }


    public function avisosCapUltimoElaborado($area, $format = 'xml')
    {
        // Lista de códigos de autonomías válidos
        $autonomias = [
            'esp' => 'España',
            '61' => 'Andalucía',
            '62' => 'Aragón',
            '63' => 'Asturias, Principado de',
            '64' => 'Baleares, Illes',
            '78' => 'Ceuta',
            '65' => 'Canarias',
            '66' => 'Cantabria',
            '67' => 'Castilla y León',
            '68' => 'Castilla - La Mancha',
            '69' => 'Cataluña',
            '77' => 'Comunitat Valenciana',
            '70' => 'Extremadura',
            '71' => 'Galicia',
            '72' => 'Madrid, Comunidad de',
            '79' => 'Melilla',
            '73' => 'Murcia, Región de',
            '74' => 'Navarra, Comunidad Foral de',
            '75' => 'País Vasco',
            '76' => 'Rioja, La',
        ];

        // Validación del código de autonomía
        if (!array_key_exists($area, $autonomias)) {
            return response()->json([
                'error' => 'Código de autonomía no válido.',
                'message' => "El código de autonomía '$area' no existe.",
                'available_codes' => array_keys($autonomias),
            ], 400); // Usamos 400 Bad Request
        }

        $token = $this->getToken();
        if (!$token) {
            return response()->json(['error' => 'Token AEMET no encontrado'], 500);
        }

        $base_url = 'https://opendata.aemet.es/opendata/api';
        $endpoint = "/avisos_cap/ultimoelaborado/area/{$area}";

        // 1ª llamada
        $response = Http::get($base_url . $endpoint, [
            'api_key' => $token,
            'format' => $format
        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Error en llamada inicial AEMET', 'details' => $response->body()], 500);
        }

        $body = $response->json();
        if (!isset($body['datos'])) {
            return response()->json(['error' => 'Respuesta inesperada de AEMET, falta "datos"', 'response' => $body], 500);
        }

        // 2ª llamada (obtiene el XML o ZIP)
        $dataResponse = Http::get($body['datos']);
        if ($dataResponse->failed()) {
            return response()->json([
                'error' => 'La API devolvió un error o contenido no válido',
                'response' => $dataResponse->body()
            ], 500);
        }

        $data = $dataResponse->body();
        $xmlContent = null;

        // Detección de ZIP por cabecera "PK"
        if (substr($data, 0, 2) === "PK") {
            try {
                $tmpFile = tempnam(sys_get_temp_dir(), 'aemet_zip_');
                file_put_contents($tmpFile, $data);

                $zip = new \ZipArchive;
                if ($zip->open($tmpFile) === true) {
                    $xmlContent = $zip->getFromIndex(0);
                    $zip->close();
                }
                unlink($tmpFile);

                if (!$xmlContent) {
                    return response()->json(['error' => 'ZIP recibido pero sin contenido XML'], 500);
                }
            } catch (Exception $e) {
                return response()->json(['error' => 'Error al procesar el archivo ZIP.', 'message' => $e->getMessage()], 500);
            }
        } else {
            // Si no es un ZIP, asumimos que es el XML directamente
            $xmlContent = $data;
        }

        // Normalizar a UTF-8 SOLO si no es ya UTF-8, para evitar mojibake
        if (!empty($xmlContent) && !mb_detect_encoding($xmlContent, 'UTF-8', true)) {
            $xmlContent = mb_convert_encoding($xmlContent, 'UTF-8', 'ISO-8859-1');
        }
        return $this->sintetizarAvisosCap($xmlContent);
    }

    private function sintetizarAvisosCap(string $xml_data): \Illuminate\Http\JsonResponse
    {
        try {
            // Paso clave de limpieza: Elimina caracteres no imprimibles y el encabezado.
            $start_pos = strpos($xml_data, '<');

            if ($start_pos === false) {
                return response()->json(['error' => 'No se encontró la etiqueta de inicio XML (<).'], 500);
            }

            $cleaned_xml_data = substr($xml_data, $start_pos);

            // Asegurar codificación UTF-8 antes de parsear (sólo si no es ya UTF-8)
            if (!mb_detect_encoding($cleaned_xml_data, 'UTF-8', true)) {
                $cleaned_xml_data = mb_convert_encoding($cleaned_xml_data, 'UTF-8', 'ISO-8859-1');
            }

            if (empty($cleaned_xml_data)) {
                return response()->json(['error' => 'La respuesta XML está vacía después de la limpieza.'], 500);
            }

            libxml_use_internal_errors(true);
            $xml = simplexml_load_string($cleaned_xml_data);

            if ($xml === false) {
                $errors = libxml_get_errors();
                libxml_clear_errors();
                $error_messages = array_map(fn($e) => $e->message, $errors);

                return response()->json([
                    'error' => 'Error al parsear el XML del aviso.',
                    'message' => 'El XML no es válido.',
                    'parser_errors' => $error_messages
                ], 500);
            }

            // Extracción y filtro de la información
            $result = [
                'identifier' => (string)$xml->identifier,
                'sent' => (string)$xml->sent,
                'status' => (string)$xml->status,
                'msgType' => (string)$xml->msgType,
            ];

            // Recorre los bloques de información y guarda solo el de español.
            $spanish_info = null;
            foreach ($xml->info as $info) {
                if ((string)$info->language === 'es-ES') {
                    $spanish_info = $info;
                    break; // Detiene el bucle una vez que encuentra el español.
                }
            }

            // Si se encontró el bloque en español, lo procesa.
            if ($spanish_info) {
                $parameters = [];
                foreach ($spanish_info->parameter as $param) {
                    $parameters[(string)$param->valueName] = (string)$param->value;
                }
                $eventCodes = [];
                foreach ($spanish_info->eventCode as $eventCode) {
                    $eventCodes[(string)$eventCode->valueName] = (string)$eventCode->value;
                }
                $geocodes = [];
                foreach ($spanish_info->area->geocode as $geocode) {
                    $geocodes[(string)$geocode->valueName] = (string)$geocode->value;
                }

                $info_data = [
                    'language' => (string)$spanish_info->language,
                    'event' => (string)$spanish_info->event,
                    'severity' => (string)$spanish_info->severity,
                    'certainty' => (string)$spanish_info->certainty,
                    'effective' => (string)$spanish_info->effective,
                    'onset' => (string)$spanish_info->onset,
                    'expires' => (string)$spanish_info->expires,
                    'senderName' => (string)$spanish_info->senderName,
                    'headline' => (string)$spanish_info->headline,
                    'description' => (string)$spanish_info->description,
                    'instruction' => (string)$spanish_info->instruction,
                    'web' => (string)$spanish_info->web,
                    'area' => [
                        'areaDesc' => (string)$spanish_info->area->areaDesc,
                        'polygon' => (string)$spanish_info->area->polygon,
                        'geocodes' => $geocodes
                    ],
                    'parameters' => [
                        'level' => $parameters['AEMET-Meteoalerta nivel'] ?? null,
                        'parameter' => $parameters['AEMET-Meteoalerta parametro'] ?? null,
                        'probability' => $parameters['AEMET-Meteoalerta probabilidad'] ?? null,
                    ],
                    'eventCode' => $eventCodes
                ];
                $result['info'] = $info_data;
            }

            // Normalizar recursivamente a UTF-8 por seguridad
            $normalize = function (&$item) {
                if (is_string($item) && !mb_detect_encoding($item, 'UTF-8', true)) {
                    $item = mb_convert_encoding($item, 'UTF-8', 'ISO-8859-1, Windows-1252');
                }
            };
            array_walk_recursive($result, $normalize);

            return response()->json($result, 200, ['Content-Type' => 'application/json; charset=UTF-8'], JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Error inesperado al procesar el XML.',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function prediccionDiariaMunicipio($municipioId = '03065')
    {
        $token = $this->getToken();
        if (!$token) {
            return response()->json(['error' => 'Token AEMET no encontrado'], 500);
        }

        $baseUrl = 'https://opendata.aemet.es/opendata/api';
        $endpoint = "/prediccion/especifica/municipio/diaria/{$municipioId}";

        // 1ª llamada
        $response = Http::get($baseUrl . $endpoint, [
            'api_key' => $token
        ]);

        if ($response->failed()) {
            return response()->json([
                'error' => 'Error en llamada inicial AEMET',
                'details' => $response->body()
            ], 500);
        }

        $body = $response->json();
        if (!isset($body['datos'])) {
            return response()->json([
                'error' => 'Respuesta inesperada de AEMET, falta "datos"',
                'response' => $body
            ], 500);
        }

        // 2ª llamada
        $dataResponse = Http::get($body['datos']);
        if ($dataResponse->failed()) {
            return response()->json([
                'error' => 'Error al obtener datos reales de AEMET',
                'details' => $dataResponse->body()
            ], 500);
        }

        // Convertir a UTF-8 antes de decodificar
        $jsonString = mb_convert_encoding($dataResponse->body(), 'UTF-8', 'ISO-8859-1');
        $prediccion = json_decode($jsonString, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                'error' => 'No se pudo decodificar el JSON',
                'json_error' => json_last_error_msg()
            ], 500);
        }

        // Parte útil
        $parteUtil = $prediccion[0]['prediccion']['dia'] ?? [];

        $hoy = new DateTimeImmutable('today'); // siempre 00:00:00 del día actual
        $resultado = [];

        foreach ($parteUtil as $dia) {
            $fechaDia = new DateTimeImmutable(substr($dia['fecha'], 0, 10));

            if ($fechaDia >= $hoy) {
                $resultado[] = [
                    'fecha' => $fechaDia->format('Y-m-d'),
                    'probPrecipitacion' => $dia['probPrecipitacion'] ?? [],
                    'estadoCielo' => $dia['estadoCielo'] ?? [],
                    'temperatura' => $dia['temperatura'] ?? []
                ];
            }
        }

        return response()->json($resultado, 200, [], JSON_UNESCAPED_UNICODE);
    }



    // public function prediccionHorariaMunicipio($municipioId)
    // {
    //     $token = $this->getToken();
    //     if (!$token) {
    //         return response()->json(['error' => 'Token AEMET no encontrado'], 500);
    //     }

    //     $baseUrl = 'https://opendata.aemet.es/opendata/api';
    //     $endpoint = "/prediccion/especifica/municipio/horaria/{$municipioId}";

    //     $response = Http::get($baseUrl . $endpoint, ['api_key' => $token]);
    //     if ($response->failed() || !isset($response->json()['datos'])) {
    //         return response()->json([
    //             'error' => 'Error en llamada inicial AEMET o respuesta inesperada.',
    //             'details' => $response->body()
    //         ], 500);
    //     }

    //     $dataUrl = $response->json()['datos'];

    //     try {
    //         $dataResponse = Http::retry(3, 1000)->timeout(15)->connectTimeout(10)->get($dataUrl);
    //     } catch (\Illuminate\Http\Client\ConnectionException $e) {
    //         return response()->json([
    //             'error' => 'Fallo de conexión con AEMET.',
    //             'message' => $e->getMessage()
    //         ], 502);
    //     }

    //     if ($dataResponse->failed()) {
    //         return response()->json([
    //             'error' => 'Error al obtener datos reales de AEMET.',
    //             'details' => $dataResponse->body()
    //         ], 500);
    //     }

    //     $jsonString = mb_convert_encoding($dataResponse->body(), 'UTF-8', 'ISO-8859-1');
    //     $prediccion = json_decode($jsonString, true);

    //     if (json_last_error() !== JSON_ERROR_NONE) {
    //         return response()->json([
    //             'error' => 'No se pudo decodificar el JSON.',
    //             'json_error' => json_last_error_msg(),
    //             'raw_response' => $jsonString
    //         ], 500);
    //     }

    //     if (!is_array($prediccion) || !isset($prediccion[0]['prediccion']['dia'])) {
    //         return response()->json([
    //             'error' => 'La estructura de la respuesta de AEMET no es la esperada.',
    //             'response' => $prediccion
    //         ], 500);
    //     }

    //     $parteUtil = $prediccion[0]['prediccion']['dia'] ?? [];
    //     $nuevoParte = [];

    //     // Hora actual +2 para corregir desfase
    //     $horaActual = (intval(date('H')) + 2) % 24;

    //     foreach ($parteUtil as $dia) {
    //         $nuevoDia = [];

    //         foreach ($dia as $clave => $bloque) {
    //             if (!is_array($bloque)) {
    //                 $nuevoDia[$clave] = $bloque;
    //                 continue;
    //             }

    //             $nuevoBloque = [];

    //             foreach ($bloque as $entrada) {
    //                 $valor = $entrada['value'] ?? null;
    //                 $periodo = $entrada['periodo'] ?? null;

    //                 if (!$periodo) {
    //                     for ($h = 0; $h < 24; $h++) {
    //                         if ($h >= $horaActual) {
    //                             $nuevoBloque[] = [
    //                                 'periodo' => str_pad($h, 2, '0', STR_PAD_LEFT),
    //                                 'value' => $valor
    //                             ];
    //                         }
    //                     }
    //                     continue;
    //                 }

    //                 if (strlen($periodo) == 4) {
    //                     $inicio = intval(substr($periodo, 0, 2));
    //                     $fin = intval(substr($periodo, 2, 2));
    //                     if ($fin < $inicio) $fin += 24;

    //                     for ($h = $inicio; $h < $fin; $h++) {
    //                         $hora = $h % 24;
    //                         if ($hora >= $horaActual) {
    //                             $nuevoBloque[] = [
    //                                 'periodo' => str_pad($hora, 2, '0', STR_PAD_LEFT),
    //                                 'value' => $valor
    //                             ];
    //                         }
    //                     }
    //                     continue;
    //                 }

    //                 $hora = intval($periodo);
    //                 if ($hora >= $horaActual) {
    //                     $nuevoBloque[] = [
    //                         'periodo' => str_pad($hora, 2, '0', STR_PAD_LEFT),
    //                         'value' => $valor
    //                     ];
    //                 }
    //             }

    //             // Eliminar duplicados y ordenar
    //             $horas = [];
    //             foreach ($nuevoBloque as $item) {
    //                 $horas[$item['periodo']] = $item;
    //             }

    //             ksort($horas);
    //             $nuevoDia[$clave] = array_values($horas);
    //         }

    //         // Filtrar solo horas que tengan algún valor
    //         $horasValidas = [];
    //         for ($h = $horaActual; $h < 24; $h++) {
    //             $horaTxt = str_pad($h, 2, '0', STR_PAD_LEFT);

    //             $hayDatos = false;
    //             $camposComprobacion = ['temperatura', 'sensTermica', 'vientoAndRachaMax', 'humedadRelativa', 'precipitacion', 'nieve'];
    //             foreach ($camposComprobacion as $campo) {
    //                 if (isset($nuevoDia[$campo])) {
    //                     foreach ($nuevoDia[$campo] as $v) {
    //                         if ($v['periodo'] === $horaTxt && isset($v['value']) && $v['value'] !== '—' && $v['value'] !== null && $v['value'] !== '') {
    //                             $hayDatos = true;
    //                             break 2;
    //                         }
    //                     }
    //                 }
    //             }

    //             if ($hayDatos) {
    //                 $horasValidas[] = $horaTxt;
    //             }
    //         }

    //         foreach ($nuevoDia as $clave => &$bloque) {
    //             if (is_array($bloque)) {
    //                 $bloque = array_values(array_filter($bloque, function ($v) use ($horasValidas) {
    //                     return in_array($v['periodo'], $horasValidas);
    //                 }));
    //             }
    //         }
    //         unset($bloque);

    //         $nuevoParte[] = $nuevoDia;
    //     }

    //     // Normalizar UTF-8 para evitar errores
    //     function utf8ize($mixed)
    //     {
    //         if (is_array($mixed)) {
    //             foreach ($mixed as $key => $value) {
    //                 $mixed[$key] = utf8ize($value);
    //             }
    //         } elseif (is_string($mixed)) {
    //             return mb_convert_encoding($mixed, 'UTF-8', 'UTF-8');
    //         }
    //         return $mixed;
    //     }

    //     return response()->json(utf8ize($nuevoParte), 200);
    // }




    public function getMunicipiosByProvincia($provincia)
    {
        $municipios = \App\Models\Municipio::where('cpro', $provincia)->get();
        return response()->json($municipios);
    }

    public function prediccionHorariaMunicipio($municipioId)
    {
        $token = $this->getToken();
        if (!$token) {
            return response()->json(['error' => 'Token AEMET no encontrado'], 500);
        }

        $baseUrl = 'https://opendata.aemet.es/opendata/api';
        $endpoint = "/prediccion/especifica/municipio/horaria/{$municipioId}";

        $response = Http::get($baseUrl . $endpoint, ['api_key' => $token]);
        if ($response->failed() || !isset($response->json()['datos'])) {
            $details = $response->body();
            if (!mb_detect_encoding($details, 'UTF-8', true)) {
                $details = mb_convert_encoding($details, 'UTF-8', 'ISO-8859-1, Windows-1252');
            }
            if (function_exists('iconv')) {
                $tmp = @iconv('UTF-8', 'UTF-8//IGNORE', $details);
                if ($tmp !== false) { $details = $tmp; }
            }
            $payload = ['error' => 'Error en llamada inicial AEMET o respuesta inesperada.', 'details' => $details];
            $json = json_encode($payload, \JSON_UNESCAPED_UNICODE | \JSON_INVALID_UTF8_SUBSTITUTE);
            return response($json, 500)->header('Content-Type', 'application/json; charset=UTF-8');
        }

        $dataUrl = $response->json()['datos'];

        try {
            $dataResponse = Http::retry(3, 1000)->timeout(15)->connectTimeout(10)->get($dataUrl);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'error' => 'Fallo de conexión con AEMET.',
                'message' => $e->getMessage()
            ], 502);
        }

        if ($dataResponse->failed()) {
            $details = $dataResponse->body();
            if (!mb_detect_encoding($details, 'UTF-8', true)) {
                $details = mb_convert_encoding($details, 'UTF-8', 'ISO-8859-1, Windows-1252');
            }
            if (function_exists('iconv')) {
                $tmp = @iconv('UTF-8', 'UTF-8//IGNORE', $details);
                if ($tmp !== false) { $details = $tmp; }
            }
            $payload = ['error' => 'Error al obtener datos reales de AEMET.', 'details' => $details];
            $json = json_encode($payload, \JSON_UNESCAPED_UNICODE | \JSON_INVALID_UTF8_SUBSTITUTE);
            return response($json, 500)->header('Content-Type', 'application/json; charset=UTF-8');
        }

        $jsonString = mb_convert_encoding($dataResponse->body(), 'UTF-8', 'ISO-8859-1');
        $prediccion = json_decode($jsonString, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $rawSafe = $jsonString;
            if (!mb_detect_encoding($rawSafe, 'UTF-8', true)) {
                $rawSafe = mb_convert_encoding($rawSafe, 'UTF-8', 'ISO-8859-1, Windows-1252');
            }
            if (function_exists('iconv')) {
                $tmp = @iconv('UTF-8', 'UTF-8//IGNORE', $rawSafe);
                if ($tmp !== false) { $rawSafe = $tmp; }
            }
            $payload = [
                'error' => 'No se pudo decodificar el JSON.',
                'json_error' => json_last_error_msg(),
                'raw_response' => $rawSafe
            ];
            $json = json_encode($payload, \JSON_UNESCAPED_UNICODE | \JSON_INVALID_UTF8_SUBSTITUTE);
            return response($json, 500)->header('Content-Type', 'application/json; charset=UTF-8');
        }

        if (!is_array($prediccion) || !isset($prediccion[0]['prediccion']['dia'])) {
            $payload = [
                'error' => 'La estructura de la respuesta de AEMET no es la esperada.',
                'response' => $prediccion
            ];
            $json = json_encode($payload, \JSON_UNESCAPED_UNICODE | \JSON_INVALID_UTF8_SUBSTITUTE);
            return response($json, 500)->header('Content-Type', 'application/json; charset=UTF-8');
        }

        $parteUtil = $prediccion[0]['prediccion']['dia'] ?? [];
        $nuevoParte = [];

        $horaActual = (intval(date('H')) + 2) % 24;
        $hoy = date('Y-m-d');

        foreach ($parteUtil as $dia) {
            $nuevoDia = [];

            foreach ($dia as $clave => $bloque) {
                if (!is_array($bloque)) {
                    $nuevoDia[$clave] = $bloque;
                    continue;
                }

                $nuevoBloque = [];

                foreach ($bloque as $entrada) {
                    $valor = $entrada['value'] ?? null;
                    $periodo = $entrada['periodo'] ?? null;

                    if (!$periodo) {
                        // Día actual: horas desde horaActual
                        $inicio = ($dia['fecha'] === $hoy) ? $horaActual : 0;
                        for ($h = $inicio; $h < 24; $h++) {
                            $nuevoBloque[] = [
                                'periodo' => str_pad($h, 2, '0', STR_PAD_LEFT),
                                'value' => $valor
                            ];
                        }
                        continue;
                    }

                    if (strlen($periodo) == 4) {
                        $inicio = intval(substr($periodo, 0, 2));
                        $fin = intval(substr($periodo, 2, 2));
                        if ($fin < $inicio) $fin += 24;

                        for ($h = $inicio; $h < $fin; $h++) {
                            $hora = $h % 24;
                            if ($dia['fecha'] === $hoy && $hora < $horaActual) continue;
                            $nuevoBloque[] = [
                                'periodo' => str_pad($hora, 2, '0', STR_PAD_LEFT),
                                'value' => $valor
                            ];
                        }
                        continue;
                    }

                    $hora = intval($periodo);
                    if ($dia['fecha'] === $hoy && $hora < $horaActual) continue;

                    $nuevoBloque[] = [
                        'periodo' => str_pad($hora, 2, '0', STR_PAD_LEFT),
                        'value' => $valor
                    ];
                }

                $horas = [];
                foreach ($nuevoBloque as $item) {
                    $horas[$item['periodo']] = $item;
                }

                ksort($horas);
                $nuevoDia[$clave] = array_values($horas);
            }

            $nuevoParte[] = $nuevoDia;
        }

        // Normalizar recursivamente a UTF-8 para evitar 'Malformed UTF-8'
        $normalize = function (&$item) {
            if (is_string($item)) {
                // Primero intentar detectar/convertir a UTF-8
                if (!mb_detect_encoding($item, 'UTF-8', true)) {
                    $item = mb_convert_encoding($item, 'UTF-8', 'ISO-8859-1, Windows-1252, UTF-8');
                } else {
                    $item = mb_convert_encoding($item, 'UTF-8', 'UTF-8');
                }
                // Luego purgar bytes inválidos si existieran
                if (function_exists('iconv')) {
                    $tmp = @iconv('UTF-8', 'UTF-8//IGNORE', $item);
                    if ($tmp !== false) { $item = $tmp; }
                }
            }
        };
        array_walk_recursive($nuevoParte, $normalize);

        // Usar sustitución de UTF-8 inválido por seguridad en el JSON final
        $json = json_encode($nuevoParte, \JSON_UNESCAPED_UNICODE | \JSON_INVALID_UTF8_SUBSTITUTE);
        if ($json === false) {
            // Último recurso: convertir todo a string seguro
            $fallback = ['error' => 'Encoding error', 'message' => json_last_error_msg()];
            $json = json_encode($fallback, \JSON_UNESCAPED_UNICODE);
        }
        // Atribuir uso al usuario autenticado (si lo hay) para este endpoint de negocio
        try {
            $user = Auth::user();
            // Route is public; try to resolve user from Sanctum bearer token if not authenticated by middleware
            if (!$user) {
                $bearer = request()->bearerToken();
                if ($bearer && strpos($bearer, '|') !== false) {
                    $parts = explode('|', $bearer, 2);
                    $plain = $parts[1] ?? null;
                    if ($plain) {
                        $hash = hash('sha256', $plain);
                        $pat = DB::table('personal_access_tokens')->where('token', $hash)->first();
                        if ($pat && isset($pat->tokenable_type, $pat->tokenable_id) && $pat->tokenable_type === \App\Models\User::class) {
                            $user = \App\Models\User::find($pat->tokenable_id);
                        }
                    }
                }
            }
            if ($user) {
                $route = request()->route();
                $uri = null;
                if ($route) {
                    $uri = method_exists($route, 'uri') ? $route->uri() : ($route->uri ?? null);
                }
                $uri = $uri ?: 'api/prediccion/horaria/{municipioId}';
                $ruta = '/' . ltrim($uri, '/');

                $endpoint = Endpoint::firstOrCreate(
                    ['url' => $ruta],
                    ['name' => $ruta, 'tipo' => 'public', 'url' => $ruta]
                );

                $rec = UbicacionEndpointUsuario::firstOrCreate(
                    [
                        'user_id' => $user->id,
                        'endpoint_id' => $endpoint->id,
                        'tipo_ubicacion' => 'sin_ubicacion',
                    ],
                    [
                        'usos' => 0,
                    ]
                );
                $rec->increment('usos');
            }
        } catch (\Throwable $e) {
            // no romper respuesta si falla el tracking
        }

        return response($json, 200)->header('Content-Type', 'application/json; charset=UTF-8');
    }
}
