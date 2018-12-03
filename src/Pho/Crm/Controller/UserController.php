<?php

namespace Pho\Crm\Controller;

use Pho\Crm\Model\User;
use Pho\Crm\Traits\AuthTrait;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\RedirectResponse;

class UserController
{
    use AuthTrait;

    public function leads()
    {
        $isLoggedIn = $this->isLoggedIn();
        if (! $isLoggedIn) {
            return new RedirectResponse(url('login'));
        }

        return new HtmlResponse(view('leads.php'));
    }

    public function leadsAjax(ServerRequestInterface $request)
    {
        $isLoggedIn = $this->isLoggedIn();
        if (! $isLoggedIn) {
            return new RedirectResponse(url('login'));
        }

        $defaultPage = 1;
        $defaultLimit = 20;

        $queryParams = $request->getQueryParams();

        $page = $queryParams['page'] ?? $defaultPage;
        $limit = $queryParams['limit'] ?? $defaultLimit;
        $offset = ($page - 1) * $limit;

        $search = $queryParams['search'] ?? '';

        $users = User::offset($offset)->limit($limit);
        $users = $users->with('instances.site', 'serviceTickets')->get();

        return new JsonResponse([
            'data' => $users,
        ]);
    }
}
