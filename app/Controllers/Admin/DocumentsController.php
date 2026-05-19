<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ClubDocumentModel;

class DocumentsController extends BaseController
{
    private ClubDocumentModel $model;
    private string $uploadPath;
    private string $uploadDir = 'uploads/PDF/Documents/';

    public function __construct()
    {
        $this->model      = new ClubDocumentModel();
        $this->uploadPath = FCPATH . $this->uploadDir;
    }

    public function index(): string
    {
        return view('admin/documents/index', [
            'title'       => 'Documents PDF',
            'breadcrumbs' => [['title' => 'Documents PDF']],
            'documents'   => $this->model->orderBy('title', 'ASC')->findAll(),
        ]);
    }

    public function create(): string
    {
        return view('admin/documents/form', [
            'title'         => 'Nouveau document',
            'breadcrumbs'   => [
                ['title' => 'Documents PDF', 'url' => base_url('admin/documents')],
                ['title' => 'Nouveau'],
            ],
            'document'      => null,
            'existingFiles' => $this->listExistingPdfs(),
        ]);
    }

    public function store()
    {
        if (!$this->validate([
            'title' => 'required|max_length[200]',
            'slug'  => 'required|max_length[100]',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $slug = $this->request->getPost('slug');

        if ($this->model->findBySlug($slug)) {
            return redirect()->back()->withInput()
                ->with('errors', ['slug' => 'Ce slug est déjà utilisé par un autre document.']);
        }

        $this->model->insert([
            'slug'        => $slug,
            'title'       => $this->request->getPost('title'),
            'filename'    => $this->resolveFile(null),
            'uploaded_at' => $this->request->getPost('uploaded_at') ?: date('Y-m-d'),
        ]);

        return redirect()->to(base_url('admin/documents'))->with('success', 'Document ajouté.');
    }

    public function edit(int $id): string
    {
        $doc = $this->model->find($id);
        if (!$doc) {
            return redirect()->to(base_url('admin/documents'))->with('error', 'Document introuvable.');
        }

        return view('admin/documents/form', [
            'title'         => 'Modifier le document',
            'breadcrumbs'   => [
                ['title' => 'Documents PDF', 'url' => base_url('admin/documents')],
                ['title' => 'Modifier'],
            ],
            'document'      => $doc,
            'existingFiles' => $this->listExistingPdfs(),
        ]);
    }

    public function update(int $id)
    {
        $doc = $this->model->find($id);
        if (!$doc) {
            return redirect()->to(base_url('admin/documents'))->with('error', 'Document introuvable.');
        }

        if (!$this->validate([
            'title' => 'required|max_length[200]',
            'slug'  => 'required|max_length[100]',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $slug     = $this->request->getPost('slug');
        $existing = $this->model->findBySlug($slug);
        if ($existing && (int) $existing->id !== $id) {
            return redirect()->back()->withInput()
                ->with('errors', ['slug' => 'Ce slug est déjà utilisé par un autre document.']);
        }

        $this->model->update($id, [
            'slug'        => $slug,
            'title'       => $this->request->getPost('title'),
            'filename'    => $this->resolveFile($doc->filename),
            'uploaded_at' => $this->request->getPost('uploaded_at') ?: $doc->uploaded_at,
        ]);

        return redirect()->to(base_url('admin/documents'))->with('success', 'Document mis à jour.');
    }

    public function delete(int $id)
    {
        $doc = $this->model->find($id);
        if (!$doc) {
            return redirect()->to(base_url('admin/documents'))->with('error', 'Document introuvable.');
        }

        $this->model->delete($id);

        return redirect()->to(base_url('admin/documents'))->with('success', 'Document supprimé.');
    }

    // ----------------------------------------------------------------

    private function resolveFile(?string $current): ?string
    {
        $file = $this->request->getFile('pdf_file');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            if (!is_dir($this->uploadPath)) {
                mkdir($this->uploadPath, 0755, true);
            }
            $name = $file->getClientName();
            $file->move($this->uploadPath, $name, true);
            return $name;
        }

        $selected = $this->request->getPost('existing_file');
        if ($selected && $selected !== '') {
            return $selected;
        }

        return $current;
    }

    private function listExistingPdfs(): array
    {
        if (!is_dir($this->uploadPath)) {
            return [];
        }
        $files = glob($this->uploadPath . '*.pdf');
        if (!$files) {
            return [];
        }
        $names = array_map('basename', $files);
        sort($names);
        return $names;
    }
}
