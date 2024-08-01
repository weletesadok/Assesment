<?php

namespace App\Controllers;

use App\Models\User;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Email\Email;

class UserController extends Controller
{
    protected $userModel;
    protected $email;

    public function __construct()
    {
        $this->userModel = new User();
        $this->email = \Config\Services::email();
    }

    public function register()
    {
        $request = service('request');

        if ($request->getHeaderLine('Content-Type') === 'application/json') {
            $data = $request->getJSON(true);
        } else {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                                  ->setJSON(['error' => 'Invalid content type. JSON expected.']);
        }

        if ($this->userModel->where('email', $data['email'])->first()) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_CONFLICT)
                                  ->setJSON(['error' => 'Email already exists.']);
        }

        if (strlen($data['password']) < 8) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                                  ->setJSON(['error' => 'Password must be at least 8 characters long.']);
        }

        $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        $data['token'] = random_int(100000, 999999);
        $data['isVerified'] = false;

        if (!$this->userModel->save($data)) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                                  ->setJSON(['error' => $this->userModel->errors()]);
        }

        if ($this->sendVerificationEmail($data['email'], $data['token'])) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_CREATED)
                                  ->setJSON(['message' => 'Registration successful, please check your email for verification.']);
        } else {
            return $this->response->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                                  ->setJSON(['error' => 'Failed to send verification email']);
        }
    }

    private function sendVerificationEmail($email, $token)
    {
        $this->email->setFrom('weletesadok@gmail.com', 'Ayele Masresha');
        $this->email->setTo($email);
        $this->email->setSubject('Email Verification');
        $this->email->setMessage("Your verification token is: $token");

        if ($this->email->send()) {
            return true;
        } else {
            return false;
        }
    }

    public function verify()
    {
        $request = service('request');

        if ($request->getHeaderLine('Content-Type') === 'application/json') {
            $data = $request->getJSON(true);
        } else {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                                  ->setJSON(['error' => 'Invalid content type. JSON expected.']);
        }

        $email = $data['email'];
        $token = $data['token'];

        $user = $this->userModel->where('email', $email)->first();

        if ($user && $user['token'] == $token) {
            $user['isVerified'] = true;
            $user['token'] = null;

            if ($this->userModel->save($user)) {
                return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
                                      ->setJSON(['message' => 'Email successfully verified']);
            }

            return $this->response->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                                  ->setJSON(['error' => 'Failed to verify email']);
        }

        return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                              ->setJSON(['error' => 'Invalid email or token']);
    }

    public function login()
    {
        $request = service('request');

        if ($request->getHeaderLine('Content-Type') === 'application/json') {
            $data = $request->getJSON(true);
        } else {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                                  ->setJSON(['error' => 'Invalid content type. JSON expected.']);
        }

        $email = $data['email'];
        $password = $data['password'];

        $user = $this->userModel->where('email', $email)->first();

        if ($user && password_verify($password, $user['password']) && $user['isVerified']) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_OK)
                                  ->setJSON(['message' => 'Login successful', 'user' => $user]);
        }

        return $this->response->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
                              ->setJSON(['error' => 'Invalid email or password, or email not verified']);
    }
}
