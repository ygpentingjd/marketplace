<?php include 'hf/header.php'; ?>

<div class="container" style="max-width: 1200px; margin: auto; padding: 20px; border-radius: 10px;">
    <div class="product-detail" style="display: flex; gap: 20px;">
        <div class="product-image" style="flex: 1;">
            <img src="image/sepatu.png" alt="Onitsuka Tiger TOKUTEN Black/White" style="width: 100%; border-radius: 10px;">
        </div>
        <div class="product-info" style="flex: 2;">
            <h2>Onitsuka Tiger TOKUTEN Black/White</h2>
            <span class="tag" style="background: lightgreen; padding: 5px; border-radius: 5px;">Tag</span>
            <h3>Rp 250.000</h3>
            <br>
            <div class="detail-options">
                <label for="warna">Warna</label>
                <select id="warna">
                    <option>Black</option>
                </select>
                <label for="ukuran">Ukuran</label>
                <select id="ukuran">
                    <option>40</option>
                </select>
            </div>
            <br>
            <button style="width: 100%; padding: 10px; background: black; color: white; border: none; border-radius: 5px; cursor: pointer;">Checkout</button>
            <br><br>
            <div class="description" style="border: 1px solid #ddd; padding: 10px; border-radius: 5px;">
                <strong>Deskripsi</strong>
                <p>Onitsuka Tiger TOKUTEN<br>Condition: 80% (Original)</p>
            </div>
        </div>
    </div>
    <br>
    <h3>Riview terbaru</h3>
    <div class="reviews" style="display: flex; gap: 20px;">
        <div class="review-card" style="flex: 1; border: 1px solid #ddd; padding: 10px; border-radius: 10px;">
            <p>⭐⭐⭐⭐⭐</p>
            <p>Produk sangat bagus dan ori</p>
            <small>White/40</small>
            <br><strong>Kintan</strong>
            <br><small>02-01-2022</small>
        </div>
        <div class="review-card" style="flex: 1; border: 1px solid #ddd; padding: 10px; border-radius: 10px;">
            <p>⭐⭐⭐⭐</p>
            <p>Pengiriman cepat dan murah</p>
            <small>White/40</small>
            <br><strong>Lisa Blekping</strong>
            <br><small>17-02-2022</small>
        </div>
        <div class="review-card" style="flex: 1; border: 1px solid #ddd; padding: 10px; border-radius: 10px;">
            <p>⭐⭐⭐⭐⭐</p>
            <p>Ori dan kualitas bagus</p>
            <small>Black/40</small>
            <br><strong>Agnez Mo</strong>
            <br><small>20-01-2025</small>
        </div>
    </div>
    <br>
    <div style="text-align: center;">
        <h3>Preloved By Ocaa</h3>
        <p>Kota Surakarta</p>
        <button style="padding: 5px 10px; background: black; color: white; border-radius: 5px; cursor: pointer;">Kunjungi</button>
    </div>
</div>

<?php include 'hf/footer.php'; ?>