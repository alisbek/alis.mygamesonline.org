document.addEventListener('DOMContentLoaded', function() {
    var mobileToggle = document.querySelector('.mobile-menu-toggle');
    var nav = document.querySelector('.nav');
    
    if (mobileToggle && nav) {
        mobileToggle.addEventListener('click', function() {
            nav.classList.toggle('active');
        });
    }
    
    initCart();
    initFilters();
    initInpostGeowidget();
});

function initCart() {
    var apiUrl = (typeof SITE_URL !== 'undefined' ? SITE_URL : '') + '/cart-api.php';
    
    // Remove buttons on cart page
    var removeButtons = document.querySelectorAll('.cart-item-remove');
    removeButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var index = parseInt(this.dataset.index);
            var formData = new FormData();
            formData.append('action', 'remove');
            formData.append('index', index);
            
            fetch(apiUrl, {
                method: 'POST',
                body: formData
            }).then(function(response) {
                return response.json();
            }).then(function(data) {
                if (data.success) {
                    location.reload();
                }
            });
        });
    });
    
    // Quantity buttons on cart page
    var quantityBtns = document.querySelectorAll('.cart-quantity-btn');
    quantityBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var input = this.parentElement.querySelector('input');
            var action = this.dataset.action;
            var index = parseInt(this.dataset.index);
            var value = parseInt(input.value);
            
            if (action === 'decrease' && value > 1) {
                value = value - 1;
            } else if (action === 'increase') {
                value = value + 1;
            } else {
                return;
            }
            
            input.value = value;
            
            var formData = new FormData();
            formData.append('action', 'update');
            formData.append('index', index);
            formData.append('quantity', value);
            
            fetch(apiUrl, {
                method: 'POST',
                body: formData
            }).then(function(response) {
                return response.json();
            }).then(function(data) {
                if (data.success) {
                    location.reload();
                }
            });
        });
    });
}

function initFilters() {
    var filterSelects = document.querySelectorAll('.filter-group select');
    filterSelects.forEach(function(select) {
        select.addEventListener('change', function() {
            var url = new URL(window.location.href);
            
            filterSelects.forEach(function(s) {
                if (s.value) {
                    url.searchParams.set(s.name, s.value);
                } else {
                    url.searchParams.delete(s.name);
                }
            });
            
            window.location.href = url.toString();
        });
    });
}

function initInpostGeowidget() {
    var deliveryRadios = document.querySelectorAll('input[name="delivery"]');
    var geowidgetContainer = document.getElementById('inpost-geowidget-container');
    var selectedPointDiv = document.getElementById('inpost-selected-point');
    var selectPrompt = document.getElementById('inpost-select-prompt');
    var hiddenPointId = document.getElementById('inpost_point_id');
    var hiddenPointName = document.getElementById('inpost_point_name');
    var summaryShipping = document.getElementById('cart-summary-shipping-value');
    var summaryTotal = document.getElementById('cart-summary-grand-total');
    var subtotalValue = document.getElementById('cart-summary-subtotal-value');
    
    if (!geowidgetContainer || deliveryRadios.length === 0) return;
    
    var shippingCost = parseFloat(geowidgetContainer.dataset.shippingCost || '12.99');
    var subtotal = parseFloat(subtotalValue ? subtotalValue.dataset.value : '0');
    var freeLabel = geowidgetContainer.dataset.freeLabel || 'Free';
    
    function updateShippingDisplay(delivery) {
        if (!summaryShipping || !summaryTotal) return;
        if (delivery === 'inpost') {
            summaryShipping.textContent = shippingCost.toFixed(2) + ' zł';
            summaryShipping.className = 'shipping-cost';
            summaryTotal.textContent = (subtotal + shippingCost).toFixed(2) + ' zł';
        } else {
            summaryShipping.textContent = freeLabel;
            summaryShipping.className = 'shipping-free';
            summaryTotal.textContent = subtotal.toFixed(2) + ' zł';
        }
    }
    
    function showGeowidget(show) {
        if (show) {
            geowidgetContainer.classList.add('active');
            if (selectPrompt && !hiddenPointId.value) {
                selectPrompt.classList.add('active');
            }
        } else {
            geowidgetContainer.classList.remove('active');
            if (selectPrompt) selectPrompt.classList.remove('active');
        }
    }
    
    deliveryRadios.forEach(function(radio) {
        radio.addEventListener('change', function() {
            var isInpost = this.value === 'inpost';
            showGeowidget(isInpost);
            updateShippingDisplay(this.value);
        });
        
        // Initialize on page load if already checked
        if (radio.checked && radio.value === 'inpost') {
            showGeowidget(true);
            updateShippingDisplay('inpost');
        }
    });
    
    // Initialize shipping display for currently selected delivery
    var checkedRadio = document.querySelector('input[name="delivery"]:checked');
    if (checkedRadio) {
        updateShippingDisplay(checkedRadio.value);
    }
    
    // Listen for Geowidget point selection
    var geowidgetEl = document.querySelector('inpost-geowidget');
    if (geowidgetEl) {
        geowidgetEl.addEventListener('onpoint', function(e) {
            var point = e.detail;
            if (!point) return;
            
            hiddenPointId.value = point.name;
            hiddenPointName.value = point.name + ' - ' + (point.address ? (point.address.line1 || '') + ', ' + (point.address.line2 || '') : '');
            
            // Show selected point
            if (selectedPointDiv) {
                var pointNameSpan = selectedPointDiv.querySelector('.inpost-point-name');
                if (pointNameSpan) {
                    pointNameSpan.textContent = hiddenPointName.value;
                }
                selectedPointDiv.classList.add('active');
            }
            
            // Hide prompt
            if (selectPrompt) {
                selectPrompt.classList.remove('active');
            }
        });
    }
    
    // Change button to reopen geowidget
    var changeBtn = document.getElementById('inpost-change-btn');
    if (changeBtn) {
        changeBtn.addEventListener('click', function() {
            selectedPointDiv.classList.remove('active');
            geowidgetContainer.classList.add('active');
            if (selectPrompt) selectPrompt.classList.add('active');
        });
    }
}
