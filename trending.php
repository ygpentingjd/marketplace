<?php include 'hf/header.php'; ?>

<style>
    .container {
        width: 90%;
        margin: auto;
    }

    .section {
        background: #F5F5F5;
        padding: 20px;
        margin-bottom: 20px;
    }

    .section h2 {
        font-size: 20px;
        font-weight: bold;
    }

    .products {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        justify-content: space-between;
    }

    .product-card {
        background: white;
        padding: 10px;
        width: 18%;
        box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
        text-align: center;
        border-radius: 15px;
    }

    .product-card img {
        max-width: 100%;
        border-radius: 15px;
    }

    .product-card p {
        margin: 5px 0;
    }

    .tag {
        background: lightgreen;
        color: black;
        padding: 2px 8px;
        border-radius: 3px;
        font-size: 12px;
        display: inline-block;
        margin-bottom: 5px;
    }
</style>

<div class="container">
    <div class="section">
        <h2>Suggested For You</h2>
        <div class="products">
            <?php for ($i = 0; $i < 2; $i++) { ?>
                <div class="product-card">
                    <a href="detailsepatu.php"><img src="image/sepatu.png" alt="Product"></a>
                    <p>Onitsuka Tiger TOKUTEN Black/White</p>
                    <span class="tag">Tag</span>
                    <p><strong>Rp 250.000</strong></p>
                </div>
                <div class="product-card">
                    <a href="detailknalpot.php"><img src="image/knalpot.png" alt="Product"></a>
                    <p>Kenalpot DBS Ninja</p>
                    <span class="tag">Tag</span>
                    <p><strong>Rp 2.830.000</strong></p>
                </div>
                <div class="product-card">
                    <a href="detailtv.php"><img src="image/tv.png" alt="Product"></a>
                    <p>Samsung TV </p>
                    <span class="tag">Tag</span>
                    <p><strong>Rp 4.500.000</strong></p>
                </div>
            <?php } ?>
        </div>
    </div>

    <div class="section">
        <h2>Trending</h2>
        <div class="products">
            <?php for ($i = 0; $i < 2; $i++) { ?>
                <div class="product-card">
                    <a href="detailsepatu.php"><img src="image/sepatu.png" alt="Product"></a>
                    <p>Onitsuka Tiger TOKUTEN Black/White</p>
                    <span class="tag">Tag</span>
                    <p><strong>Rp 250.000</strong></p>
                </div>
                <div class="product-card">
                    <a href="detailknalpot.php"><img src="image/knalpot.png" alt="Product"></a>
                    <p>Kenalpot DBS Ninja</p>
                    <span class="tag">Tag</span>
                    <p><strong>Rp 2.830.000</strong></p>
                </div>
                <div class="product-card">
                    <a href="detailtv.php"><img src="image/tv.png" alt="Product"></a>
                    <p>Samsung TV 43 Inch</p>
                    <span class="tag">Tag</span>
                    <p><strong>Rp 4.500.000</strong></p>
                </div>
            <?php } ?>
        </div>
    </div>
</div>

<?php include 'hf/footer.php'; ?>