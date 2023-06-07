<?php

namespace App\Http\Controllers\EmbeddingControllers;

use App\Http\Requests\StoreDocumentEmbeddingRequest;
use App\Service\QueryEmbedding;
use App\Service\Tokenizer;
use Spatie\PdfToText\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DocumentEmbeddingController extends EmbeddingController
{
    public function __construct(
        protected Tokenizer $tokenizer,
        protected QueryEmbedding $query
    ) {
        parent::__construct();
    }

    public function store(StoreDocumentEmbeddingRequest $request): StreamedResponse
    {
        $validated = $request->validated();
        $file = $validated['file'];

        if (app()->environment('production')){
            $markdown = Pdf::getText($file);
        } else {
            $markdown = Pdf::getText($file, '/opt/homebrew/bin/pdftotext');
        }

        $title = strtok($markdown, "\n");

        return $this->process($title, $markdown, 'document');
    }
}
