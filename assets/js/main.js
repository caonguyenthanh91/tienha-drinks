document.documentElement.classList.add("js");

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
        const addCartButton = p.is_active
            ? `<button class=\"btn btn-success\" onclick=\"addToCart(${p.id}, 1)\">Them vao gio hang</button>`
            : '<button class="btn btn-secondary" type="button" disabled>Tam ngung</button>';

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
                    ${addCartButton}
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
            showToast(json.message || "Không thể thêm giỏ hàng", "error");
            return;
        }

        const badge = document.getElementById("cartCountBadge");
        if (badge) {
            const oldCount = parseInt(badge.textContent) || 0;
            const newCount = json.cart_count || 0;
            badge.textContent = String(newCount);
            animateCartBadge();
        }

        showToast("Đã thêm sản phẩm vào giỏ hàng", "success");
    } catch (error) {
        showToast("Có lỗi xảy ra, vui lòng thử lại", "error");
    }
}

function showToast(message, type = "info") {
    const toastId = "toast-" + Date.now();
    const bgClass = type === "success" ? "bg-success" : type === "error" ? "bg-danger" : "bg-info";

    const toastEl = document.createElement("div");
    toastEl.id = toastId;
    toastEl.className = `toast align-items-center text-white ${bgClass} border-0`;
    toastEl.setAttribute("role", "alert");
    toastEl.setAttribute("aria-live", "assertive");
    toastEl.setAttribute("aria-atomic", "true");
    toastEl.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">${escapeHtml(message)}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;

    if (!document.getElementById("toastContainer")) {
        const container = document.createElement("div");
        container.id = "toastContainer";
        container.className = "toast-container position-fixed bottom-0 end-0 p-3";
        document.body.appendChild(container);
    }

    document.getElementById("toastContainer").appendChild(toastEl);
    const toast = new bootstrap.Toast(toastEl);
    toast.show();

    toastEl.addEventListener("hidden.bs.toast", () => {
        toastEl.remove();
    });
}

function animateCartBadge() {
    const badge = document.getElementById("cartCountBadge");
    if (badge) {
        badge.style.transform = "scale(1.3)";
        setTimeout(() => {
            badge.style.transform = "scale(1)";
        }, 200);
    }
}

// Reveal elements on scroll (progressive enhancement).
document.addEventListener("DOMContentLoaded", () => {
    const revealItems = document.querySelectorAll("[data-reveal]");
    if (!revealItems.length) {
        return;
    }

    if (!("IntersectionObserver" in window)) {
        revealItems.forEach((el) => el.classList.add("is-visible"));
        return;
    }

    const observer = new IntersectionObserver(
        (entries, obs) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add("is-visible");
                    obs.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.12, rootMargin: "0px 0px -40px 0px" }
    );

    revealItems.forEach((el) => observer.observe(el));
});

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
