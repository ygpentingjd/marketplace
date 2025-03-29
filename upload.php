<link href="style.css" rel="stylesheet">
<?php include 'hf/style.php'; ?>
<?php include 'hf/header.php'; ?>

<div class="container mt-4">
    <h4>Tambahkan Produk</h4>

    <div class="card mt-3">
        <div class="card-body">
            <form method="POST" action="" enctype="multipart/form-data">
                <h5 class="mb-4">Informasi produk</h5>

                <div class="mb-3 row">
                    <label class="col-sm-2 col-form-label">Nama produk</label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="nama_produk" placeholder="Masukkan nama produk">
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-sm-2 col-form-label">Foto produk</label>
                    <div class="col-sm-10">
                        <div class="row" id="imagePreviewContainer">
                            <div class="col-md-3">
                                <div class="border rounded p-2 text-center mb-3">
                                    <img src="image/sepatu.png" class="img-fluid mb-2" style="max-height: 150px;">
                                    <button type="button" class="btn btn-sm btn-danger w-100">Hapus</button>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="border rounded p-2 text-center mb-3 upload-box" style="height: 150px; cursor: pointer;" onclick="document.getElementById('imageUpload').click()">
                                    <div class="d-flex align-items-center justify-content-center h-100">
                                        <div>
                                            <i class="fas fa-camera fa-2x mb-2"></i>
                                            <div>Tambahkan</div>
                                            <div><span id="imageCount">1</span>/5</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="file" id="imageUpload" name="product_images[]" accept="image/*" multiple style="display: none;" onchange="handleImageUpload(this)">
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-sm-2 col-form-label">Deskripsi produk</label>
                    <div class="col-sm-10">
                        <textarea class="form-control" name="deskripsi" rows="4"></textarea>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-sm-2 col-form-label">Kategori</label>
                    <div class="col-sm-10">
                        <select class="form-select" name="kategori">
                            <option value="Fashion">Fashion</option>
                            <option value="Otomotif">Otomotif</option>
                            <option value="Elektronik">Elektronik</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-sm-2 col-form-label">Harga produk</label>
                    <div class="col-sm-10">
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control" name="harga">
                        </div>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <a href="account.php"><button type="button" class="btn btn-light me-2">Kembali</button></a>
                    <button type="button" class="btn btn-secondary me-2">Simpan & siapkan</button>
                    <button type="submit" class="btn btn-dark">Tambahkan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    let imageCount = 1; // Starting with 1 for the existing image

    function handleImageUpload(input) {
        const maxImages = 5;
        const container = document.getElementById('imagePreviewContainer');
        const uploadBox = container.querySelector('.upload-box').parentElement;
        const files = input.files;

        for (let i = 0; i < files.length && imageCount < maxImages; i++) {
            const file = files[i];
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                const newCol = document.createElement('div');
                newCol.className = 'col-md-3';

                reader.onload = function(e) {
                    newCol.innerHTML = `
                    <div class="border rounded p-2 text-center mb-3">
                        <img src="${e.target.result}" class="img-fluid mb-2" style="max-height: 150px;">
                        <button type="button" class="btn btn-sm btn-danger w-100" onclick="removeImage(this)">Hapus</button>
                    </div>
                `;
                    container.insertBefore(newCol, uploadBox);
                    imageCount++;
                    updateImageCount();
                };

                reader.readAsDataURL(file);
            }
        }

        // Reset input to allow selecting the same file again
        input.value = '';
    }

    function removeImage(button) {
        button.closest('.col-md-3').remove();
        imageCount--;
        updateImageCount();
    }

    function updateImageCount() {
        document.getElementById('imageCount').textContent = imageCount;
        const uploadBox = document.querySelector('.upload-box').parentElement;
        uploadBox.style.display = imageCount >= 5 ? 'none' : 'block';
    }
</script>

<?php include 'hf/footer.php'; ?>