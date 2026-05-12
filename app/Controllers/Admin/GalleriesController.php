<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\GalleryModel;
use App\Models\GalleryPhotoModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class GalleriesController extends BaseController
{
    private GalleryModel      $model;
    private GalleryPhotoModel $photoModel;

    public function __construct()
    {
        $this->model      = new GalleryModel();
        $this->photoModel = new GalleryPhotoModel();
    }

    public function index(): string
    {
        $galleries = $this->model->getAllWithCover(false);

        foreach ($galleries as $g) {
            $g->photo_count = $this->model->getPhotoCount($g->id);
        }

        return view('admin/galleries/index', [
            'title'     => 'Galeries photos',
            'galleries' => $galleries,
        ]);
    }

    public function create(): string
    {
        return view('admin/galleries/form', [
            'title'   => 'Nouvelle galerie',
            'gallery' => null,
        ]);
    }

    public function store()
    {
        $data = $this->buildData();
        if ($data === null) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $id = $this->model->insert($data);

        return redirect()->to(base_url('admin/galleries/' . $id . '/photos'))
                         ->with('success', 'Galerie créée. Ajoutez vos photos ci-dessous.');
    }

    public function edit(int $id): string
    {
        $gallery = $this->model->find($id);
        if (!$gallery) throw PageNotFoundException::forPageNotFound();

        return view('admin/galleries/form', [
            'title'   => 'Modifier la galerie',
            'gallery' => $gallery,
        ]);
    }

    public function update(int $id)
    {
        $gallery = $this->model->find($id);
        if (!$gallery) throw PageNotFoundException::forPageNotFound();

        $data = $this->buildData($gallery->slug);
        if ($data === null) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $this->model->update($id, $data);

        return redirect()->to(base_url('admin/galleries/' . $id . '/photos'))
                         ->with('success', 'Galerie mise à jour.');
    }

    public function delete(int $id)
    {
        $gallery = $this->model->find($id);
        if ($gallery) {
            $photos = $this->photoModel->getByGallery($id);
            $dir    = FCPATH . 'uploads/galleries/' . $id . '/';
            foreach ($photos as $p) {
                $f = $dir . $p->filename;
                if (file_exists($f)) unlink($f);
            }
            if (is_dir($dir)) rmdir($dir);
            $this->model->delete($id);
        }

        return redirect()->to(base_url('admin/galleries'))
                         ->with('success', 'Galerie supprimée.');
    }

    public function show(int $id): string
    {
        $gallery = $this->model->getWithCover($id);
        if (!$gallery) throw PageNotFoundException::forPageNotFound();

        $photos = $this->photoModel->getByGallery($id);

        return view('admin/galleries/show', [
            'title'   => 'Photos — ' . $gallery->title,
            'gallery' => $gallery,
            'photos'  => $photos,
        ]);
    }

    public function uploadPhotos(int $id)
    {
        $gallery = $this->model->find($id);
        if (!$gallery) throw PageNotFoundException::forPageNotFound();

        // If PHP dropped the entire body (post_max_size exceeded), $_FILES is empty
        if (empty($_FILES)) {
            $limit = ini_get('post_max_size');
            return redirect()->back()->with('error',
                "PHP n'a reçu aucun fichier. La taille totale dépasse probablement post_max_size ({$limit}). "
                . "Réduisez le nombre de photos ou augmentez post_max_size dans php.ini.");
        }

        $files = $this->request->getFileMultiple('photos');
        if (!$files) {
            return redirect()->back()->with('error', 'Aucun fichier sélectionné.');
        }

        $dir = FCPATH . 'uploads/galleries/' . $id . '/';
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $startOrder  = $this->photoModel->getNextSortOrder($id);
        $firstNew    = null;
        $i           = 0;

        foreach ($files as $file) {
            if (!$file->isValid() || $file->hasMoved()) continue;

            $ext     = strtolower($file->getClientExtension());
            $idx     = str_pad($startOrder + $i, 3, '0', STR_PAD_LEFT);
            $newName = $gallery->slug . '_' . $idx . '.' . $ext;

            $file->move($dir, $newName);

            $photoId = $this->photoModel->insert([
                'gallery_id' => $id,
                'filename'   => $newName,
                'sort_order' => $startOrder + $i,
            ]);

            if ($i === 0) $firstNew = $photoId;
            $i++;
        }

        // Auto-set cover if gallery has none
        if (!$gallery->cover_photo_id && $firstNew) {
            $this->model->update($id, ['cover_photo_id' => $firstNew]);
        }

        return redirect()->to(base_url('admin/galleries/' . $id . '/photos'))
                         ->with('success', $i . ' photo(s) ajoutée(s).');
    }

    public function deletePhoto(int $id, int $photoId)
    {
        $gallery = $this->model->find($id);
        $photo   = $this->photoModel->find($photoId);

        if (!$photo || (int)$photo->gallery_id !== $id) {
            throw PageNotFoundException::forPageNotFound();
        }

        $path = FCPATH . 'uploads/galleries/' . $id . '/' . $photo->filename;
        if (file_exists($path)) unlink($path);

        if ((int)$gallery->cover_photo_id === $photoId) {
            $this->model->update($id, ['cover_photo_id' => null]);
        }

        $this->photoModel->delete($photoId);

        return redirect()->to(base_url('admin/galleries/' . $id . '/photos'))
                         ->with('success', 'Photo supprimée.');
    }

    public function setCover(int $id, int $photoId)
    {
        $photo = $this->photoModel->find($photoId);
        if (!$photo || (int)$photo->gallery_id !== $id) {
            throw PageNotFoundException::forPageNotFound();
        }

        $this->model->update($id, ['cover_photo_id' => $photoId]);

        return redirect()->to(base_url('admin/galleries/' . $id . '/photos'))
                         ->with('success', 'Photo de couverture définie.');
    }

    // ── Helpers ───────────────────────────────────────────────────────────

    private function buildData(?string $currentSlug = null): ?array
    {
        if (!$this->validate(['title' => 'required|max_length[255]'])) {
            return null;
        }

        $title = $this->request->getPost('title');
        $slug  = trim($this->request->getPost('slug') ?: '');

        if ($slug === '') {
            $slug = $this->makeSlug($title);
        } else {
            $slug = $this->makeSlug($slug);
        }

        // Ensure uniqueness (skip current slug on edit)
        $base = $slug;
        $n    = 2;
        while (true) {
            $q = $this->model->where('slug', $slug);
            if ($currentSlug !== null) {
                $q = $q->where('slug !=', $currentSlug);
            }
            if (!$q->first()) break;
            $slug = $base . '-' . $n++;
        }

        return [
            'title'        => $title,
            'slug'         => $slug,
            'description'  => $this->request->getPost('description') ?: null,
            'event_date'   => $this->request->getPost('event_date') ?: null,
            'season'       => $this->request->getPost('season') ?: null,
            'is_published' => $this->request->getPost('is_published') ? 1 : 0,
        ];
    }

    private function makeSlug(string $str): string
    {
        $str = mb_strtolower($str);
        $str = str_replace(
            ['à','â','ä','é','è','ê','ë','î','ï','ô','ö','ù','û','ü','ç','œ','æ'],
            ['a','a','a','e','e','e','e','i','i','o','o','u','u','u','c','oe','ae'],
            $str
        );
        $str = preg_replace('/[^a-z0-9\s-]/', '', $str);
        $str = preg_replace('/[\s-]+/', '-', trim($str));
        return trim($str, '-');
    }
}
