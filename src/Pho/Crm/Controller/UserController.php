<?php

namespace Pho\Crm\Controller;

use Pho\Crm\Model\User;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;

class UserController
{
    public function leads()
    {
        return new HtmlResponse(view('leads.php'));
    }

    public function leadsAjax(ServerRequestInterface $request)
    {

        $defaultPage = 1;
        $defaultLimit = 20;

        $queryParams = $request->getQueryParams();

        $page = $queryParams['page'] ?? $defaultPage;
        $limit = $queryParams['limit'] ?? $defaultLimit;
        $offset = ($page - 1) * $limit;

        $search = $queryParams['search'] ?? '';

        $users = User::query();

        if ($search) {
            $users = $users->where('first_name', 'like', "%$search%");
            $users = $users->orWhere('last_name', 'like', "%$search%");

            $users = $users->orWhereHas('instances', function ($query) use ($search) {
                $query->whereHas('site', function ($query) use ($search) {
                    $query->where('url', 'like', "%$search%");
                });
            });
        }

        $total = (clone $users)->count();
        $lastPage = ceil($total / $limit) ?: 1;

        $users = $users->offset($offset)->limit($limit);
        $users = $users->with([
            'instances.site',
        ])
        ->withCount([
            'accessTokens' => function ($query) {
                $query->whereRaw('created_at > (NOW() - INTERVAL 30 DAY)');
            },
            'serviceConversations',
        ])->get();

        return new JsonResponse([
            'data' => $users,
            'current_page' => $page,
            'last_page' => $lastPage,
            'total' => $total,
        ]);
    }

    public function leadDetail($user_id)
    {
        $isLoggedIn = $this->isLoggedIn();
        if (! $isLoggedIn) {
            return new RedirectResponse(url('login'));
        }

        $user = User::where('id', $user_id)
        ->with([
            'instances.site',
        ])
        ->withCount([
            'accessTokens' => function ($query) {
                $query->whereRaw('created_at > (NOW() - INTERVAL 30 DAY)');
            },
            'serviceConversations',
        ])->first();

        return new HtmlResponse(view('lead_detail.php', [
            'user' => $user,
        ]));
    }
}
