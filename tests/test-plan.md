# Test Plan: Online Laundry Service System

## How to record results

1. Reset the data before testing: import `database/schema.sql` then `database/seed.sql`.
2. Run each test case in order. Follow the steps exactly.
3. Write what **actually** happened in *Actual result*. Do not copy the expected result.
4. Set *Status* to **Pass** (actual matches expected) or **Fail**. Leave **Not run** until executed.
5. Save a screenshot per test as `tests/evidence/TC-xx.png` and fix any failures, then re-test.

Demo accounts: admin@laundry.test / `Admin@123`, staff1@laundry.test / `Staff@123`, jane@example.com / `Customer@123`, eric@example.com / `Customer@123`. Today's date is used for "future" dates, so pick dates relative to the day you test.

---

### TC-01 Successful customer registration
- **Preconditions:** Logged out; email `newuser@example.com` not in the database.
- **Steps:** Open Register. Enter name "New User", username `newuser`, email `newuser@example.com`, phone `0788999111`, address "KG 1 Ave, Kigali", password `Test1234`, confirm `Test1234`. Submit.
- **Expected:** Redirected to Login with "Registration successful. Please log in." In MySQL, `users` has the new row with a bcrypt hash (starts `$2y$`) and `customers` has a linked row.
- **Actual result:** _(to be filled)_
- **Status:** Not run

### TC-02 Registration with an existing email
- **Preconditions:** Logged out.
- **Steps:** Register with email `jane@example.com` and otherwise valid data.
- **Expected:** Form re-displays with "Email already exists." and no new rows are created.
- **Actual result:** _(to be filled)_
- **Status:** Not run

### TC-03 Login with correct credentials
- **Preconditions:** Logged out.
- **Steps:** Log in as admin, then log out, staff1, log out, jane.
- **Expected:** Admin → Admin dashboard, staff → Staff dashboard, customer → Customer dashboard; each shows a welcome message.
- **Actual result:** _(to be filled)_
- **Status:** Not run

### TC-04 Login with incorrect credentials
- **Preconditions:** Logged out.
- **Steps:** Log in as `jane@example.com` with password `wrong`; then with an unknown email.
- **Expected:** "Incorrect email or password." in both cases; stays on the login page.
- **Actual result:** _(to be filled)_
- **Status:** Not run

### TC-05 Unauthorized access to the admin dashboard
- **Preconditions:** (a) logged out; (b) logged in as jane (customer).
- **Steps:** (a) Open `/admin/index.php`. (b) Open `/admin/index.php`, `/admin/customers.php` and `/staff/index.php`.
- **Expected:** (a) Redirected to Login with "Please log in to continue." (b) "Unauthorized access." (403) for each URL.
- **Actual result:** _(to be filled)_
- **Status:** Not run

### TC-06 Successful creation of a customer (admin)
- **Preconditions:** Logged in as admin.
- **Steps:** Customers → Add customer. Enter valid details with a new email and password `Test1234`. Save.
- **Expected:** "Customer created successfully."; customer appears in the list; the new customer can log in.
- **Actual result:** _(to be filled)_
- **Status:** Not run

### TC-07 Successful update of a customer (admin)
- **Preconditions:** Logged in as admin; a customer exists.
- **Steps:** Customers → Edit on a customer; change the phone number and address; save.
- **Expected:** "Customer updated successfully."; the list shows the new values.
- **Actual result:** _(to be filled)_
- **Status:** Not run

### TC-08 Successful deletion of a customer (admin)
- **Preconditions:** Logged in as admin; use the customer created in TC-06 (no orders).
- **Steps:** Customers → Delete on that customer; confirm the dialog.
- **Expected:** "Customer deleted successfully."; customer gone from the list; the matching `users` row is also gone.
- **Actual result:** _(to be filled)_
- **Status:** Not run

### TC-09 Successful creation of a laundry order
- **Preconditions:** Logged in as jane.
- **Steps:** Schedule Pickup; pickup = tomorrow, delivery = 3 days from today; add Shirt ×2 and Towel ×1; submit.
- **Expected:** "Order created successfully."; order is **Pending**; it appears in My Orders with the correct total.
- **Actual result:** _(to be filled)_
- **Status:** Not run

