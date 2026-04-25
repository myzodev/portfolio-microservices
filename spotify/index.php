<?php

require '../vendor/autoload.php';

use SpotifyWebAPI\Session;
use SpotifyWebAPI\SpotifyWebAPI;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad();

$requestOrigin = $_SERVER['HTTP_ORIGIN'] ?? '';
$requestOrigin = rtrim($requestOrigin, '/');

$allowedOrigins = $_ENV['ALLOWED_ORIGINS'] ?? '';
$allowedOriginsArray = explode(',', $allowedOrigins);

if (in_array($requestOrigin, $allowedOriginsArray)) {
    header("Access-Control-Allow-Origin: $requestOrigin");
    header("Access-Control-Allow-Methods: GET, OPTIONS"); 
    header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
    header("Access-Control-Allow-Credentials: true");
} else {
    http_response_code(403);
    echo json_encode(['error' => 'Origin not allowed: ' . $requestOrigin]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); // No content
    exit;
}

header('Content-Type: application/json');

$clientId = $_ENV['SPOTIFY_CLIENT_ID'];
$clientSecret = $_ENV['SPOTIFY_CLIENT_SECRET'];
$refreshToken = $_ENV['SPOTIFY_REFRESH_TOKEN'];

$session = new Session($clientId, $clientSecret);

$session->refreshAccessToken($refreshToken);
$accessToken = $session->getAccessToken();

$api = new SpotifyWebAPI();
$api->setAccessToken($accessToken);

$currentTrack = $api->getMyCurrentTrack();

$responseData = [
    'isPlaying' => false,
    'track' => "",
    'artist' => "",
    'album' => "",
    'url' => ""
];

if ($currentTrack && $currentTrack->item) {
    $artists = array_map(function($artist) {
        return $artist->name;
    }, $currentTrack->item->artists);

    $responseData = [
        'isPlaying' => $currentTrack->is_playing, 
        'track'      => $currentTrack->item->name,
        'artist'     => implode(', ', $artists),
        'album'      => $currentTrack->item->album->name,
        'url'        => $currentTrack->item->external_urls->spotify
    ];
}

echo json_encode($responseData);