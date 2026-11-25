<?php
/** app/controllers/OrderController.php - order management for admin, staff and customers. */
class OrderController
{
    private function catalog(): array
    {
        return require APP_PATH . '/config/items.php';
    }

    /** Load an order or stop with "Order not found." */
    private function loadOrder(int $id): array
    {
        $order = Order::find($id);
        if (!$order) {
            abortWith(404, 'Order not found.');
        }
        return $order;
    }

    // ------------------------------------------------------------------ ADMIN

    public function adminIndex(): void
    {
        requireRole(['admin']);
        $q      = getStr('q');
        $status = getStr('status');
        if (!array_key_exists($status, orderStatuses())) {
            $status = '';
        }
        render('admin/orders', [
            'title'  => 'Orders',
            'orders' => Order::search(['q' => $q, 'status' => $status]),
            'q'      => $q,
            'status' => $status,
        ]);
    }

    public function adminView(): void
    {
        requireRole(['admin']);
        $order = $this->loadOrder((int) ($_GET['id'] ?? 0));
        render('admin/order_view', [
            'title' => 'Order #' . (int) $order['order_id'],
            'order' => $order,
            'items' => OrderItem::forOrder((int) $order['order_id']),
            'staff' => User::staffList(),
        ]);
    }

    /** Create (no id) or edit (?id=) an order with its items. */
    public function adminForm(): void
    {
        requireRole(['admin']);
        $catalog = $this->catalog();
        $id      = (int) ($_GET['id'] ?? 0);
        $order   = null;
        if ($id > 0) {
            $order = $this->loadOrder($id);
            if (in_array($order['status'], ['delivered', 'cancelled'], true)) {
                setFlash('error', 'Delivered or cancelled orders cannot be edited.');
                redirect('admin/order.php?id=' . $id);
            }
        }
        $errors = [];
        if ($order) {
            $old = [
                'customer_id'       => $order['customer_id'],
                'assigned_staff_id' => $order['assigned_staff_id'],
                'pickup_date'       => $order['pickup_date'],
                'delivery_date'     => $order['delivery_date'],
                'status'            => $order['status'],
            ];
            $rows = OrderItem::forOrder($id);
        } else {
            $old  = ['customer_id' => '', 'assigned_staff_id' => '', 'pickup_date' => '', 'delivery_date' => '', 'status' => 'pending'];
            $rows = [];
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();
            $old = [
                'customer_id'       => postStr('customer_id'),
                'assigned_staff_id' => postStr('assigned_staff_id'),
                'pickup_date'       => postStr('pickup_date'),
                'delivery_date'     => postStr('delivery_date'),
                'status'            => $order ? postStr('status') : 'pending',
            ];
            $rows = readItemRows();

            $customerId = (int) $old['customer_id'];
            if ($customerId < 1 || !Customer::find($customerId)) {
                $errors[] = 'Please select a valid customer.';
            }
            $staffId = null;
            if ($old['assigned_staff_id'] !== '') {
                $staff = User::findById((int) $old['assigned_staff_id']);
                if (!$staff || $staff['role'] !== 'staff') {
                    $errors[] = 'The selected staff member is invalid.';
                } else {
                    $staffId = (int) $staff['user_id'];
                }
            }
            $checkPast = !$order || $old['pickup_date'] !== $order['pickup_date'];
            validateOrderDates($old['pickup_date'], $old['delivery_date'], $checkPast, $errors);
            $items = validateItemRows($rows, $catalog, true, $errors);

            $status = $order ? $order['status'] : 'pending';
            if ($order && $old['status'] !== $order['status']) {
                if (canTransition($order['status'], $old['status'])) {
                    $status = $old['status'];
                } else {
                    $errors[] = 'Invalid status change: ' . statusLabel($order['status']) . ' cannot move to that status.';
                }
            }

            if (!$errors) {
                try {
                    if ($order) {
                        Order::update($id, $customerId, $staffId, $old['pickup_date'], $old['delivery_date'], $items, $status);
                        setFlash('success', 'Order updated successfully.');
                        redirect('admin/order.php?id=' . $id);
                    }
                    $newId = Order::create($customerId, $staffId, $old['pickup_date'], $old['delivery_date'], $items);
                    setFlash('success', 'Order created successfully.');
                    redirect('admin/order.php?id=' . $newId);
                } catch (PDOException $ex) {
                    error_log('Order save failed: ' . $ex->getMessage());
                    $errors[] = 'Could not save the order. Please try again.';
                }
            }
        }
        if (!$rows) {
            $rows = [['item_type' => '', 'quantity' => '1', 'unit_price' => '']];
        }
        render('admin/order_form', [
            'title'     => $order ? 'Edit Order #' . $id : 'Create Order',
            'order'     => $order,
            'errors'    => $errors,
            'old'       => $old,
            'rows'      => $rows,
            'catalog'   => $catalog,
            'customers' => Customer::all(),
            'staff'     => User::staffList(),
            'allowed'   => $order ? allowedNextStatuses($order['status']) : [],
        ]);
    }

