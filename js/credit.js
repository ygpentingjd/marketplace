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
    const monthlyPayment = totalPrice / months;

    return {
        adminFee: adminFee,
        interest: interest,
        totalPrice: totalPrice,
        monthlyPayment: monthlyPayment
    };
}

function showCreditOptions(price) {
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
            Pembayaran cicilan dikenakan biaya admin 2%
        </div>
        <div class="credit-options" style="border: 1px solid #ddd; padding: 15px; border-radius: 5px;">
            <h4>Pilihan Pembayaran:</h4>
    `;

    options.forEach(option => {
        const checked = option.months === 'cash' ? 'checked' : '';
        let priceInfo = '';
        
        if (option.months !== 'cash') {
            const calculation = calculateInstallment(price, option.months);
            priceInfo = `<br><small>Cicilan: ${formatRupiah(calculation.monthlyPayment)}/bulan</small>
                        <br><small>Total: ${formatRupiah(calculation.totalPrice)}</small>`;
        }

        html += `
            <div class="form-check mb-2">
                <input class="form-check-input" type="radio" name="credit_option" 
                       value="${option.months}" id="credit_${option.months}" ${checked}>
                <label class="form-check-label" for="credit_${option.months}">
                    ${option.label}${priceInfo}
                </label>
            </div>
        `;
    });

    html += '</div>';
    return html;
} 