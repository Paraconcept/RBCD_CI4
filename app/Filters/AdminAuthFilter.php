<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null): mixed
    {
        if (!session()->get('admin_logged_in')) {
            return redirect()->to(base_url('admin/login'))->with('error', 'Veuillez vous connecter.');
        }

        $path = $request->getUri()->getPath();
        if (session()->get('must_change_password')
            && !str_contains($path, 'admin/change-password')
            && !str_contains($path, 'admin/logout'))
        {
            return redirect()->to(base_url('admin/change-password'));
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): mixed
    {
        return null;
    }
}
