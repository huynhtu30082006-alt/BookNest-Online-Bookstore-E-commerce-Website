/**
 * BookNest customer-side AJAX:
 *  - add to cart (any page)        -> updates the navbar badge
 *  - cart quantity update / remove -> updates line totals and the price summary
 *  - coupon apply / remove         -> updates discount, delivery fee and total
 * All forms also work without JavaScript (the controllers fall back to redirects).
 */
(function () {
    'use strict';

    var csrf = document.querySelector('meta[name="csrf-token"]');
    var token = csrf ? csrf.getAttribute('content') : '';

    function toast(message, type) {
        var area = document.getElementById('toast-area');
        if (!area) { return; }
        var el = document.createElement('div');
        el.className = 'alert alert-' + (type || 'success') + ' shadow-sm py-2 mb-2';
        el.setAttribute('role', 'alert');
        el.textContent = message;
        area.appendChild(el);
        setTimeout(function () { el.remove(); }, 3500);
    }

    function send(form, overrides) {
        var body = new FormData(form);
        Object.keys(overrides || {}).forEach(function (k) { body.set(k, overrides[k]); });
        return fetch(form.action, {
            method: 'POST', // Laravel reads _method (PATCH / DELETE) from the form data
            headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: body,
            credentials: 'same-origin'
        }).then(function (res) {
            return res.json().catch(function () { return {}; }).then(function (data) {
                if (!res.ok) {
                    var msg = data.message || 'Something went wrong. Please try again.';
                    if (data.errors) { msg = Object.values(data.errors)[0][0]; }
                    var err = new Error(msg);
                    throw err;
                }
                return data;
            });
        });
    }

    function setText(selector, value) {
        document.querySelectorAll(selector).forEach(function (el) { el.textContent = value; });
    }

    function toggle(selector, show) {
        document.querySelectorAll(selector).forEach(function (el) { el.classList.toggle('d-none', !show); });
    }

    /** Refresh every price element from the server's formatted summary payload. */
    function applySummary(data) {
        var badge = document.getElementById('cart-count');
        if (badge && typeof data.count !== 'undefined') { badge.textContent = data.count; }
        if (typeof data.subtotal === 'undefined') { return; }

        ['subtotal', 'discount', 'delivery_fee', 'total'].forEach(function (k) {
            setText('[data-summary="' + k + '"]', data[k]);
        });
        toggle('[data-summary-row="discount"]', data.has_discount);
        setText('[data-summary="coupon_code_label"]', data.coupon_code ? '(' + data.coupon_code + ')' : '');
        setText('[data-summary="coupon_code"]', data.coupon_code || '');

        document.querySelectorAll('[data-coupon-form]').forEach(function (f) { f.classList.toggle('d-none', !!data.coupon_code); });
        toggle('[data-coupon-applied]', !!data.coupon_code);

        if (data.lines) {
            document.querySelectorAll('[data-line]').forEach(function (row) {
                var line = data.lines[row.getAttribute('data-line')];
                if (!line) { row.remove(); return; }
                var qty = row.querySelector('input[name="quantity"]');
                if (qty) { qty.value = line.quantity; }
                setText('[data-line="' + row.getAttribute('data-line') + '"] [data-line-field="unit_price"]', line.unit_price);
                setText('[data-line="' + row.getAttribute('data-line') + '"] [data-line-field="line_total"]', line.line_total);
            });
            if (data.count === 0 && document.getElementById('cart-content')) {
                document.getElementById('cart-content').remove();
                var empty = document.getElementById('cart-empty');
                if (empty) { empty.classList.remove('d-none'); }
            }
        }
        if (data.coupon_notice) { toast(data.coupon_notice, 'warning'); }
    }

    // --- Add to cart -------------------------------------------------------
    document.addEventListener('submit', function (e) {
        var form = e.target;

        if (form.matches('[data-add-to-cart]')) {
            e.preventDefault();
            var btn = form.querySelector('button[type="submit"]');
            btn.disabled = true;
            send(form).then(function (data) {
                applySummary(data);
                toast(data.message, 'success');
            }).catch(function (err) {
                toast(err.message, 'danger');
            }).then(function () { btn.disabled = false; });
        }

        // --- Update quantity -------------------------------------------------
        if (form.matches('[data-qty-form]')) {
            e.preventDefault();
            var input = form.querySelector('input[name="quantity"]');
            var previous = input.defaultValue;
            send(form).then(function (data) {
                input.defaultValue = input.value;
                applySummary(data);
                toast(data.message, 'success');
            }).catch(function (err) {
                input.value = previous;
                toast(err.message, 'danger');
            });
        }

        // --- Remove item -----------------------------------------------------
        if (form.matches('[data-remove-form]')) {
            e.preventDefault();
            send(form).then(function (data) {
                applySummary(data);
                toast(data.message, 'success');
            }).catch(function (err) { toast(err.message, 'danger'); });
        }

        // --- Coupon apply / remove --------------------------------------------
        if (form.matches('[data-coupon-form]') || form.matches('[data-coupon-remove]')) {
            e.preventDefault();
            var box = form.closest('[data-coupon-box]');
            var errorEl = box.querySelector('[data-coupon-error]');
            errorEl.classList.add('d-none');
            send(form).then(function (data) {
                applySummary(data);
                var codeInput = box.querySelector('input[name="code"]');
                if (codeInput) { codeInput.value = ''; }
                toast(data.message, 'success');
            }).catch(function (err) {
                errorEl.textContent = err.message;
                errorEl.classList.remove('d-none');
            });
        }

        // Prevent double submission of the order.
        if (form.id === 'checkout-form') {
            var place = document.getElementById('place-order-btn');
            if (place) { setTimeout(function () { place.disabled = true; place.textContent = 'Placing order…'; }, 0); }
        }
    });
})();
