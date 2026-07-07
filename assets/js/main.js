async function openQuickView(productId) {
    const modalEl = document.getElementById("productQuickViewModal");
    const body = document.getElementById("productQuickViewBody");
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

    body.innerHTML = "Dang tai du lieu...";
    modal.show();

    try {
        const response = await fetch(`config/api.php?action=product&id=${productId}`);
        const json = await response.json();

        if (!json.success) {
            body.innerHTML = `<div class=\"alert alert-warning mb-0\">${json.message || "Khong the tai du lieu"}</div>`;
            return;
        }

        const p = json.data;
        const oldPrice = p.sale_price ? `<span class=\"old-price\">${formatVnd(p.price)}</span>` : "";

        body.innerHTML = `
            <div class="row g-3 align-items-center">
                <div class="col-md-5">
                    <img src="${escapeHtml(p.thumbnail)}" alt="${escapeHtml(p.name)}" class="img-fluid rounded-3 w-100" />
                </div>
                <div class="col-md-7">
                    <small class="text-success fw-semibold">${escapeHtml(p.category_name)}</small>
                    <h4 class="mt-1">${escapeHtml(p.name)}</h4>
                    <div class="price-wrap mb-3">${oldPrice}<span class="new-price">${formatVnd(p.effective_price)}</span></div>
                    <p class="text-muted">${escapeHtml(p.description || "Dang cap nhat mo ta...")}</p>
                    <button class="btn btn-success" onclick="addToCart(${p.id}, 1)">Them vao gio hang</button>
                </div>
            </div>
        `;
    } catch (error) {
        body.innerHTML = '<div class="alert alert-danger mb-0">Co loi xay ra khi tai san pham.</div>';
    }
}

async function addToCart(productId, quantity) {
    try {
        const response = await fetch("config/api.php?action=add_to_cart", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                product_id: productId,
                quantity: quantity,
            }),
        });

        const json = await response.json();
        if (!json.success) {
            alert(json.message || "Khong the them gio hang");
            return;
        }

        const badge = document.getElementById("cartCountBadge");
        if (badge) {
            badge.textContent = String(json.cart_count || 0);
        }

        alert("Da them san pham vao gio hang.");
    } catch (error) {
        alert("Co loi xay ra, vui long thu lai.");
    }
}

function formatVnd(value) {
    return new Intl.NumberFormat("vi-VN").format(Number(value || 0)) + " VND";
}

function escapeHtml(value) {
    return String(value || "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/\"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
