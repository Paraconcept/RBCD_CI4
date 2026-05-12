<?php

namespace App\Controllers\Public;

use App\Controllers\BaseController;
use App\Models\GalleryModel;
use App\Models\GalleryPhotoModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class GalleriesController extends BaseController
{
    public function index(): string
    {
        $model     = new GalleryModel();
        $galleries = $model->getAllWithCover(true);

        foreach ($galleries as $g) {
            $g->photo_count = $model->getPhotoCount($g->id);
        }

        return view('public/pages/galeries_photos', [
            'title'     => 'Galeries photos',
            'galleries' => $galleries,
        ]);
    }

    public function show(string $slug): string
    {
        $model   = new GalleryModel();
        $gallery = $model->getBySlug($slug);

        if (!$gallery) throw PageNotFoundException::forPageNotFound();

        $photos = (new GalleryPhotoModel())->getByGallery($gallery->id);

        return view('public/pages/galerie_photos', [
            'title'   => $gallery->title,
            'gallery' => $gallery,
            'photos'  => $photos,
        ]);
    }
}
