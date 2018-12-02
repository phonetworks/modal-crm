<?php

namespace Pho\Crm\Controller;

use Illuminate\Database\Capsule\Manager;
use Pho\Crm\Model\AccessToken;
use Pho\Crm\Model\User;
use Pho\Crm\Traits\AuthTrait;
use Psr\Http\Message\ServerRequestInterface;
use Rakit\Validation\Validator;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;

class AuthController
{
    use AuthTrait;

    private $validator;

    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
    }

    public function login(ServerRequestInterface $request)
    {
        $isLoggedIn = $this->isLoggedIn();
        if ($isLoggedIn) {
            return new RedirectResponse(url(''));
        }
        return new HtmlResponse(view('login.php'));
    }

    public function loginPost(ServerRequestInterface $request)
    {
        $body = $request->getParsedBody();

        $validation = $this->validator->validate($body, [
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if ($validation->fails()) {
            $errors = $validation->errors();
            return new HtmlResponse(view('login.php', [
                'body' => $body,
                'errors' => $errors,
            ]));
        }

        $email = $body['email'];
        $password = $body['password'];
        $passwordHashed = md5($password);
        $user = User::where([
            'email' => $email,
            'password' => $passwordHashed,
        ])->first();

        if ($user === null) {
            return new HtmlResponse(view('login.php', [
                'body' => $body,
                'fail_message' => 'Invalid username/password combination',
            ]));
        }

        if (! $user->is_verified) {
            return new HtmlResponse(view('login.php', [
                'body' => $body,
                'fail_message' => 'The user is not verified',
            ]));
        }

        if (! $user->crm_role) {
            return new HtmlResponse(view('login.php', [
                'body' => $body,
                'fail_message' => 'The user is not authorized for CRM',
            ]));
        }

        /**
         * Generate token
         */

        $expireMinutes = config('auth.access_token_expiry_minute');

        $tokenId = base64_encode(\random_bytes(32));

        AccessToken::insertGetId([
            'id' => $tokenId,
            'user_id' => $user->id,
            'revoked' => false,
            'created_at' => new \DateTime(),
            'expires_at' => (new \DateTime())->add(new \DateInterval("PT{$expireMinutes}M")),
        ]);

        $now = Manager::connection()->query()->selectRaw('now() as now')->first()->now;
        User::where('id', $user->id)->update([
            'last_login_timestamp' => $now,
        ]);

        $_SESSION['access_token_id'] = $tokenId;

        return new RedirectResponse(url(''));
    }

    public function logoutPost(ServerRequestInterface $request)
    {
        $response = new RedirectResponse(url('login'));

        $accessToken = $this->getAccessToken();
        if (($accessToken === null)
            || $accessToken->revoked) {
            return $response;
        }
        $accessToken->revoked = true;
        $accessToken->save();

        return $response;
    }
}
