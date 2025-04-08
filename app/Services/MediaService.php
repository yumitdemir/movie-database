<?php

namespace App\Services;

use App\Repositories\MediaRepository;
use Illuminate\Support\Facades\Storage;

class MediaService
{
    protected $mediaRepository;
    
    public function __construct(MediaRepository $mediaRepository)
    {
        $this->mediaRepository = $mediaRepository;
    }
    
    public function getMovieMedia($movieId)
    {
        return $this->mediaRepository->getMediaByMovie($movieId);
    }
    
    public function addMedia($movieId, $file, $data = [])
    {
        $path = $file->store('movie_media', 'public');
        
        return $this->mediaRepository->create([
            'movie_id' => $movieId,
            'file_path' => $path,
            'file_type' => $file->getMimeType(),
            'title' => $data['title'] ?? null,
            'description' => $data['description'] ?? null,
        ]);
    }
    
    public function updateMedia($id, $data)
    {
        return $this->mediaRepository->update($id, [
            'title' => $data['title'] ?? null,
            'description' => $data['description'] ?? null,
        ]);
    }
    
    public function deleteMedia($id)
    {
        $media = $this->mediaRepository->getById($id);
        Storage::delete('public/' . $media->file_path);
        
        return $this->mediaRepository->delete($id);
    }
} 