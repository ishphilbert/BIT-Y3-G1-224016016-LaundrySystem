<?php
/** app/controllers/AuthController.php - registration, login and logout. */
class AuthController
{
    public function login(): void
    {
        if (isLoggedIn()) {
            redirect(dashboardPathForRole($_SESSION['role']));
        }
        $audience = postStr('audience');
        if ($audience === '') {
            $audience = (string) ($_GET['type'] ?? '');
        }
        if (!in_array($audience, ['customer', 'staff'], true)) {
            $audience = '';
        }
        $errors = [];
        $old    = ['email' => ''];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();
            $postedAudience = postStr('audience');
            $audience = in_array($postedAudience, ['customer', 'staff'], true) ? $postedAudience : '';
            if ($audience === '') {
                $errors[] = 'Choose a login type.';
            }
            $email    = strtolower(postStr('email'));
            $password = postPassword('password');
            $old['email'] = $email;

            if (!validEmail($email)) {
                $errors[] = 'Invalid email address.';
            }
            if ($password === '') {
                $errors[] = 'Password is required.';
            }
            if (!$errors) {
                $user = User::findByEmail($email);
                $roleMatchesAudience = $user && (
                    ($audience === 'customer' && $user['role'] === 'customer') ||
                    ($audience === 'staff' && in_array($user['role'], ['admin', 'staff'], true))
                );
                if ($user && $roleMatchesAudience && password_verify($password, $user['password_hash'])) {
                    loginUser($user);
                    setFlash('success', 'Welcome back, ' . $user['username'] . '!');
                    redirect(dashboardPathForRole($user['role']));
                }
                $errors[] = 'Incorrect email or password.'; // same message for unknown email and wrong password
            }
        }
        render('auth/login', ['title' => 'Login', 'errors' => $errors, 'old' => $old, 'audience' => $audience], 'public');
    }

    public function register(): void
    {
        if (isLoggedIn()) {
            redirect(dashboardPathForRole($_SESSION['role']));
        }
        $errors = [];
        $old    = ['username' => '', 'email' => '', 'full_name' => '', 'phone' => '', 'address' => ''];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();
            $data     = validateCustomerFields($errors);
            $password = postPassword('password');
            $confirm  = postPassword('confirm_password');
            $old      = $data;

            if (!validPassword($password)) {
                $errors[] = 'Password must be at least 8 characters and contain a letter and a number.';
            } elseif ($password !== $confirm) {
                $errors[] = 'Passwords do not match.';
            }
            if (!$errors && User::emailExists($data['email'])) {
                $errors[] = 'Email already exists.';
            }
            if (!$errors) {
                try {
                    Customer::createWithUser($data, $password);
                    setFlash('success', 'Registration successful. Please log in.');
                    redirect('login.php');
                } catch (PDOException $ex) {
                    error_log('Registration failed: ' . $ex->getMessage());
                    $errors[] = ($ex->getCode() === '23000')
                        ? 'Email already exists.'
                        : 'Registration failed. Please try again.';
                }
            }
        }
        render('auth/register', ['title' => 'Create Account', 'errors' => $errors, 'old' => $old], 'public');
    }

    public function forgotPassword(): void
    {
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();
            $email = strtolower(postStr('email'));
            if (!validEmail($email)) {
                $errors[] = 'Enter a valid email address.';
            } else {
                $user = User::findByEmail($email);
                if ($user) {
                    try {
                        $token = bin2hex(random_bytes(32));
                        User::createPasswordReset((int) $user['user_id'], hash('sha256', $token), date('Y-m-d H:i:s', time() + 3600));
                        $link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://')
                            . ($_SERVER['HTTP_HOST'] ?? 'localhost') . url('reset-password.php?token=' . urlencode($token));
                        $subject = APP_NAME . ' password reset';
                        $message = "Hello {$user['username']},\n\nUse this link within one hour to reset your password:\n{$link}\n\nIf you did not request this, you can ignore this email.";
                        $from = RESET_EMAIL_FROM;
                        $headers = 'From: ' . APP_NAME . ' <' . $from . ">\r\n" . "Content-Type: text/plain; charset=UTF-8";
                        if (!mail($email, $subject, $message, $headers)) {
                            error_log('Password reset email could not be sent for user ' . (int) $user['user_id']);
                        }
                    } catch (Throwable $e) {
                        error_log('Password reset request failed: ' . $e->getMessage());
                    }
                }
                setFlash('success', 'If an account exists for that email address, a password reset link has been sent.');
                redirect('login.php');
            }
        }
        render('auth/forgot_password', ['title' => 'Forgot Password', 'errors' => $errors], 'public');
    }

    public function resetPassword(): void
    {
        $token = $_SERVER['REQUEST_METHOD'] === 'POST' ? postStr('token') : (string) ($_GET['token'] ?? '');
        $reset = preg_match('/^[a-f0-9]{64}$/', $token) ? User::findPasswordReset(hash('sha256', $token)) : false;
        if (!$reset) {
            setFlash('error', 'That password reset link is invalid or has expired.');
            redirect('forgot-password.php');
        }
        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();
            $password = postPassword('password');
            $confirm = postPassword('confirm_password');
            if (!validPassword($password)) {
                $errors[] = 'Password must be at least 8 characters and contain a letter and a number.';
            } elseif ($password !== $confirm) {
                $errors[] = 'Passwords do not match.';
            }
            if (!$errors) {
                User::resetPassword((int) $reset['user_id'], $password, hash('sha256', $token));
                setFlash('success', 'Your password has been reset. Please log in.');
                redirect('login.php');
            }
        }
        render('auth/reset_password', ['title' => 'Reset Password', 'errors' => $errors, 'token' => $token], 'public');
    }

    public function logout(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('index.php');
        }
        verifyCsrf();
        logoutUser();
        session_start();
        setFlash('success', 'You have been logged out.');
        redirect('login.php');
    }
}
