<?php

namespace App\Http\Controllers;

use App\Models\UbicacionEndpointUsuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class UbicacionEndpointUsuarioController extends Controller
{
    // Guardar o actualizar uso de una ubicación
    public function storeOrUpdate(Request $request)
    {
        $data = $request->validate([
            'endpoint_id' => 'required|exists:endpoints,id',
            'tipo_ubicacion' => 'required|in:coordenadas,municipio',
            'valor_lat' => 'nullable|numeric',
            'valor_lon' => 'nullable|numeric',
            'valor_id_municipio' => 'nullable|string',
            'nombre_amigable' => 'nullable|string|max:255',
        ]);

        $userId = Auth::id();

        if ($data['tipo_ubicacion'] === 'coordenadas' && $data['valor_lat'] && $data['valor_lon']) {
            // Redondeamos para margen de tolerancia
            $lat = round($data['valor_lat'], 2);
            $lon = round($data['valor_lon'], 2);

            // Geocodificación inversa para nombre amigable si no se envía
            $nombreAmigable = $data['nombre_amigable'] ?? null;
            if (!$nombreAmigable) {
                $response = Http::get('https://nominatim.openstreetmap.org/reverse', [
                    'lat' => $lat,
                    'lon' => $lon,
                    'format' => 'json'
                ]);

                if ($response->successful() && isset($response['display_name'])) {
                    $nombreAmigable = $response['display_name'];
                } else {
                    $nombreAmigable = "Coordenadas: $lat, $lon";
                }
            }
        } else {
            $lat = null;
            $lon = null;
            $nombreAmigable = $data['nombre_amigable'] ?? null;
        }

        // Buscar si ya existe la ubicación
        $ubicacion = UbicacionEndpointUsuario::where('user_id', $userId)
            ->where('endpoint_id', $data['endpoint_id'])
            ->when($data['tipo_ubicacion'] === 'coordenadas', function ($query) use ($lat, $lon) {
                $query->where('valor_lat', $lat)
                    ->where('valor_lon', $lon);
            })
            ->when($data['tipo_ubicacion'] === 'municipio', function ($query) use ($data) {
                $query->where('valor_id_municipio', $data['valor_id_municipio']);
            })
            ->first();

        if ($ubicacion) {
            $ubicacion->increment('usos');
        } else {
            $ubicacion = UbicacionEndpointUsuario::create([
                'user_id' => $userId,
                'endpoint_id' => $data['endpoint_id'],
                'tipo_ubicacion' => $data['tipo_ubicacion'],
                'valor_lat' => $lat,
                'valor_lon' => $lon,
                'valor_id_municipio' => $data['valor_id_municipio'] ?? null,
                'nombre_amigable' => $nombreAmigable,
                'usos' => 1
            ]);
        }

        return response()->json($ubicacion);
    }

    // Listar ubicaciones ordenadas por uso
    public function index(Request $request)
    {
        $userId = Auth::id();
        $endpointId = $request->input('endpoint_id');

        $ubicaciones = UbicacionEndpointUsuario::where('user_id', $userId)
            ->where('endpoint_id', $endpointId)
            ->orderByDesc('predeterminada')
            ->orderByDesc('usos')
            ->get();

        return response()->json($ubicaciones);
    }

    // Establecer predeterminada
    public function setPredeterminada($id)
    {
        $userId = Auth::id();
        $ubicacion = UbicacionEndpointUsuario::where('user_id', $userId)->findOrFail($id);

        // Quitar predeterminada a todas
        UbicacionEndpointUsuario::where('user_id', $userId)->update(['predeterminada' => false]);

        // Poner predeterminada a la elegida
        $ubicacion->update(['predeterminada' => true]);

        return response()->json(['message' => 'Ubicación establecida como predeterminada']);
    }
}
