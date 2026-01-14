<?php

declare(strict_types=1);

namespace Application\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Application\Repository\Users;

class AuthController extends AbstractActionController
{
  protected $userRepo;

  public function __construct(Users $userRepo)
  {
    $this->userRepo = $userRepo;
  }

  public function loginAction()
  {
    if (isset($_SESSION['user_id'])) {
      if ($_SESSION['user_type'] == 1) {
        return $this->redirect()->toRoute('admin');
      }
      return $this->redirect()->toRoute('home');
    }

    $loginError = '';
    $signupError = '';
    $signupSuccess = '';
    $activeTab = 'login';

    if ($this->getRequest()->isPost()) {
      $formType = $this->params()->fromPost('form_type');
      if ($formType === 'login') {
        $username = $this->params()->fromPost('username');
        $password = $this->params()->fromPost('password');
        $user = $this->userRepo->findByUsername($username);
        if ($user && $this->userRepo->verifyPassword($password, $user['password'])) {
          $_SESSION['user_id'] = $user['id'];
          $_SESSION['username'] = $user['username'];
          $_SESSION['user_type'] = $user['type'];
          $_SESSION['full_name'] = $user['full_name'];
          if ($user['type'] == 1) {
            return $this->redirect()->toRoute('admin');
          }
          return $this->redirect()->toRoute('home');
        } else {
          $loginError = 'Invalid username or password!';
          $activeTab = 'login';
        }
      } elseif ($formType === 'signup') {
        $username = $this->params()->fromPost('username');
        $password = $this->params()->fromPost('password');
        $full_name = $this->params()->fromPost('full_name');
        $email = $this->params()->fromPost('email');
        if (!$username || !$password || !$full_name || !$email) {
          $signupError = 'Please fill in all fields!';
        } elseif ($username && $this->userRepo->findByUsername($username)) {
          $signupError = 'Username already exists!';
        } else {
          $this->userRepo->create([
            'username' => $username,
            'password' => $password,
            'full_name' => $full_name,
            'email' => $email,
            'type' => 0
          ]);
          $signupSuccess = 'Sign up successful! You can now log in.';
          $activeTab = 'login';
        }
        $activeTab = 'signup';
      }
    }

    $view = new ViewModel([
      'loginError' => $loginError,
      'signupError' => $signupError,
      'signupSuccess' => $signupSuccess,
      'activeTab' => $activeTab
    ]);
    $view->setTerminal(true);
    $view->setTemplate('application/auth/login');
    return $view;
  }

  public function logoutAction()
  {
    unset($_SESSION['user_id']);
    unset($_SESSION['username']);
    unset($_SESSION['user_type']);
    unset($_SESSION['full_name']);

    session_destroy();

    return $this->redirect()->toRoute('home');
  }
}
