<?php

namespace App\Http\Controllers\EmbeddingControllers;

use App\Http\Requests\StoreWebsiteEmbeddingRequest;
use App\Service\QueryEmbedding;
use App\Service\Scrape;
use App\Service\Tokenizer;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WebsiteEmbeddingController extends EmbeddingController
{
    public function __construct(
        protected Tokenizer $tokenizer,
        protected QueryEmbedding $query,
        protected Scrape $scraper
    ) {
        parent::__construct();
    }

    public function store(StoreWebsiteEmbeddingRequest $request): StreamedResponse
    {
        $validated = $request->validated();
        $url = $validated['link'];

        $markdown = $this->scraper->handle($url);
        $title = $this->scraper->title;

        return $this->process($title, $markdown, 'website');
    }
}
