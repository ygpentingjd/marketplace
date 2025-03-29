function formatRupiah(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    }).format(amount);
}

function calculateInstallment(price, months) {
    const adminFee = price * 0.02; // 2% admin fee
    let interestRate;
    
    // Progressive interest rates
    switch(months) {
        case 3:
            interestRate = 0.03; // 3%
            break;
        case 6:
            interestRate = 0.05; // 5%
            break;
        case 12:
            interestRate = 0.08; // 8%
            break;
        default:
            interestRate = 0;
    }

    const interest = price * interestRate;
    const totalPrice = price + adminFee + interest;
    const monthlyPayment = Math.ceil(totalPrice / months);

    return {
        adminFee: adminFee,
        interest: interest,
        totalPrice: totalPrice,
        monthlyPayment: monthlyPayment,
        interestRate: interestRate * 100
    };
}

function addToCart(item) {
    const cartItems = JSON.parse(localStorage.getItem('cart')) || [];
    const creditOption = document.querySelector('input[name="credit_option"]:checked');
    
    if (!creditOption) {
        alert('Silakan pilih metode pembayaran');
        return false;
    }

    const paymentMethod = creditOption.value;
    item.paymentMethod = paymentMethod;

    if (paymentMethod !== 'cash') {
        const months = parseInt(paymentMethod);
        item.installment = calculateInstallment(item.price, months);
    }

    cartItems.push(item);
    localStorage.setItem('cart', JSON.stringify(cartItems));
    return true;
}

function showCreditOptions(price, productName) {
    if (price < 500000) {
        return '<div class="alert alert-info">Cicilan tidak tersedia untuk produk di bawah Rp 500.000</div>';
    }

    const options = [
        { months: 'cash', label: 'Bayar Langsung' },
        { months: 3, label: 'Cicilan 3 Bulan' },
        { months: 6, label: 'Cicilan 6 Bulan' },
        { months: 12, label: 'Cicilan 12 Bulan' }
    ];

    let html = `
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> Informasi Cicilan:
            <ul class="mb-0 mt-2">
                <li>Biaya admin 2% dari harga produk</li>
                <li>Bunga cicilan:
                    <ul>
                        <li>3 bulan: 3%</li>
                        <li>6 bulan: 5%</li>
                        <li>12 bulan: 8%</li>
                    </ul>
                </li>
            </ul>
        </div>
        <div class="credit-options card">
            <div class="card-header">
                <h5 class="mb-0">Pilihan Pembayaran</h5>
            </div>
            <div class="card-body">
    `;

    options.forEach(option => {
        const checked = option.months === 'cash' ? 'checked' : '';
        let priceInfo = '';
        
        if (option.months !== 'cash') {
            const calculation = calculateInstallment(price, option.months);
            priceInfo = `
                <div class="ms-4 mt-1">
                    <div class="text-muted small">
                        <div>Cicilan: ${formatRupiah(calculation.monthlyPayment)}/bulan</div>
                        <div>Biaya Admin: ${formatRupiah(calculation.adminFee)}</div>
                        <div>Bunga: ${calculation.interestRate}% (${formatRupiah(calculation.interest)})</div>
                        <div class="text-primary">Total: ${formatRupiah(calculation.totalPrice)}</div>
                    </div>
                </div>`;
        }

        html += `
            <div class="form-check credit-option mb-3">
                <input class="form-check-input" type="radio" name="credit_option" 
                       value="${option.months}" id="credit_${option.months}" ${checked}
                       onchange="updatePaymentSummary('${productName}', ${price}, this.value)">
                <label class="form-check-label" for="credit_${option.months}">
                    <div class="d-flex align-items-center">
                        <span class="me-2">${option.label}</span>
                        ${option.months === 'cash' ? 
                            `<span class="badge bg-primary">${formatRupiah(price)}</span>` : 
                            ''}
                    </div>
                    ${priceInfo}
                </label>
            </div>
        `;
    });

    html += `
            </div>
        </div>
        <div id="paymentSummary" class="mt-3"></div>
    `;
    return html;
}

function updatePaymentSummary(productName, price, selectedOption) {
    const summaryDiv = document.getElementById('paymentSummary');
    if (selectedOption === 'cash') {
        summaryDiv.innerHTML = `
            <div class="alert alert-success">
                <h6 class="mb-2">Ringkasan Pembayaran:</h6>
                <div>${productName}</div>
                <div class="fw-bold">${formatRupiah(price)}</div>
            </div>
        `;
    } else {
        const months = parseInt(selectedOption);
        const calculation = calculateInstallment(price, months);
        summaryDiv.innerHTML = `
            <div class="alert alert-success">
                <h6 class="mb-2">Ringkasan Cicilan ${months} Bulan:</h6>
                <div>${productName}</div>
                <div>Harga Produk: ${formatRupiah(price)}</div>
                <div>Biaya Admin (2%): ${formatRupiah(calculation.adminFee)}</div>
                <div>Bunga (${calculation.interestRate}%): ${formatRupiah(calculation.interest)}</div>
                <div class="fw-bold">Cicilan per Bulan: ${formatRupiah(calculation.monthlyPayment)}</div>
                <div class="fw-bold">Total Pembayaran: ${formatRupiah(calculation.totalPrice)}</div>
            </div>
        `;
    }
} 