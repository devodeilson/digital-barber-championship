<?php

namespace App\Events;

use App\Models\Content;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ContentSubmitted
{
    use Dispatchable, SerializesModels;

    public $content;

    public function __construct(Content $content)
    {
        $this->content = $content;
    }
} 