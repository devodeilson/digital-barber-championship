<?php

namespace App\Jobs;

use App\Models\Content;
use App\Services\StorageService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Log;
use FFMpeg;

class ProcessContentMedia implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $content;
    public $tries = 2;

    public function __construct(Content $content)
    {
        $this->content = $content;
    }

    public function handle(StorageService $storageService)
    {
        if ($this->content->isVideo()) {
            // Processar vídeo
            $this->processVideo();
        } else {
            // Processar imagem
            $this->processImage();
        }

        // Atualizar status do conteúdo
        $this->content->update(['processing_status' => 'completed']);
    }

    protected function processVideo()
    {
        $video = FFMpeg::fromDisk('s3')
            ->open($this->content->media_url);

        // Gerar thumbnail
        $video->getFrameFromSeconds(1)
            ->export()
            ->toDisk('s3')
            ->save("thumbnails/{$this->content->id}.jpg");

        // Converter para diferentes qualidades
        $video->exportForHLS()
            ->setSegmentLength(10)
            ->setKeyFrameInterval(48)
            ->addFormat(FFMpeg::create()->format('x264')->setKiloBitrate(500))
            ->addFormat(FFMpeg::create()->format('x264')->setKiloBitrate(1000))
            ->addFormat(FFMpeg::create()->format('x264')->setKiloBitrate(1500))
            ->save("videos/{$this->content->id}/stream.m3u8");
    }

    protected function processImage()
    {
        // Criar diferentes tamanhos da imagem
        $sizes = [
            'thumb' => [150, 150],
            'medium' => [600, 600],
            'large' => [1200, 1200]
        ];

        foreach ($sizes as $size => [$width, $height]) {
            $image = Image::make(storage_path("app/public/{$this->content->media_url}"));
            $image->fit($width, $height);
            $image->save(storage_path("app/public/images/{$size}/{$this->content->id}.jpg"));
        }
    }
} 