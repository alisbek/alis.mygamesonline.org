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
