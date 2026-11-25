/* public/assets/js/app.js - vanilla JavaScript: navigation, validation, order form, live search. */
document.addEventListener('DOMContentLoaded', function () {
    'use strict';

    // ---------- Mobile navigation ----------
    var navToggle = document.getElementById('navToggle');
    var navLinks = document.getElementById('navLinks');
    if (navToggle && navLinks) {
        navToggle.addEventListener('click', function () {
            var open = navLinks.classList.toggle('open');
            navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
    }
    var sidebarToggle = document.getElementById('sidebarToggle');
    var sidebar = document.getElementById('sidebar');
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', function () { sidebar.classList.toggle('open'); });
    }

    // ---------- Confirm before destructive actions ----------
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!window.confirm(form.getAttribute('data-confirm'))) {
                event.preventDefault();
            }
        });
    });

    // ---------- Client-side form validation (the server validates again) ----------
    document.querySelectorAll('form[data-validate]').forEach(function (form) {
        // password confirmation: <input data-match="id-of-password-field">
        form.querySelectorAll('input[data-match]').forEach(function (confirmField) {
            var original = form.querySelector('#' + confirmField.getAttribute('data-match'));
            if (!original) { return; }
            var check = function () {
                var mismatch = confirmField.value !== original.value;
                confirmField.setCustomValidity(mismatch ? 'Passwords do not match.' : '');
            };
            confirmField.addEventListener('input', check);
            original.addEventListener('input', check);
        });

        // dates: delivery must be later than pickup
        var pickup = form.querySelector('#pickup_date');
        var delivery = form.querySelector('#delivery_date');
        if (pickup && delivery) {
            var checkDates = function () {
                var bad = pickup.value && delivery.value && delivery.value <= pickup.value;
                delivery.setCustomValidity(bad ? 'Delivery date must be later than pickup date.' : '');
            };
            pickup.addEventListener('change', checkDates);
            delivery.addEventListener('change', checkDates);
            checkDates();
        }

        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                form.reportValidity();
            }
        });
    });

    // ---------- Order form: dynamic item rows and automatic totals ----------
    var rowsBox = document.getElementById('itemRows');
    if (rowsBox) {
        var priceEditable = rowsBox.getAttribute('data-price-editable') === '1';
        var template = document.getElementById('itemRowTemplate');
        var totalEl = document.getElementById('orderTotal');

        var recalc = function () {
            var total = 0;
            rowsBox.querySelectorAll('.item-row').forEach(function (row) {
                var qty = parseInt(row.querySelector('.item-qty').value, 10);
                var price = parseFloat(row.querySelector('.item-price').value);
                var sub = (qty > 0 && price >= 0) ? qty * price : 0;
                row.querySelector('.item-subtotal').textContent = sub.toFixed(2);
                total += sub;
            });
            totalEl.textContent = total.toFixed(2);
        };

        var setDefaultPrice = function (row, force) {
            var select = row.querySelector('.item-type');
            var priceInput = row.querySelector('.item-price');
            var option = select.options[select.selectedIndex];
            if (option && option.getAttribute('data-price') && (force || priceInput.value === '')) {
                priceInput.value = parseFloat(option.getAttribute('data-price')).toFixed(2);
            }
        };

        rowsBox.addEventListener('change', function (event) {
            if (event.target.classList.contains('item-type')) {
                setDefaultPrice(event.target.closest('.item-row'), true);
            }
            recalc();
        });
        rowsBox.addEventListener('input', recalc);
        rowsBox.addEventListener('click', function (event) {
            if (event.target.classList.contains('remove-item')) {
                if (rowsBox.querySelectorAll('.item-row').length > 1) {
                    event.target.closest('.item-row').remove();
                    recalc();
                }
            }
        });
        document.getElementById('addItemRow').addEventListener('click', function () {
            rowsBox.appendChild(template.content.cloneNode(true));
            recalc();
        });

        rowsBox.querySelectorAll('.item-row').forEach(function (row) {
            // customers always see the catalogue price; admins keep a saved price if there is one
            setDefaultPrice(row, !priceEditable);
        });
        recalc();
    }

    // ---------- Live order search (Fetch API + JSON) ----------
    var search = document.getElementById('liveSearch');
    if (search) {
        var msg = document.getElementById('liveSearchMsg');
        var table = document.getElementById('liveResults');
        var tbody = table.querySelector('tbody');
        var endpoint = search.getAttribute('data-endpoint');
        var orderUrl = search.getAttribute('data-order-url');
        var timer = null;
        var latest = 0;

        var cell = function (row, text, className) {
            var td = document.createElement('td');
            td.textContent = text;           // textContent (not innerHTML) prevents XSS
            if (className) { td.className = className; }
            row.appendChild(td);
            return td;
        };

        var show = function (orders) {
            tbody.innerHTML = '';
            orders.forEach(function (o) {
                var tr = document.createElement('tr');
                cell(tr, '#' + o.order_id);
                cell(tr, o.customer_name);
                var statusTd = cell(tr, '');
                var badge = document.createElement('span');
                badge.className = 'badge badge-' + String(o.status).replace(/[^a-z_]/g, '');
                badge.textContent = o.status_label;
                statusTd.appendChild(badge);
                cell(tr, o.pickup_date);
                cell(tr, o.delivery_date);
                cell(tr, o.total, 'num');
                cell(tr, o.staff_name);
                var linkTd = document.createElement('td');
                var link = document.createElement('a');
                link.href = orderUrl + '?id=' + encodeURIComponent(o.order_id);
                link.textContent = 'View';
                linkTd.appendChild(link);
                tr.appendChild(linkTd);
                tbody.appendChild(tr);
            });
            table.hidden = orders.length === 0;
        };

        var runSearch = function () {
            var q = search.value.trim();
            var id = ++latest;
            if (q === '') {
                show([]);
                msg.textContent = '';
                return;
            }
            msg.textContent = 'Searching...';
            fetch(endpoint + '?q=' + encodeURIComponent(q), {
                headers: { 'Accept': 'application/json' },
                credentials: 'same-origin'
            })
                .then(function (response) {
                    return response.json().then(function (data) { return { ok: response.ok, data: data }; });
                })
                .then(function (result) {
                    if (id !== latest) { return; }       // ignore out-of-date responses
                    if (!result.ok) {
                        show([]);
                        msg.textContent = result.data.error || 'Search failed.';
                        return;
                    }
                    show(result.data.orders);
                    msg.textContent = result.data.orders.length === 0
                        ? 'No matching orders found.'
                        : result.data.orders.length + ' order(s) found.';
                })
                .catch(function () {
                    if (id === latest) { show([]); msg.textContent = 'Could not reach the server. Please try again.'; }
                });
        };

        search.addEventListener('input', function () {
            clearTimeout(timer);
            timer = setTimeout(runSearch, 300);   // wait until the user pauses typing
        });
    }
});