### TC-10 Successful update of an order status
- **Preconditions:** Logged in as staff1; order #3 (Picked Up) is assigned to staff1.
- **Steps:** Assigned Orders → Open #3 → click "Start Processing". Then try to reach the next stages in order.
- **Expected:** Status becomes Processing, then Ready for Delivery, Out for Delivery and Delivered one step at a time, with a success message each time. The customer sees the new status.
- **Actual result:** _(to be filled)_
- **Status:** Not run

### TC-11 Invalid pickup and delivery dates
- **Preconditions:** Logged in as jane. To bypass the browser's date check, test (a) in the browser and (b)/(c) after disabling JavaScript or by editing the page.
- **Steps:** (a) Delivery = pickup date. (b) Delivery before pickup. (c) Pickup in the past.
- **Expected:** Order is not created. Messages: "Delivery date must be later than pickup date." / "Pickup date cannot be in the past."
- **Actual result:** _(to be filled)_
- **Status:** Not run

### TC-12 Adding multiple laundry items to an order
- **Preconditions:** Logged in as admin.
- **Steps:** Orders → New order. Select a customer; valid dates; add 3 different items (e.g. Shirt ×3, Dress ×1, Blanket ×2); save.
- **Expected:** Order details page lists all 3 items with quantities, unit prices and subtotals.
- **Actual result:** _(to be filled)_
- **Status:** Not run

### TC-13 Automatic order total calculation
- **Preconditions:** Same order as TC-12.
- **Steps:** While filling the form, watch the total update. After saving, compare the total with a manual calculation and with MySQL: `SELECT total, (SELECT SUM(quantity*unit_price) FROM order_items WHERE order_id = o.order_id) FROM orders o WHERE order_id = <id>;`
- **Expected:** The on-page total, the saved total and the SQL sum are equal.
- **Actual result:** _(to be filled)_
- **Status:** Not run

### TC-14 Live order search using Fetch API
- **Preconditions:** Logged in as admin; seed data loaded; browser developer tools open (Network tab).
- **Steps:** On the Admin dashboard type `Jane`, then `3`, then `processing`, then `zzzz`.
- **Expected:** A request to `api/search-orders.php?q=...` is sent for each search; matching orders appear without a page reload; `zzzz` shows "No matching orders found." The JSON contains no passwords or emails.
- **Actual result:** _(to be filled)_
- **Status:** Not run

### TC-15 Invalid CSRF token submission
- **Preconditions:** Logged in as admin.
- **Steps:** Open the Add customer form. In developer tools, change the hidden `csrf_token` value (or delete the input), then submit.
- **Expected:** Request is rejected with a 419 page ("Invalid or expired security token…"); nothing is saved.
- **Actual result:** _(to be filled)_
- **Status:** Not run

### TC-16 Customer attempting to access another customer's order
- **Preconditions:** Logged in as jane (owns orders #1 and #2); order #3 belongs to Eric.
- **Steps:** Open `/customer/order.php?id=3`. Also try POSTing a cancel for order #4 (Eric's pending order) by editing the hidden `order_id` on the cancel form of one of Jane's pending orders.
- **Expected:** "Order not found." (404) in both cases; Eric's orders are unchanged.
- **Actual result:** _(to be filled)_
- **Status:** Not run

---

## Additional recommended checks (optional)

| ID | Test | Expected | Actual | Status |
|---|---|---|---|---|
| TC-17 | Customer cancels a pending order | Status becomes Cancelled; cancelling a confirmed order is refused | | Not run |
| TC-18 | Admin assigns staff to an order | Assignment saved; the staff member sees the order | | Not run |
| TC-19 | Staff opens an order not assigned to them | "Order not found." | | Not run |
| TC-20 | Logout, then press the browser Back button | Protected pages are not shown; login required | | Not run |
| TC-21 | Resize to ~375px wide | Menu collapses, tables scroll, forms stay usable | | Not run |
| TC-22 | Name a customer `<script>alert(1)</script>` | Shown as plain text, no alert | | Not run |

## Summary (fill in after testing)

| Total executed | Passed | Failed |
|---|---|---|
| | | |
