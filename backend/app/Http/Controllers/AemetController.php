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

        // 1ª llamada para obtener las URLs
        $initialResponse = Http::get($base_url . $endpoint, [
            'api_key' => $token,
        ]);

        if ($initialResponse->failed()) {
            return response()->json([
                'error' => 'Error en llamada inicial AEMET',
                'details' => $initialResponse->body()
            ], 500);
        }

        $body = $initialResponse->json();

        if (!isset($body['datos'])) {
            return response()->json(['error' => 'Respuesta inesperada de AEMET: falta "datos"'], 500);
        }

        // 2ª llamada para obtener los datos reales
        $dataResponse = Http::get($body['datos']);

        if ($dataResponse->failed()) {
            return response()->json([
                'error' => 'Error al obtener datos reales de AEMET',
                'details' => $dataResponse->body()
            ], 500);
        }

        // AEMET a veces devuelve JSON y otras veces texto
        $decoded = json_decode($dataResponse->body(), true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return response()->json($decoded);
        } else {
            return response($dataResponse->body(), 200)
                ->header('Content-Type', 'text/plain; charset=UTF-8');
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

            // 1ª llamada: obtener la URL de los datos
            $initialResponse = $client->get($baseUrl);
            $step1 = json_decode($initialResponse->getBody()->getContents(), true);

            if (!isset($step1['datos'])) {
                return response()->json([
                    'error' => 'No se encontró la URL de datos en la respuesta de AEMET.',
                    'respuesta' => $step1,
                ], 500);
            }

            // 2ª llamada: obtener los datos reales
            $dataResponse = $client->get($step1['datos']);
            $rawBody = $dataResponse->getBody()->getContents();

            // Forzar UTF-8 (AEMET suele devolver ISO-8859-1)
            $utf8Body = mb_convert_encoding($rawBody, 'UTF-8', 'ISO-8859-1');

            // Decodificar JSON
            $json = json_decode($utf8Body, true);

            // Siempre devolver JSON
            if (json_last_error() === JSON_ERROR_NONE) {
                return response()->json($json, 200, [], JSON_UNESCAPED_UNICODE);
            } else {
                return response()->json([
                    'warning' => 'Respuesta de AEMET no era JSON válido, se devolvió texto convertido a UTF-8',
                    'raw' => $utf8Body,
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error obteniendo la predicción de playa',
                'message' => $e->getMessage(),
            ], 500);
        }
    }



    /**
     * Predicción específica para montaña
     */
    public function prediccionMontana($area_montana, $dia_montana)
    {
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

        if (!array_key_exists($area_montana, $areasValidas)) {
            return response()->json(['error' => 'Parámetro area_montana inválido'], 400);
        }

        if (!in_array($dia_montana, ['0', '1', '2', '3'])) {
            return response()->json(['error' => 'Parámetro dia_montana inválido'], 400);
        }

        $base_url = 'https://opendata.aemet.es/opendata/api';
        $endpoint = "/prediccion/especifica/montaña/pasada/area/{$area_montana}/dia/{$dia_montana}";

        // 1ª llamada
        $response = Http::get($base_url . $endpoint, [
            'api_key' => $token,
        ]);

        if ($response->failed()) {
            return response()->json(['error' => 'Error en llamada inicial AEMET', 'details' => $response->body()], 500);
        }

        $body = $response->json();

        if (!isset($body['datos'])) {
            return response()->json(['error' => 'Respuesta inesperada de AEMET, falta "datos"'], 500);
        }

        // 2ª llamada
        $dataResponse = Http::get($body['datos']);

        if ($dataResponse->failed()) {
            return response()->json(['error' => 'Error al obtener datos reales de AEMET', 'details' => $dataResponse->body()], 500);
        }

        // Convertir de ISO-8859-1 a UTF-8
        $texto = mb_convert_encoding($dataResponse->body(), 'UTF-8', 'ISO-8859-1');

        // Intentar decodificar JSON (puede que venga como texto plano)
        $decoded = json_decode($texto, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $boletin = $decoded;
        } else {
            // Si no es JSON válido, devolver como texto tal cual
            $boletin = $texto;
        }

        return response()->json([
            'zona' => $areasValidas[$area_montana],
            'dia' => $dia_montana,
            'boletin' => $boletin
        ]);
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

            return response()->json($result, 200, [], JSON_UNESCAPED_UNICODE);
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
            return response()->json([
                'error' => 'Error en llamada inicial AEMET o respuesta inesperada.',
                'details' => $response->body()
            ], 500);
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
            return response()->json([
                'error' => 'Error al obtener datos reales de AEMET.',
                'details' => $dataResponse->body()
            ], 500);
        }

        $jsonString = mb_convert_encoding($dataResponse->body(), 'UTF-8', 'ISO-8859-1');
        $prediccion = json_decode($jsonString, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return response()->json([
                'error' => 'No se pudo decodificar el JSON.',
                'json_error' => json_last_error_msg(),
                'raw_response' => $jsonString
            ], 500);
        }

        if (!is_array($prediccion) || !isset($prediccion[0]['prediccion']['dia'])) {
            return response()->json([
                'error' => 'La estructura de la respuesta de AEMET no es la esperada.',
                'response' => $prediccion
            ], 500);
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

        function utf8ize($mixed)
        {
            if (is_array($mixed)) {
                foreach ($mixed as $key => $value) {
                    $mixed[$key] = utf8ize($value);
                }
            } elseif (is_string($mixed)) {
                return mb_convert_encoding($mixed, 'UTF-8', 'UTF-8');
            }
            return $mixed;
        }

        return response()->json(utf8ize($nuevoParte), 200);
    }
}
