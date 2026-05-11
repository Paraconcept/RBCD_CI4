<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\NewsModel;
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
        return view('admin/news/form', ['news' => null]);
    }

    public function store()
    {
        if (!$this->validate($this->rules())) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $imageName = $this->handleUpload();

        (new NewsModel())->insert([
            'title'        => $this->request->getPost('title'),
            'slug'         => $this->sanitizeSlug($this->request->getPost('slug')),
            'excerpt'      => $this->request->getPost('excerpt') ?: null,
            'content'      => $this->request->getPost('content'),
            'image'        => $imageName,
            'published_at' => $this->request->getPost('published_at') ?: null,
            'is_published' => (int) $this->request->getPost('is_published'),
        ]);

        return redirect()->to(base_url('admin/news'))
                         ->with('success', 'Actualité créée avec succès.');
    }

    public function edit(int $id): string
    {
        $news = (new NewsModel())->find($id);
        if (!$news) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('admin/news/form', ['news' => $news]);
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
            'published_at' => $this->request->getPost('published_at') ?: null,
            'is_published' => (int) $this->request->getPost('is_published'),
        ]);

        return redirect()->to(base_url('admin/news'))
                         ->with('success', 'Actualité mise à jour.');
    }

    public function delete(int $id)
    {
        $model = new NewsModel();
        $news  = $model->find($id);
        if ($news) {
            $this->deleteFile($news->image);
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

    private function deleteFile(?string $name): void
    {
        if ($name && file_exists($this->uploadPath . $name)) {
            unlink($this->uploadPath . $name);
        }
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
