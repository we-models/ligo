<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class GoogleMapsController extends Controller
{

    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->client = new Client();
        $this->apiKey = getConfigValue('GOOGLE_MAPS_KEY' );
    }

    public function getPredictions(Request $request)
    {
        $input = $request->input('input');
        $lat = $request->input('lat');
        $lon = $request->input('lon');
        $radius = $request->input('radius');
        $country = $request->input('country', 'EC');

        $baseUrl = 'https://maps.googleapis.com/maps/api/place/autocomplete/json';
        $params = [
            'input' => $input,
            'location' => "{$lat},{$lon}",
            'radius' => $radius,
            'key' => $this->apiKey,
            'components' => "country:{$country}",
        ];

        try {
            $response = $this->client->get($baseUrl, ['query' => $params]);
            if ($response->getStatusCode() === 200) {
                $body = json_decode($response->getBody()->getContents(), true);
                return response()->json(array_map(function ($prediction) {
                    return [
                        'description' => $prediction['description'],
                        'place_id' => $prediction['place_id'],
                    ];
                }, $body['predictions']));
            } else {
                return response()->json([], 204);
            }
        } catch (RequestException $e) {
            return response()->json(['error' => 'Error fetching predictions.'], 500);
        }
    }

    public function getPlaceDetailsByPlaceId(Request $request)
    {
        $baseUrl = 'https://maps.googleapis.com/maps/api/place/details/json';
        $placeId = $request->input('place_id');
        $params = [
            'place_id' => $placeId,
            'key' => $this->apiKey,
            'fields' => 'name,formatted_address,geometry,place_id',
        ];


        try {
            $response = $this->client->get($baseUrl, ['query' => $params]);
            if ($response->getStatusCode() === 200) {
                $body = json_decode($response->getBody()->getContents(), true);
                return response()->json($body);
            } else {
                return response()->json([], 204);
            }
        } catch (RequestException $e) {
            return response()->json(['error' => 'Error fetching place details.'], 500);
        }
    }


}
