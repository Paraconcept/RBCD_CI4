<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\NewsModel;
use App\Models\NewsImagesModel;
use App\Models\GalleryModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class NewsController extends BaseController
{
    private string $uploadPath;

    public function __construct()
    {
        $this->uploadPath = FCPATH . 'uploads/news/';
    }

    public function index(): string
    {
        return view('admin/news/index', [
            'news' => (new NewsModel())->orderBy('published_at', 'DESC')->orderBy('id', 'DESC')->findAll(),
        ]);
    }

    public function create(): string
    {
        return view('admin/news/form', [
            'news'          => null,
            'galleryImages' => [],
            'galleries'     => $this->getGalleriesList(),
        ]);
    }

    public function store()
    {
        if (!$this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $imageName = $this->handleUpload();

        $newsId = (new NewsModel())->insert([
            'title'        => $this->request->getPost('title'),
            'slug'         => $this->sanitizeSlug($this->request->getPost('slug')),
            'excerpt'      => $this->request->getPost('excerpt') ?: null,
            'content'      => $this->request->getPost('content'),
            'image'        => $imageName,
            'gallery_id'   => $this->request->getPost('gallery_id') ?: null,
            'published_at' => $this->request->getPost('published_at') ?: null,
            'is_published' => (int) $this->request->getPost('is_published'),
        ]);

        $this->handleGalleryUploads((int) $newsId);

        return redirect()->to(base_url('admin/news'))
                         ->with('success', 'Actualité créée avec succès.');
    }

    public function edit(int $id): string
    {
        $news = (new NewsModel())->find($id);
        if (!$news) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('admin/news/form', [
            'news'          => $news,
            'galleryImages' => (new NewsImagesModel())->getByNewsId($id),
            'galleries'     => $this->getGalleriesList(),
        ]);
    }

    public function update(int $id)
    {
        $model = new NewsModel();
        $news  = $model->find($id);
        if (!$news) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (!$this->validate($this->rules($id))) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $imageName = $news->image;

        if ($this->request->getPost('remove_image')) {
            $this->deleteFile($imageName);
            $imageName = null;
        }

        $uploaded = $this->handleUpload();
        if ($uploaded) {
            $this->deleteFile($imageName);
            $imageName = $uploaded;
        }

        $model->update($id, [
            'title'        => $this->request->getPost('title'),
            'slug'         => $this->sanitizeSlug($this->request->getPost('slug')),
            'excerpt'      => $this->request->getPost('excerpt') ?: null,
            'content'      => $this->request->getPost('content'),
            'image'        => $imageName,
            'gallery_id'   => $this->request->getPost('gallery_id') ?: null,
            'published_at' => $this->request->getPost('published_at') ?: null,
            'is_published' => (int) $this->request->getPost('is_published'),
        ]);

        $this->handleGalleryUploads($id);

        return redirect()->to(base_url('admin/news/' . $id . '/edit'))
                         ->with('success', 'Actualité mise à jour.');
    }

    public function delete(int $id)
    {
        $model = new NewsModel();
        $news  = $model->find($id);
        if ($news) {
            $this->deleteFile($news->image);
            $this->deleteAllGalleryImages($id);
            $model->delete($id);
        }

        return redirect()->to(base_url('admin/news'))
                         ->with('success', 'Actualité supprimée.');
    }

    public function toggle(int $id)
    {
        $model = new NewsModel();
        $news  = $model->find($id);
        if (!$news) {
            throw PageNotFoundException::forPageNotFound();
        }

        $model->update($id, ['is_published' => $news->is_published ? 0 : 1]);

        return redirect()->back()->with('success', 'Statut mis à jour.');
    }

    public function shareToFacebook(int $id)
    {
        $news = (new NewsModel())->find($id);
        if (!$news) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (!$news->is_published) {
            return redirect()->back()->with('error', 'Seule une actualité publiée peut être partagée.');
        }

        $pageId = env('FACEBOOK_PAGE_ID');
        $token  = env('FACEBOOK_PAGE_ACCESS_TOKEN');

        if (!$pageId || !$token) {
            return redirect()->back()->with('error', 'Partage Facebook non configuré (FACEBOOK_PAGE_ID / FACEBOOK_PAGE_ACCESS_TOKEN manquants dans .env).');
        }

        $message = $news->title . ($news->excerpt ? "\n\n" . $news->excerpt : '');

        $ch = curl_init("https://graph.facebook.com/v19.0/{$pageId}/feed");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'message'      => $message,
            'link'         => base_url('actualites/' . $news->slug),
            'access_token' => $token,
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        $data = json_decode((string) $response, true);

        if ($httpCode === 200 && isset($data['id'])) {
            return redirect()->back()->with('success', 'Actualité partagée sur la page Facebook du club.');
        }

        log_message('error', 'Échec partage Facebook (news #' . $id . ') : ' . ($curlError ?: $response));

        return redirect()->back()->with('error', 'Échec du partage Facebook : ' . ($data['error']['message'] ?? $curlError ?? 'erreur inconnue'));
    }

    public function deleteGalleryImage(int $newsId, int $imageId)
    {
        $imgModel = new NewsImagesModel();
        $image    = $imgModel->find($imageId);

        if ($image && (int) $image->news_id === $newsId) {
            $this->deleteFile($image->filename);
            $imgModel->delete($imageId);
        }

        return redirect()->to(base_url('admin/news/' . $newsId . '/edit'))
                         ->with('success', 'Photo supprimée.');
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    private function handleUpload(): ?string
    {
        $file = $this->request->getFile('image');
        if (!$file || !$file->isValid() || $file->hasMoved()) {
            return null;
        }

        $name = $file->getRandomName();
        $file->move($this->uploadPath, $name);

        return $name;
    }

    private function handleGalleryUploads(int $newsId): void
    {
        $gallery = $this->request->getFileMultiple('gallery');

        if (empty($gallery)) {
            return;
        }

        $imgModel = new NewsImagesModel();

        foreach ($gallery as $file) {
            if (!$file->isValid() || $file->hasMoved()) {
                continue;
            }
            $name = $file->getRandomName();
            $file->move($this->uploadPath, $name);

            $imgModel->insert([
                'news_id'    => $newsId,
                'filename'   => $name,
                'sort_order' => $imgModel->getNextSortOrder($newsId),
            ]);
        }
    }

    private function deleteFile(?string $name): void
    {
        if ($name && file_exists($this->uploadPath . $name)) {
            unlink($this->uploadPath . $name);
        }
    }

    private function deleteAllGalleryImages(int $newsId): void
    {
        $imgModel = new NewsImagesModel();
        $images   = $imgModel->getByNewsId($newsId);

        foreach ($images as $image) {
            $this->deleteFile($image->filename);
        }

        $imgModel->where('news_id', $newsId)->delete();
    }

    private function getGalleriesList(): array
    {
        return (new GalleryModel())
            ->orderBy('event_date', 'DESC')
            ->orderBy('title', 'ASC')
            ->findAll();
    }

    private function sanitizeSlug(string $slug): string
    {
        $slug = mb_strtolower(trim($slug));
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $slug);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);

        return trim($slug, '-');
    }

    private function rules(int $excludeId = 0): array
    {
        return [
            'title'        => 'required|max_length[255]',
            'slug'         => "required|max_length[255]|is_unique[news.slug,id,{$excludeId}]",
            'excerpt'      => 'permit_empty|max_length[500]',
            'content'      => 'required',
            'published_at' => 'permit_empty|valid_date[Y-m-d]',
            'image'        => 'permit_empty|is_image[image]|max_size[image,3072]',
        ];
    }
}
