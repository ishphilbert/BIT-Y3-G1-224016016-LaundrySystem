<?php
/** app/controllers/CustomerController.php - customer CRUD (admin) and own profile (customer). */
class CustomerController
{
    public function adminIndex(): void
    {
        requireRole(['admin']);
        $q = getStr('q');
        render('admin/customers', [
            'title'     => 'Customers',
            'customers' => Customer::all($q),
            'q'         => $q,
        ]);
    }

    /** Create (no id) or edit (?id=) a customer. */
    public function adminForm(): void
    {
        requireRole(['admin']);
        $id       = (int) ($_GET['id'] ?? 0);
        $customer = null;
        if ($id > 0) {
            $customer = Customer::find($id);
            if (!$customer) {
                abortWith(404, 'Customer not found.');
            }
        }
        $errors = [];
        $old    = $customer ?: ['username' => '', 'email' => '', 'full_name' => '', 'phone' => '', 'address' => ''];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();
            $data     = validateCustomerFields($errors);
            $password = postPassword('password');
            $old      = $data;

            if (!$customer && !validPassword($password)) {
                $errors[] = 'Password must be at least 8 characters and contain a letter and a number.';
            }
            if ($customer && $password !== '' && !validPassword($password)) {
                $errors[] = 'New password must be at least 8 characters and contain a letter and a number.';
            }
            $excludeUser = $customer ? (int) $customer['user_id'] : 0;
            if (!$errors && User::emailExists($data['email'], $excludeUser)) {
                $errors[] = 'Email already exists.';
            }
            if (!$errors) {
                try {
                    if ($customer) {
                        Customer::updateWithUser($id, $data, $password);
                        setFlash('success', 'Customer updated successfully.');
                    } else {
                        Customer::createWithUser($data, $password);
                        setFlash('success', 'Customer created successfully.');
                    }
                    redirect('admin/customers.php');
                } catch (PDOException $ex) {
                    error_log('Customer save failed: ' . $ex->getMessage());
                    $errors[] = ($ex->getCode() === '23000') ? 'Email already exists.' : 'Could not save the customer.';
                }
            }
        }
        render('admin/customer_form', [
            'title'    => $customer ? 'Edit Customer' : 'Add Customer',
            'customer' => $customer,
            'errors'   => $errors,
            'old'      => $old,
        ]);
    }

    public function adminDelete(): void
    {
        requireRole(['admin']);
        requirePost();
        verifyCsrf();
        $id = (int) postStr('customer_id');
        if (!Customer::find($id)) {
            abortWith(404, 'Customer not found.');
        }
        Customer::delete($id);
        setFlash('success', 'Customer deleted successfully.');
        redirect('admin/customers.php');
    }

    /** Customers view and update only their own profile. */
    public function profile(): void
    {
        $user     = requireRole(['customer']);
        $customer = Customer::findByUserId((int) $user['user_id']);
        if (!$customer) {
            abortWith(403, 'Customer profile not found.');
        }
        $errors = [];
        $old    = $customer;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();
            $data = validateCustomerFields($errors);
            $data['username'] = $customer['username'];   // username is not editable here
            $old = $data;

            $current  = postPassword('current_password');
            $newPass  = postPassword('new_password');
            $confirm  = postPassword('confirm_password');
            if ($newPass !== '') {
                if (!password_verify($current, User::getHash((int) $user['user_id']))) {
                    $errors[] = 'Current password is incorrect.';
                } elseif (!validPassword($newPass)) {
                    $errors[] = 'New password must be at least 8 characters and contain a letter and a number.';
                } elseif ($newPass !== $confirm) {
                    $errors[] = 'Passwords do not match.';
                }
            }
            if (!$errors && User::emailExists($data['email'], (int) $user['user_id'])) {
                $errors[] = 'Email already exists.';
            }
            if (!$errors) {
                try {
                    Customer::updateWithUser((int) $customer['customer_id'], $data, $newPass);
                    setFlash('success', 'Profile updated successfully.');
                    redirect('customer/profile.php');
                } catch (PDOException $ex) {
                    error_log('Profile update failed: ' . $ex->getMessage());
                    $errors[] = 'Could not update your profile.';
                }
            }
        }
        render('customer/profile', [
            'title'    => 'My Profile',
            'customer' => $customer,
            'errors'   => $errors,
            'old'      => $old,
        ]);
    }
}