    public function adminDelete(): void
    {
        requireRole(['admin']);
        requirePost();
        verifyCsrf();
        $order = $this->loadOrder((int) postStr('order_id'));
        Order::delete((int) $order['order_id']);
        setFlash('success', 'Order deleted successfully.');
        redirect('admin/orders.php');
    }

    public function adminAssign(): void
    {
        requireRole(['admin']);
        requirePost();
        verifyCsrf();
        $order   = $this->loadOrder((int) postStr('order_id'));
        $staffId = null;
        $posted  = postStr('staff_id');
        if (in_array($order['status'], ['delivered', 'cancelled'], true)) {
            setFlash('error', 'Staff cannot be changed on delivered or cancelled orders.');
        } else {
            $ok = true;
            if ($posted !== '') {
                $staff = User::findById((int) $posted);
                if (!$staff || $staff['role'] !== 'staff') {
                    setFlash('error', 'The selected staff member is invalid.');
                    $ok = false;
                } else {
                    $staffId = (int) $staff['user_id'];
                }
            }
            if ($ok) {
                Order::assignStaff((int) $order['order_id'], $staffId);
                setFlash('success', $staffId ? 'Staff member assigned successfully.' : 'Staff assignment removed.');
            }
        }
        redirect('admin/order.php?id=' . (int) $order['order_id']);
    }

    public function reports(): void
    {
        requireRole(['admin']);
        $counts = Order::statusCounts();
        render('admin/reports', [
            'title'      => 'Order Status Reports',
            'counts'     => $counts,
            'workload'   => Order::staffWorkload(),
            'unassigned' => Order::unassignedCount(),
        ]);
    }

    // ------------------------------------------------- STATUS (admin + staff)

    /** Move an order to its next status, following the workflow. */
    public function changeStatus(array $roles): void
    {
        $user = requireRole($roles);
        requirePost();
        verifyCsrf();
        $id    = (int) postStr('order_id');
        $new   = postStr('new_status');
        $order = $this->loadOrder($id);

        if ($user['role'] === 'staff' && (int) $order['assigned_staff_id'] !== (int) $user['user_id']) {
            abortWith(404, 'Order not found.');   // staff only see their own assigned orders
        }
        if (!array_key_exists($new, orderStatuses())) {
            setFlash('error', 'Invalid status.');
        } elseif ($user['role'] === 'staff' && $new === 'cancelled') {
            setFlash('error', 'Staff members cannot cancel orders.');
        } elseif (!canTransition($order['status'], $new)) {
            setFlash('error', 'Invalid status change: ' . statusLabel($order['status']) . ' cannot move to ' . statusLabel($new) . '.');
        } else {
            Order::updateStatus($id, $new);
            setFlash('success', 'Order #' . $id . ' is now ' . statusLabel($new) . '.');
        }
        redirect(($user['role'] === 'admin' ? 'admin' : 'staff') . '/order.php?id=' . $id);
    }

    // ------------------------------------------------------------------ STAFF

    private function staffFilters(int $staffId, array $statuses): array
    {
        $q      = getStr('q');
        $status = getStr('status');
        $filter = ['staff_id' => $staffId, 'q' => $q];
        if ($status !== '' && in_array($status, $statuses, true)) {
            $filter['status'] = $status;
        } else {
            $status = '';
            $filter['statuses'] = $statuses;
        }
        return [$filter, $q, $status];
    }

    public function staffIndex(): void
    {
        $user = requireRole(['staff']);
        list($filter, $q, $status) = $this->staffFilters((int) $user['user_id'], activeStatuses());
        render('staff/orders', [
            'title'    => 'Assigned Orders',
            'heading'  => 'My Assigned Orders',
            'orders'   => Order::search($filter),
            'q'        => $q,
            'status'   => $status,
            'choices'  => activeStatuses(),
            'page'     => 'orders.php',
        ]);
    }

    public function staffHistory(): void
    {
        $user = requireRole(['staff']);
        list($filter, $q, $status) = $this->staffFilters((int) $user['user_id'], ['delivered', 'cancelled']);
        render('staff/orders', [
            'title'    => 'Order History',
            'heading'  => 'Order History',
            'orders'   => Order::search($filter),
            'q'        => $q,
            'status'   => $status,
            'choices'  => ['delivered', 'cancelled'],
            'page'     => 'history.php',
        ]);
    }

