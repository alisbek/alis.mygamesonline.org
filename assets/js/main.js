document.addEventListener('DOMContentLoaded', function() {
    const mobileToggle = document.querySelector('.mobile-menu-toggle');
    const nav = document.querySelector('.nav');
    
    if (mobileToggle && nav) {
        mobileToggle.addEventListener('click', function() {
            nav.classList.toggle('active');
        });
    }
    
    initCart();
    
    initProductOptions();
    
    initFilters();
});

function initCart() {
    const addToCartBtn = document.querySelector('.add-to-cart-btn');
    if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function() {
            const productId = this.dataset.productId;
            const productName = this.dataset.productName;
            const price = parseFloat(this.dataset.price);
            const size = document.querySelector('.size-option.selected')?.dataset.size;
            const color = document.querySelector('.color-option.selected')?.dataset.color;
            const quantity = parseInt(document.querySelector('.quantity-input input')?.value || 1);
            
            if (!size) {
                alert('Please select a size');
                return;
            }
            
            addToCart({
                id: productId,
                name: productName,
                price: price,
                size: size,
                color: color || '',
                quantity: quantity,
                image: this.dataset.image || ''
            });
        });
    }
    
    updateCartCount();
    
    const removeButtons = document.querySelectorAll('.cart-item-remove');
    removeButtons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const index = parseInt(this.dataset.index);
            removeFromCart(index);
            location.reload();
        });
    });
    
    const quantityBtns = document.querySelectorAll('.quantity-btn');
    quantityBtns.forEach(function(btn) {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('input');
            const action = this.dataset.action;
            let value = parseInt(input.value);
            
            if (action === 'decrease' && value > 1) {
                input.value = value - 1;
            } else if (action === 'increase') {
                input.value = value + 1;
            }
        });
    });
}

function getCart() {
    return JSON.parse(localStorage.getItem('feltee_cart') || '[]');
}

function saveCart(cart) {
    localStorage.setItem('feltee_cart', JSON.stringify(cart));
}

function addToCart(item) {
    const cart = getCart();
    const existingIndex = cart.findIndex(function(i) {
        return i.id === item.id && i.size === item.size && i.color === item.color;
    });
    
    if (existingIndex >= 0) {
        cart[existingIndex].quantity += item.quantity;
    } else {
        cart.push(item);
    }
    
    saveCart(cart);
    updateCartCount();
    
    alert('Added to cart!');
}

function removeFromCart(index) {
    const cart = getCart();
    cart.splice(index, 1);
    saveCart(cart);
    updateCartCount();
}

function updateCartCount() {
    const cart = getCart();
    const count = cart.reduce(function(sum, item) {
        return sum + item.quantity;
    }, 0);
    
    const cartCountEl = document.querySelector('.cart-count');
    if (cartCountEl) {
        cartCountEl.textContent = count;
    }
}

function initProductOptions() {
    const sizeOptions = document.querySelectorAll('.size-option');
    sizeOptions.forEach(function(option) {
        option.addEventListener('click', function() {
            sizeOptions.forEach(function(o) {
                o.classList.remove('selected');
            });
            this.classList.add('selected');
        });
    });
    
    const colorOptions = document.querySelectorAll('.color-option');
    colorOptions.forEach(function(option) {
        option.addEventListener('click', function() {
            colorOptions.forEach(function(o) {
                o.classList.remove('selected');
            });
            this.classList.add('selected');
        });
    });
}

function initFilters() {
    const filterSelects = document.querySelectorAll('.filter-group select');
    filterSelects.forEach(function(select) {
        select.addEventListener('change', function() {
            const url = new URL(window.location.href);
            
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