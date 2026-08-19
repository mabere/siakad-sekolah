<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use JsonException;
use RuntimeException;

final class GeminiClient
{
    /**
     * @param  array<string, mixed>  $schema
     * @return array<string, mixed>
     */
    public function generateJson(string $systemInstruction, string $prompt, array $schema): array
    {
        $configuration = config('services.gemini', []);
        $apiKey = $configuration['api_key'] ?? null;
        $maxInputChars = max(1000, (int) ($configuration['max_input_chars'] ?? 16000));

        if (($configuration['enabled'] ?? false) !== true || ! is_string($apiKey) || trim($apiKey) === '') {
            throw new RuntimeException('Provider Gemini belum diaktifkan oleh administrator.');
        }

        if (mb_strlen($prompt) > $maxInputChars) {
            throw new InvalidArgumentException(
                'Konteks terlalu panjang. Ringkas beberapa isian sebelum mencoba lagi.'
            );
        }

        $configuredModel = trim((string) ($configuration['model'] ?? 'gemini-3.6-flash'));
        if ($configuredModel === '') {
            $configuredModel = 'gemini-3.6-flash';
        }

        // Candidate model sequence for resilient fallback if Google experiences temporary 503/429/404
        $candidateModels = array_values(array_unique(array_filter([
            $configuredModel,
            'gemini-3.6-flash',
            'gemini-3.5-flash-lite',
            'gemini-2.5-flash-lite',
            'gemini-3.7-flash',
        ])));

        $baseUrl = rtrim((string) ($configuration['base_url'] ?? 'https://generativelanguage.googleapis.com/v1beta'), '/');
        $timeout = max(5, (int) ($configuration['timeout'] ?? 60));
        $lastException = null;

        foreach ($candidateModels as $model) {
            $endpoint = $baseUrl.'/models/'.rawurlencode($model).':generateContent';

            try {
                $response = Http::acceptJson()
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'x-goog-api-key' => $apiKey,
                    ])
                    ->timeout($timeout)
                    ->retry(2, 500, throw: false)
                    ->post($endpoint, [
                        'systemInstruction' => [
                            'parts' => [
                                ['text' => $systemInstruction],
                            ],
                        ],
                        'contents' => [
                            [
                                'role' => 'user',
                                'parts' => [
                                    ['text' => $prompt],
                                ],
                            ],
                        ],
                        'generationConfig' => [
                            'temperature' => 0.2,
                            'maxOutputTokens' => 8192,
                            'responseMimeType' => 'application/json',
                            'responseJsonSchema' => $schema,
                        ],
                    ]);

                if ($response->failed()) {
                    Log::warning('Gemini generation request failed on model: '.$model, [
                        'status' => $response->status(),
                        'body' => substr($response->body(), 0, 500),
                        'model' => $model,
                    ]);

                    // If transient 503, 429, or 404, continue to next candidate model
                    if (in_array($response->status(), [404, 429, 500, 503], true)) {
                        continue;
                    }

                    throw new RuntimeException(
                        'Gemini tidak dapat memproses draf saat ini (Status: '.$response->status().').'
                    );
                }

                $parts = data_get($response->json(), 'candidates.0.content.parts', []);
                $text = collect(is_array($parts) ? $parts : [])
                    ->map(fn (mixed $part): mixed => is_array($part) ? ($part['text'] ?? null) : null)
                    ->filter(fn (mixed $part): bool => is_string($part) && trim($part) !== '')
                    ->implode('');

                if (trim($text) === '') {
                    Log::warning('Gemini response did not contain text for model: '.$model);
                    continue;
                }

                $jsonText = trim($text);
                if (str_starts_with($jsonText, '```')) {
                    $jsonText = preg_replace('/\\A```(?:json)?\\s*/i', '', $jsonText) ?? $jsonText;
                    $jsonText = preg_replace('/\\s*```\\z/', '', $jsonText) ?? $jsonText;
                }

                $decoded = json_decode($jsonText, true, 512, JSON_THROW_ON_ERROR);
                if (is_array($decoded)) {
                    return $decoded;
                }
            } catch (JsonException $e) {
                Log::warning('Gemini response was not valid JSON for model: '.$model, [
                    'snippet' => substr($jsonText ?? '', 0, 300),
                    'error' => $e->getMessage(),
                ]);
                $lastException = $e;
                continue;
            } catch (\Throwable $e) {
                Log::warning('Gemini client error for model: '.$model, [
                    'error' => $e->getMessage(),
                ]);
                $lastException = $e;
                continue;
            }
        }

        throw new RuntimeException(
            'Gemini tidak dapat memproses draf saat ini. Server AI sedang sibuk atau konfigurasi model perlu disesuaikan. Silakan coba kembali dalam beberapa saat.'
        );
    }
}
