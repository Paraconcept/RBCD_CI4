<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\JournalIssueModel;

class JournalController extends BaseController
{
    private JournalIssueModel $model;
    private string $uploadPath;
    private string $uploadDir = 'uploads/PDF/PartieLibre/';

    public function __construct()
    {
        $this->model      = new JournalIssueModel();
        $this->uploadPath = FCPATH . $this->uploadDir;
    }

    public function index(): string
    {
        $year  = (int) ($this->request->getGet('year') ?? date('Y'));
        $years = $this->model->getAvailableYears();

        return view('admin/journal/index', [
            'title'       => 'Journal "Partie Libre"',
            'breadcrumbs' => [['title' => 'Journal "Partie Libre"']],
            'issues'      => $this->model->getByYear($year),
            'year'        => $year,
            'years'       => $years,
        ]);
    }

    public function create(): string
    {
        return view('admin/journal/form', [
            'title'       => 'Nouveau numéro',
            'breadcrumbs' => [
                ['title' => 'Journal "Partie Libre"', 'url' => base_url('admin/journal')],
                ['title' => 'Nouveau numéro'],
            ],
            'issue'         => null,
            'existingFiles' => $this->listExistingPdfs(),
        ]);
    }

    public function store()
    {
        if (!$this->validate([
            'title' => 'required|max_length[200]',
        ])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $filePath = $this->resolveFile(null);

        $this->model->insert([
            'title'          => $this->request->getPost('title'),
            'published_date' => $this->request->getPost('published_date') ?: null,
            'description'    => $this->request->getPost('description') ?: null,
            'file_path'      => $filePath,
            'is_published'   => (int) (bool) $this->request->getPost('is_published'),
        ]);

        return redirect()->to(base_url('admin/journal'))->with('success', 'Numéro ajouté.');
    }

    public function edit(int $id): string
    {
        $issue = $this->model->find($id);
        if (!$issue) {
            return redirect()->to(base_url('admin/journal'))->with('error', 'Numéro introuvable.');
        }

        return view('admin/journal/form', [
            'title'       => 'Modifier le numéro',
            'breadcrumbs' => [
                ['title' => 'Journal "Partie Libre"', 'url' => base_url('admin/journal')],
                ['title' => 'Modifier'],
            ],
            'issue'         => $issue,
            'existingFiles' => $this->listExistingPdfs(),
        ]);
    }

    public function update(int $id)
    {
        $issue = $this->model->find($id);
        if (!$issue) {
            return redirect()->to(base_url('admin/journal'))->with('error', 'Numéro introuvable.');
        }

        if (!$this->validate(['title' => 'required|max_length[200]'])) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $filePath = $this->resolveFile($issue->file_path);

        $this->model->update($id, [
            'title'          => $this->request->getPost('title'),
            'published_date' => $this->request->getPost('published_date') ?: null,
            'description'    => $this->request->getPost('description') ?: null,
            'file_path'      => $filePath,
            'is_published'   => (int) (bool) $this->request->getPost('is_published'),
        ]);

        return redirect()->to(base_url('admin/journal'))->with('success', 'Numéro mis à jour.');
    }

    public function delete(int $id)
    {
        $issue = $this->model->find($id);
        if (!$issue) {
            return redirect()->to(base_url('admin/journal'))->with('error', 'Numéro introuvable.');
        }

        $this->model->delete($id);

        return redirect()->to(base_url('admin/journal'))->with('success', 'Numéro supprimé.');
    }

    private function resolveFile(?string $current): ?string
    {
        // 1. Nouvel upload prioritaire
        $file = $this->request->getFile('pdf_file');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $name = $file->getClientName();
            $file->move($this->uploadPath, $name, true);
            return $name;
        }

        // 2. Fichier existant sélectionné dans le dropdown
        $selected = $this->request->getPost('existing_file');
        if ($selected && $selected !== '') {
            return $selected;
        }

        // 3. Conserver l'actuel
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
