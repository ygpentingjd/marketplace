   <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
   <script>
       document.addEventListener('DOMContentLoaded', function() {
           loadCartItems();
       });

       function loadCartItems() {
           const cartItems = JSON.parse(localStorage.getItem('cartItems')) || [];
           const cartContainer = document.getElementById('cartItems');
           cartContainer.innerHTML = '';

           cartItems.forEach((item, index) => {
               const itemHtml = `
                    <div class="cart-item">
                        <div class="row align-items-center">
                            <div class="col-auto">
                                <input class="form-check-input item-checkbox" 
                                       type="checkbox" 
                                       data-price="${item.price}"
                                       data-index="${index}"
                                       onchange="updateTotal()">
                            </div>
                            <div class="col-auto">
                                <img src="${item.image}" alt="${item.name}" class="product-image">
                            </div>
                            <div class="col">
                                <h5 class="mb-1">${item.name}</h5>
                                ${item.size ? `<p class="text-muted mb-1">size: ${item.size}</p>` : ''}
                                ${item.color ? `<p class="text-muted mb-1">color: ${item.color}</p>` : ''}
                                <h6 class="mb-0 price" id="price-${index}">Rp${item.price.toLocaleString('id-ID')}</h6>
                            </div>
                            <div class="col-auto">
                                <button class="favorite-btn me-2">
                                    <i class="far fa-heart"></i>
                                </button>
                                <button class="delete-btn" onclick="removeItem(${index})">
                                    <i class="far fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
               cartContainer.innerHTML += itemHtml;
           });
       }

       function toggleAll(checkbox) {
           const itemCheckboxes = document.querySelectorAll('.item-checkbox');
           itemCheckboxes.forEach(item => {
               item.checked = checkbox.checked;
               togglePrice(item);
           });
           updateTotal();
       }

       function togglePrice(checkbox) {
           const index = checkbox.getAttribute('data-index');
           const priceElement = document.getElementById(`price-${index}`);
           if (checkbox.checked) {
               priceElement.classList.add('show');
           } else {
               priceElement.classList.remove('show');
           }
       }

       function updateTotal() {
           const checkboxes = document.querySelectorAll('.item-checkbox:checked');
           let total = 0;

           checkboxes.forEach(checkbox => {
               const price = parseInt(checkbox.getAttribute('data-price'));
               total += price;
               togglePrice(checkbox);
           });

           document.getElementById('totalPrice').textContent = `Rp${total.toLocaleString('id-ID')}`;
       }

       function removeItem(index) {
           const cartItems = JSON.parse(localStorage.getItem('cartItems')) || [];
           cartItems.splice(index, 1);
           localStorage.setItem('cartItems', JSON.stringify(cartItems));
           loadCartItems();
           updateTotal();
       }

       function checkout() {
           const checkedItems = document.querySelectorAll('.item-checkbox:checked');
           if (checkedItems.length === 0) {
               alert('Silakan pilih produk yang akan dibeli');
               return;
           }
           // Add your checkout logic here
           alert('Proses checkout akan dimulai');
       }
   </script>