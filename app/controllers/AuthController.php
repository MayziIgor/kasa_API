<?php

namespace App\Controllers;

use App\Exceptions\AuthErrorException;
use App\Services\ActiveConnectionService;
use App\Services\UserService;

class AuthController
{
    protected $activeConnectionService;

    protected $userService;

    public function __construct()
    {
        $this->activeConnectionService = new ActiveConnectionService();
        $this->userService             = new UserService();
        date_default_timezone_set('Europe/Kiev');
    }

    public function login(): array
    {
        try {
            $request = request();
            //$_POST = json_decode(file_get_contents("php://input"), true);
            $user_name = $request['user_name'];
            $password  = $request['password'];
            //echo "in";
            logger('Спроба логування.');
            logger($user_name);
            logger($password);

            if (empty($user_name) || empty($password)) {
                throw new AuthErrorException();
            }
            //echo ":dd0";
            $user = $this->userService->getUserById(1);

            if ($user->getLogin() != $user_name || $user->getPassword() != $password) {
                throw new AuthErrorException();
            }
            //echo ":dd0";
            $id = makeGuid();
            logger('Авторизація успішна, id - ' . $id);
            $this->activeConnectionService->create($id);

            return [
                'user_id'       => $id,
                'response_code' => 1,
            ];
        } catch (AuthErrorException $exception) {
            return [
                'user_id'       => '',
                'response_code' => $exception->getErrorCode(),
            ];
        }
    }

    public function logout(): array
    {
        $request = request();
        $user_id = $request['user_id'];
        logger('Спроба розлогіна');

        if (empty($user_id)) {
            return [
                'response_code' => 0,
            ];
        }

        $this->activeConnectionService->disable($user_id);
        logger('Розлогін вдалий.ID:'.$user_id);

        return [
            'response_code' => 1,
        ];
    }
}
