<?php

namespace App\Http\Controllers\EmbeddingControllers;

use App\Helpers\ServerEvent;
use App\Models\Chat;
use App\Models\EmbedCollection;
use App\Models\Embedding;
use App\Service\QueryEmbedding;
use App\Service\Tokenizer;
use Exception;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EmbeddingController
{
    public function __construct()
    {
    }

    public function process(string $title, string $markdown, string $type): StreamedResponse
    {
        return response()->stream(function () use ($title, $markdown, $type) {
            try {
                ServerEvent::send("Start crawling: {$title}");
                $tokens = $this->tokenizer->tokenize($markdown, 256);

                $count = count($tokens);
                $total = 0;
                $collection = EmbedCollection::create([
                    'name' => $title,
                    'meta_data' => json_encode([
                        'title' => $title,
                        'type' => $type,
                    ]),
                ]);

                foreach ($tokens as $token) {
                    $total++;
                    $text = implode("\n", $token);
                    $vectors = $this->query->getQueryEmbedding($text);
                    Embedding::create([
                        'embed_collection_id' => $collection->id,
                        'text' => $text,
                        'embedding' => json_encode($vectors)
                    ]);
                    ServerEvent::send("Indexing: {$title}, {$total} of {$count} elements.");

                    if (connection_aborted()) {
                        break;
                    }
                }
                sleep(1);
                $chat = Chat::create(['embed_collection_id' => $collection->id]);
                ServerEvent::send(route("chat.show", $chat->id));
            } catch (Exception $e) {
                Log::error($e);
                ServerEvent::send("Embedding failed");
            }
        }, 200, [
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
            'X-Accel-Buffering' => 'no',
            'Content-Type' => 'text/event-stream',
        ]);
    }
}
