<?php

namespace Pho\Crm\Controller;

use Pho\Crm\Model\User;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;

class UserController
{
    public function customersGraphjs()
    {
        return $this->customers('graphjs');
    }

    public function customersGroups()
    {
        return $this->customers('groups');
    }

    public function customers($type)
    {
        return new HtmlResponse(view('customers.php', [ 'customerType' => $type ]));
    }

    public function customersAjax(ServerRequestInterface $request, \PDO $pdo)
    {
        $defaultPage = 1;
        $defaultLimit = 20;

        $queryParams = $request->getQueryParams();

        $page = $queryParams['page'] ?? $defaultPage;
        $limit = $queryParams['limit'] ?? $defaultLimit;
        $offset = ($page - 1) * $limit;

        $search = $queryParams['search'] ?? '';
        $sort = $queryParams['sort'] ?? [];
        $type = $queryParams['type'] ?? null;

        $whereClause = '';
        $whereBindings = [];

        $orderBy = [];

        $whereUsers = '';
        $whereInstances = '';

        // preparing where clause which is common for queries
        // to get total and users
        if ($search) {
            $whereUsers .= <<<SQL

`first_name` LIKE ?
OR
`last_name` LIKE ?
SQL;
            $whereInstances .= <<<SQL

EXISTS (
  SELECT * FROM `sites`
  WHERE
  `instances`.`id` = `sites`.`instance_id`
  AND
  `url` LIKE ?
)
SQL;
            $whereBindings = [
                "%$search%",
                "%$search%",
                "%$search%",
            ];
        }

        switch ($type) {
            case 'graphjs':
                $whereInstances .= ($whereInstances ? "\nAND" : "") . <<<SQL

(`instances`.`group_name` IS NULL OR `instances`.`group_name` = "")
SQL;
                break;
            case 'groups':
                $whereInstances .= ($whereInstances ? "\nAND" : "") . <<<SQL

(`instances`.`group_name` IS NOT NULL AND `instances`.`group_name` != "")
SQL;
                break;
        }

        if ($whereInstances) {
            $whereUsers .= ($whereUsers ? "\nOR" : "") . <<<SQL

EXISTS (
  SELECT * FROM `instances`
  WHERE
  $whereInstances
  AND
  `users`.`id` = `instances`.`user_id`
)
SQL;
        }

        if ($whereUsers) {
            $whereClause .= <<<SQL

WHERE
$whereUsers
SQL;
        }

        // getting total
        $countQuery = "SELECT count(*) AS aggregate FROM `users` $whereClause";
        $stmt = $pdo->prepare($countQuery);
        if (! $stmt->execute($whereBindings)) {
            throw new \Exception(print_r($pdo->errorInfo(), true));
        }
        $row = $stmt->fetch(\PDO::FETCH_OBJ);
        $total = (int) $row->aggregate;

        $lastPage = ceil($total / $limit) ?: 1;

        $selectQuery = <<<SQL
SELECT `users`.*,

(
  SELECT count(*) FROM `access-tokens`
  WHERE
  `users`.`id` = `access-tokens`.`user_id`
  AND
  `created_at` > (NOW() - INTERVAL 30 DAY)
)
AS `access_tokens_count`,

(
  SELECT count(*) FROM `service-conversations`
  INNER JOIN `service-tickets`
  ON `service-tickets`.`uuid` = `service-conversations`.`uuid`
  WHERE `users`.`id` = `service-tickets`.`by`
)
AS `service_conversations_count`,

(
  SELECT count(*) FROM `analytics`
  INNER JOIN `instances`
  ON `instances`.`uuid` = `analytics`.`id`
  WHERE `users`.`id` = `instances`.`user_id`
  AND `time` > (NOW() - INTERVAL 1 WEEK)
)
AS `analytics_count`

FROM `users`

$whereClause
SQL;

        if (isset($sort['email_count']) && in_array($sort['email_count'], ['asc', 'desc'])) {
            $orderBy[] = [ 'service_conversations_count', $sort['email_count'] ];
        }
        if (isset($sort['login_count']) && in_array($sort['login_count'], ['asc', 'desc'])) {
            $orderBy[] = [ 'access_tokens_count', $sort['login_count'] ];
        }
        if (isset($sort['analytics_count']) && in_array($sort['analytics_count'], ['asc', 'desc'])) {
            $orderBy[] = [ 'analytics_count', $sort['analytics_count'] ];
        }

        if ($orderBy) {
            $selectQuery = join("\n", [
                $selectQuery,
                "ORDER BY",
                join(",\n", array_map(function ($orderByItem) {
                    list($orderByField, $orderByType) = $orderByItem;
                    return "`$orderByField` $orderByType";
                }, $orderBy)),
            ]);
        }

        $selectQuery .= "\nLIMIT $limit OFFSET $offset";

        // getting users
        $stmt = $pdo->prepare($selectQuery);
        if (! $stmt->execute($whereBindings)) {
            throw new \Exception(print_r($pdo->errorInfo(), true));
        }
        $users = $stmt->fetchAll(\PDO::FETCH_OBJ);

        // fetching related data
        $instances = [];
        $sites = [];
        $userIds = array_map(function ($user) {
            return $user->id;
        }, $users);
        if ($userIds) {
            $stmt = $pdo->prepare("SELECT * FROM instances WHERE `instances`.`user_id` IN (" . join(array_fill(0, count($userIds), '?'), ', ') . ")");
            if (! $stmt->execute($userIds)) {
                throw new \Exception(print_r($pdo->errorInfo(), true));
            }
            $instances = $stmt->fetchAll(\PDO::FETCH_OBJ);

            $instanceIds = array_map(function ($instance) {
                return $instance->id;
            }, $instances);

            if ($instanceIds) {
                $stmt = $pdo->prepare("SELECT * FROM `sites` WHERE `sites`.`instance_id` IN (" . join(array_fill(0, count($instanceIds), '?'), ', ') . ")");
                if (! $stmt->execute($instanceIds)) {
                    throw new \Exception(print_r($pdo->errorInfo(), true));
                }
                $sites = $stmt->fetchAll(\PDO::FETCH_OBJ);
            }
        }

        // prepare structure for response
        array_walk($users, function ($user) use (&$instances, &$sites) {
            $user->instances = array_filter($instances, function ($instance) use (&$user) {
                return $instance->user_id === $user->id;
            });
            array_walk($user->instances, function ($instance) use (&$sites) {
                $instance->site = current(array_filter($sites, function ($site) use (&$instance) {
                    return $site->instance_id === $instance->id;
                }));
            });

            // remove any field that is to be hidden
            unset($user->password);
        });

        return new JsonResponse([
            'data' => $users,
            'current_page' => $page,
            'last_page' => $lastPage,
            'total' => $total,
        ]);
    }

    public function customerDetail($user_id)
    {
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

        return new HtmlResponse(view('customer_detail.php', [
            'user' => $user,
        ]));
    }
}
