<?php
/** app/controllers/UserController.php - user account management (administrators only). */
class UserController
{
    public function adminIndex(): void
    {
        requireRole(['admin']);
        $role = getStr('role');
        if (!in_array($role, ['admin', 'staff', 'customer'], true)) {
            $role = '';
        }
        $q = getStr('q');
        render('admin/users', [
            'title' => 'User Accounts',
            'users' => User::all($role, $q),
            'role'  => $role,
            'q'     => $q,
        ]);
    }

    /** Register a staff account (no id) or edit any account's username/email/password (?id=). */
    public function adminForm(): void
    {
        requireRole(['admin']);
        $id   = (int) ($_GET['id'] ?? 0);
        $edit = null;
        if ($id > 0) {
            $edit = User::findById($id);
            if (!$edit) {
                abortWith(404, 'User not found.');
            }
        }
        $errors = [];
        $old    = $edit ?: ['username' => '', 'email' => ''];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();
            $username = postStr('username');
            $email    = strtolower(postStr('email'));
            $password = postPassword('password');
            $old      = ['username' => $username, 'email' => $email];

            if (preg_match('/^[A-Za-z0-9 _.\-]{3,100}$/', $username) !== 1) {
                $errors[] = 'Username must be 3-100 characters (letters, numbers, spaces, . _ -).';
            }
            if (!validEmail($email)) {
                $errors[] = 'Invalid email address.';
            }
            if (!$edit && !validPassword($password)) {
                $errors[] = 'Password must be at least 8 characters and contain a letter and a number.';
            }
            if ($edit && $password !== '' && !validPassword($password)) {
                $errors[] = 'New password must be at least 8 characters and contain a letter and a number.';
            }
            if (!$errors && User::emailExists($email, $edit ? (int) $edit['user_id'] : 0)) {
                $errors[] = 'Email already exists.';
            }
            if (!$errors) {
                try {
                    if ($edit) {
                        User::update((int) $edit['user_id'], $username, $email, $password);
                        setFlash('success', 'User updated successfully.');
                    } else {
                        User::create($username, $email, $password, 'staff');
                        setFlash('success', 'Staff account created successfully.');
                    }
                    redirect('admin/users.php');
                } catch (PDOException $ex) {
                    error_log('User save failed: ' . $ex->getMessage());
                    $errors[] = ($ex->getCode() === '23000') ? 'Email already exists.' : 'Could not save the user.';
                }
            }
        }
        render('admin/user_form', [
            'title'  => $edit ? 'Edit User' : 'Register Staff',
            'edit'   => $edit,
            'errors' => $errors,
            'old'    => $old,
        ]);
    }

    public function adminDelete(): void
    {
        $admin = requireRole(['admin']);
        requirePost();
        verifyCsrf();
        $id   = (int) postStr('user_id');
        $user = User::findById($id);
        if (!$user) {
            abortWith(404, 'User not found.');
        }
        if ($id === (int) $admin['user_id']) {
            setFlash('error', 'You cannot delete your own account.');
        } elseif ($user['role'] === 'admin' && User::countByRole('admin') <= 1) {
            setFlash('error', 'The last administrator account cannot be deleted.');
        } else {
            User::delete($id);
            setFlash('success', 'User deleted successfully.');
        }
        redirect('admin/users.php');
    }
}
