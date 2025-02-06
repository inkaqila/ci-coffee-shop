<div class="container mt-4">
  <h1 class="text-center">Daftar Menu</h1>
  <div class="row">
    <?php if (!empty($menu)): ?>
      <?php foreach ($menu as $m): ?>
        <div class="col-md-3">
          <div class="card mb-4">
		  <img src="<?= base_url('assets/img/menu/' . $m['gambar']); ?>" class="card-img-top" alt="<?= $m['nama']; ?>">
            <div class="card-body">
              <h5 class="card-title"><?= $m['nama']; ?></h5>
              <p class="card-text">Rp<?= number_format($m['harga'], 0, ',', '.'); ?></p>
              <a href="<?= base_url('menu/pesan/' . $m['id']); ?>" class="btn btn-primary">Pesan</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="text-center">Belum ada menu tersedia.</p>
    <?php endif; ?>
  </div>
</div>
