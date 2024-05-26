<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

class NewsService
{
    protected $client;
    protected $apiKey;

    public function __construct()
    {
        $this->apiKey = env('NEWS_API_KEY');
        $this->client = new Client();
    }

    public function getArticles($filters = [])
    {
        if (empty($filters['q']) && empty($filters['sources']) && empty($filters['domains'])) {
            $filters['q'] = 'latest'; // Default query term if none is provided
        }

        try {
            $response = $this->client->get('https://newsapi.org/v2/everything', [
                
                'query' => array_merge($filters, ['apiKey' => $this->apiKey]),
                'verify' => false, // Disable SSL verification (if needed)
            ]);
            return json_decode($response->getBody()->getContents(), true);
        } catch (RequestException $e) {
            // Handle the error
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

   

}
