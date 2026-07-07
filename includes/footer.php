	<footer class="site-footer mt-5">
		<div class="container py-4">
			<div class="row g-3 align-items-center">
				<div class="col-md-8">
					<h6 class="mb-1">TienHa Drinks - Nuoc uong tuoi moi ngay</h6>
					<p class="mb-0 text-light-emphasis">Dia chi: Cho Ben Thanh, Quan 1, TP.HCM | Email: hello@tienhadrinks.vn</p>
				</div>
				<div class="col-md-4 text-md-end">
					<a href="<?= e(app_url('index.php?page=contact')) ?>" class="btn btn-outline-light btn-sm">Lien he hop tac</a>
				</div>
			</div>
		</div>
	</footer>
</div>

<div class="modal fade" id="productQuickViewModal" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header border-0">
				<h5 class="modal-title">Chi tiet san pham</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body" id="productQuickViewBody">
				Dang tai du lieu...
			</div>
			<div class="modal-footer border-0">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Dong</button>
			</div>
		</div>
	</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="<?= e(app_url('assets/js/main.js')) ?>"></script>
</body>
</html>

