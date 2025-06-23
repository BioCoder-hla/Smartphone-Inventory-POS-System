/**
 * js/main.js
 * FINAL VERSION with modern SweetAlert2 popups.
 */
document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('product-search')) {
        initializePOS();
    }
});

function initializePOS() {
    let cart = [];
    let searchDebounceTimer;

    const searchInput = document.getElementById('product-search');
    const searchResults = document.getElementById('search-results');
    const cartItemsTableBody = document.getElementById('cart-items');
    const cartTotalAmountSpan = document.getElementById('cart-total-amount');
    const finalizeBtn = document.getElementById('finalize-sale-btn');
    const quickAddContainer = document.querySelector('.pos-quick-add');
    const spinner = document.querySelector('.spinner');

    // --- EVENT LISTENERS ---
    searchInput.addEventListener('input', () => {
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(() => searchProducts(searchInput.value), 300);
    });

    searchResults.addEventListener('click', (e) => {
        const button = e.target.closest('.add-to-cart-btn');
        if (button) {
            const product = JSON.parse(button.dataset.product);
            addToCart(product);
        }
    });

    quickAddContainer.addEventListener('click', (e) => {
        const button = e.target.closest('.product-chip');
        if (button) {
            const product = JSON.parse(button.dataset.product);
            addToCart(product);
        }
    });

    cartItemsTableBody.addEventListener('input', (e) => {
        const quantityInput = e.target.closest('.quantity-input');
        if (quantityInput) {
            const variantId = parseInt(quantityInput.dataset.id, 10);
            const newQty = parseInt(quantityInput.value, 10);
            updateCartQuantity(variantId, newQty);
        }
    });

    cartItemsTableBody.addEventListener('click', (e) => {
        const removeButton = e.target.closest('.remove-item-btn');
        if (removeButton) {
            const variantId = parseInt(removeButton.dataset.id, 10);
            removeFromCart(variantId);
        }
    });

    finalizeBtn.addEventListener('click', finalizeSale);

    // --- FUNCTIONS ---
    async function searchProducts(term) {
        spinner.style.display = 'block';
        searchResults.innerHTML = '';
        searchResults.appendChild(spinner);

        if (term.length < 2) {
            spinner.style.display = 'none';
            searchResults.innerHTML = '<p style="text-align:center; color:#888; padding: 20px;">Enter at least 2 characters</p>';
            return;
        }

        try {
            const response = await fetch(`search_products.php?term=${encodeURIComponent(term)}`);
            const products = await response.json();
            let html = '';
            if (products.length > 0) {
                products.forEach(p => {
                    const productData = JSON.stringify(p);
                    html += `<div class="result-item"><div><strong>${p.brand} ${p.master_name}</strong><br><small>${p.variant_name} - Stock: ${p.quantity}</small></div><button class="add-to-cart-btn" data-product='${productData}'>Add</button></div>`;
                });
            } else {
                html = '<p style="text-align:center; color:#888; padding: 20px;">No products found.</p>';
            }
            searchResults.innerHTML = html;
        } catch (error) {
            console.error('Search Error:', error);
            searchResults.innerHTML = '<p style="text-align:center; color:red; padding: 20px;">Error during search.</p>';
        } finally {
            spinner.style.display = 'none';
        }
    }

    function addToCart(product) {
        const existingItem = cart.find(item => item.id === product.id);
        if (existingItem) {
            if (existingItem.qty < product.quantity) {
                existingItem.qty++;
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Stock Limit Reached',
                    text: 'You cannot add more than the available stock.',
                    confirmButtonText: 'OK',
                    customClass: {
                        popup: 'swal2-popup',
                        title: 'swal2-title',
                        content: 'swal2-content',
                        confirmButton: 'swal2-confirm'
                    },
                    showClass: {
                        popup: 'animate__animated animate__fadeInDown'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__fadeOutUp'
                    }
                });
            }
        } else {
            cart.push({ id: product.id, name: `${product.brand} ${product.master_name}`, variant: product.variant_name, price: product.selling_price, qty: 1, stock: product.quantity });
        }
        updateCartDisplay();
    }

    function removeFromCart(variantId) {
        cart = cart.filter(item => item.id !== variantId);
        updateCartDisplay();
    }

    function updateCartQuantity(variantId, newQty) {
        const item = cart.find(i => i.id === variantId);
        if (item) {
            if (newQty > 0 && newQty <= item.stock) {
                item.qty = newQty;
            } else if (newQty > item.stock) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Stock Limit Exceeded',
                    text: `Only ${item.stock} units are available. Quantity has been reset to max.`,
                    confirmButtonText: 'OK',
                    customClass: {
                        popup: 'swal2-popup',
                        title: 'swal2-title',
                        content: 'swal2-content',
                        confirmButton: 'swal2-confirm'
                    },
                    showClass: {
                        popup: 'animate__animated animate__fadeInDown'
                    },
                    hideClass: {
                        popup: 'animate__animated animate__fadeOutUp'
                    }
                });
                item.qty = item.stock;
            } else {
                removeFromCart(variantId);
            }
        }
        updateCartDisplay();
    }

    function updateCartDisplay() {
        let html = '';
        let total = 0;
        if (cart.length > 0) {
            cart.forEach(item => {
                const itemTotal = item.qty * item.price;
                total += itemTotal;
                html += `<tr><td>${item.name}<br><small>${item.variant}</small></td><td><input type="number" class="form-control quantity-input" data-id="${item.id}" value="${item.qty}" min="1" max="${item.stock}"></td><td>$${itemTotal.toFixed(2)}</td><td><button class="btn-link remove-item-btn" data-id="${item.id}">X</button></td></tr>`;
            });
            finalizeBtn.disabled = false;
        } else {
            html = '<tr><td colspan="4" style="text-align:center;">Cart is empty</td></tr>';
            finalizeBtn.disabled = true;
        }
        cartItemsTableBody.innerHTML = html;
        cartTotalAmountSpan.textContent = total.toFixed(2);
    }

    function finalizeSale() {
        if (cart.length === 0) return;

        Swal.fire({
            title: 'Finalize This Sale?',
            text: 'This action cannot be undone and will update inventory levels.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Complete Sale',
            cancelButtonText: 'Cancel',
            customClass: {
                popup: 'swal2-popup',
                title: 'swal2-title',
                content: 'swal2-content',
                confirmButton: 'swal2-confirm',
                cancelButton: 'swal2-cancel'
            },
            showClass: {
                popup: 'animate__animated animate__fadeInDown'
            },
            hideClass: {
                popup: 'animate__animated animate__fadeOutUp'
            }
        }).then(async (result) => {
            if (result.isConfirmed) {
                finalizeBtn.disabled = true;
                finalizeBtn.textContent = 'Processing...';

                try {
                    const response = await fetch('process_sale.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify(cart)
                    });
                    const result = await response.json();

                    if (result.success) {
                        await Swal.fire({
                            icon: 'success',
                            title: 'Sale Successfully Completed!',
                            text: 'Redirecting to receipt...',
                            confirmButtonText: 'OK',
                            customClass: {
                                popup: 'swal2-popup',
                                title: 'swal2-title',
                                content: 'swal2-content',
                                confirmButton: 'swal2-confirm'
                            },
                            showClass: {
                                popup: 'animate__animated animate__fadeInUp'
                            },
                            hideClass: {
                                popup: 'animate__animated animate__fadeOutDown'
                            },
                            timer: 2000,
                            timerProgressBar: true
                        });
                        window.location.href = `receipt.php?id=${result.sale_id}`;
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Sale Failed',
                            text: result.message,
                            confirmButtonText: 'Try Again',
                            customClass: {
                                popup: 'swal2-popup',
                                title: 'swal2-title',
                                content: 'swal2-content',
                                confirmButton: 'swal2-confirm'
                            },
                            showClass: {
                                popup: 'animate__animated animate__fadeInDown'
                            },
                            hideClass: {
                                popup: 'animate__animated animate__fadeOutUp'
                            }
                        });
                        finalizeBtn.disabled = false;
                        finalizeBtn.textContent = 'Finalize Sale';
                    }
                } catch (error) {
                    console.error('Finalize Sale Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'A Critical Error Occurred',
                        text: 'Could not communicate with the server. Please check your connection.',
                        confirmButtonText: 'Retry',
                        customClass: {
                            popup: 'swal2-popup',
                            title: 'swal2-title',
                            content: 'swal2-content',
                            confirmButton: 'swal2-confirm'
                        },
                        showClass: {
                            popup: 'animate__animated animate__fadeInDown'
                        },
                        hideClass: {
                            popup: 'animate__animated animate__fadeOutUp'
                        }
                    });
                    finalizeBtn.disabled = false;
                    finalizeBtn.textContent = 'Finalize Sale';
                }
            }
        });
    }
}

// Debug function to check SweetAlert2 availability
if (typeof Swal === 'undefined') {
    console.error('SweetAlert2 is not available. Please ensure the library is loaded.');
}