    public function staffView(): void
    {
        $user  = requireRole(['staff']);
        $order = $this->loadOrder((int) ($_GET['id'] ?? 0));
        if ((int) $order['assigned_staff_id'] !== (int) $user['user_id']) {
            abortWith(404, 'Order not found.');
        }
        $next = array_values(array_diff(allowedNextStatuses($order['status']), ['cancelled']));
        render('staff/order_view', [
            'title' => 'Order #' . (int) $order['order_id'],
            'order' => $order,
            'items' => OrderItem::forOrder((int) $order['order_id']),
            'next'  => $next,
        ]);
    }

    // --------------------------------------------------------------- CUSTOMER

    private function myProfile(array $user): array
    {
        $customer = Customer::findByUserId((int) $user['user_id']);
        if (!$customer) {
            abortWith(403, 'Customer profile not found.');
        }
        return $customer;
    }

    /** Load an order that must belong to the logged-in customer. */
    private function myOrder(array $customer, int $id): array
    {
        $order = $this->loadOrder($id);
        if ((int) $order['customer_id'] !== (int) $customer['customer_id']) {
            abortWith(404, 'Order not found.');   // never reveal other customer orders
        }
        return $order;
    }

    public function customerItems(): void
    {
        requireRole(['customer']);
        render('customer/items', ['title' => 'Items & Prices', 'catalog' => $this->catalog()]);
    }

    public function customerIndex(): void
    {
        $user     = requireRole(['customer']);
        $customer = $this->myProfile($user);
        $filter   = getStr('filter');
        $search   = ['customer_id' => $customer['customer_id']];
        if ($filter === 'active') {
            $search['statuses'] = activeStatuses();
        } elseif ($filter === 'completed') {
            $search['status'] = 'delivered';
        } else {
            $filter = '';
        }
        render('customer/orders', [
            'title'  => 'My Orders',
            'orders' => Order::search($search),
            'filter' => $filter,
        ]);
    }

    public function customerForm(): void
    {
        $user     = requireRole(['customer']);
        $customer = $this->myProfile($user);
        $catalog  = $this->catalog();
        $errors   = [];
        $old      = ['pickup_date' => '', 'delivery_date' => '', 'address' => $customer['address']];
        $rows     = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            verifyCsrf();
            $old = [
                'pickup_date'   => postStr('pickup_date'),
                'delivery_date' => postStr('delivery_date'),
                'address'       => postStr('address'),
            ];
            $rows = readItemRows();

            if (strlen($old['address']) < 5 || strlen($old['address']) > 500) {
                $errors[] = 'Pickup address is required (5-500 characters).';
            }
            validateOrderDates($old['pickup_date'], $old['delivery_date'], true, $errors);
            $items = validateItemRows($rows, $catalog, false, $errors);   // catalogue prices only

            if (!$errors) {
                try {
                    $newId = Order::create(
                        (int) $customer['customer_id'], null, $old['pickup_date'], $old['delivery_date'],
                        $items, 'pending', $old['address']
                    );
                    setFlash('success', 'Order created successfully.');
                    redirect('customer/order.php?id=' . $newId);
                } catch (PDOException $ex) {
                    error_log('Customer order failed: ' . $ex->getMessage());
                    $errors[] = 'Could not place your order. Please try again.';
                }
            }
        }
        if (!$rows) {
            $rows = [['item_type' => '', 'quantity' => '1', 'unit_price' => '']];
        }
        render('customer/order_form', [
            'title'   => 'Schedule Pickup',
            'errors'  => $errors,
            'old'     => $old,
            'rows'    => $rows,
            'catalog' => $catalog,
        ]);
    }

    public function customerView(): void
    {
        $user     = requireRole(['customer']);
        $customer = $this->myProfile($user);
        $order    = $this->myOrder($customer, (int) ($_GET['id'] ?? 0));
        render('customer/order_view', [
            'title' => 'Order #' . (int) $order['order_id'],
            'order' => $order,
            'items' => OrderItem::forOrder((int) $order['order_id']),
        ]);
    }

    public function customerCancel(): void
    {
        $user     = requireRole(['customer']);
        requirePost();
        verifyCsrf();
        $customer = $this->myProfile($user);
        $order    = $this->myOrder($customer, (int) postStr('order_id'));
        if ($order['status'] !== 'pending') {
            setFlash('error', 'Only pending orders can be cancelled.');
        } else {
            Order::updateStatus((int) $order['order_id'], 'cancelled');
            setFlash('success', 'Order #' . (int) $order['order_id'] . ' has been cancelled.');
        }
        redirect('customer/order.php?id=' . (int) $order['order_id']);
    }
}
