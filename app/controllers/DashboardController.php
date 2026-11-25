<?php
/** app/controllers/DashboardController.php - statistics for the three dashboards. */
class DashboardController
{
    /** Sum the counts of each status group (pending, processing, completed, delivered, cancelled). */
    private function groupCounts(array $counts): array
    {
        $out = [];
        foreach (statusGroups() as $group => $statuses) {
            $n = 0;
            foreach ($statuses as $s) {
                $n += $counts[$s]['n'] ?? 0;
            }
            $out[$group] = $n;
        }
        return $out;
    }

    public function admin(): void
    {
        requireRole(['admin']);
        $counts  = Order::statusCounts();
        $revenue = 0.0;
        $total   = 0;
        foreach ($counts as $status => $row) {
            $total += $row['n'];
            if ($status !== 'cancelled') {
                $revenue += $row['revenue'];
            }
        }
        render('admin/dashboard', [
            'title'            => 'Admin Dashboard',
            'customerCount'    => Customer::count(),
            'staffCount'       => User::countByRole('staff'),
            'totalOrders'      => $total,
            'groups'           => $this->groupCounts($counts),
            'revenue'          => $revenue,
            'deliveredRevenue' => $counts['delivered']['revenue'] ?? 0.0,
            'recent'           => Order::search([], 5),
        ]);
    }

    public function staff(): void
    {
        $user   = requireRole(['staff']);
        $counts = Order::statusCounts(null, (int) $user['user_id']);
        $total  = 0;
        foreach ($counts as $row) {
            $total += $row['n'];
        }
        render('staff/dashboard', [
            'title'  => 'Staff Dashboard',
            'total'  => $total,
            'groups' => $this->groupCounts($counts),
            'active' => Order::search(['staff_id' => $user['user_id'], 'statuses' => activeStatuses()], 10),
        ]);
    }

    public function customer(): void
    {
        $user     = requireRole(['customer']);
        $customer = Customer::findByUserId((int) $user['user_id']);
        if (!$customer) {
            abortWith(403, 'Customer profile not found.');
        }
        $counts = Order::statusCounts((int) $customer['customer_id']);
        $total  = 0;
        $active = 0;
        $spent  = 0.0;
        foreach ($counts as $status => $row) {
            $total += $row['n'];
            if (in_array($status, activeStatuses(), true)) {
                $active += $row['n'];
            }
            if ($status === 'delivered') {
                $spent += $row['revenue'];
            }
        }
        render('customer/dashboard', [
            'title'     => 'My Dashboard',
            'customer'  => $customer,
            'total'     => $total,
            'active'    => $active,
            'delivered' => $counts['delivered']['n'] ?? 0,
            'spent'     => $spent,
            'recent'    => Order::search(['customer_id' => $customer['customer_id']], 5),
        ]);
    }
}
