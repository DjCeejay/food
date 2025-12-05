// Mobile nav
const navToggle = document.getElementById("navToggle");
const nav = document.querySelector(".af-nav");
if (navToggle && nav) {
  navToggle.addEventListener("click", () => {
    nav.classList.toggle("af-nav-open");
  });
  nav.querySelectorAll("a").forEach((link) => {
    link.addEventListener("click", () => nav.classList.remove("af-nav-open"));
  });
}

// Year in footer
document.getElementById("year").textContent = new Date().getFullYear();

// Simple cart
let cart = [];
const cartCountEl = document.getElementById("cartCount");
const cartFab = document.getElementById("cartFab");
const cartOverlay = document.getElementById("cartOverlay");
const cartOverlayClose = document.getElementById("cartOverlayClose");
const cartOverlayBackdrop = document.getElementById("cartOverlayBackdrop");
const featuredGrid = document.getElementById("featuredGrid");
const menuGrid = document.getElementById("menuGrid");
const menuFilters = document.getElementById("menuFilters");

if (cartFab) {
  cartFab.addEventListener("click", () => {
    openCartOverlay();
  });
}

if (cartOverlayClose) {
  cartOverlayClose.addEventListener("click", closeCartOverlay);
}

if (cartOverlayBackdrop) {
  cartOverlayBackdrop.addEventListener("click", closeCartOverlay);
}

function openCartOverlay() {
  if (!cartOverlay) return;
  cartOverlay.classList.add("af-open");
  document.body.classList.add("af-modal-open");
}

function closeCartOverlay() {
  if (!cartOverlay) return;
  cartOverlay.classList.remove("af-open");
  document.body.classList.remove("af-modal-open");
}

function bumpCartFab() {
  if (!cartFab) return;
  cartFab.classList.remove("af-cart-fab-bump");
  // force reflow for retrigger
  void cartFab.offsetWidth;
  cartFab.classList.add("af-cart-fab-bump");
}

function updateCartCount() {
  if (!cartCountEl) return;
  const count = cart.reduce((sum, item) => sum + item.qty, 0);
  cartCountEl.textContent = count;
  bumpCartFab();
}

function getCartTotal() {
  return cart.reduce((sum, item) => sum + item.price * item.qty, 0);
}

function flyToCart(sourceEl) {
  if (!cartFab) return;
  const targetRect = cartFab.getBoundingClientRect();
  const sourceImg =
    sourceEl.closest("article")?.querySelector("img") || sourceEl;
  const sourceRect = sourceImg.getBoundingClientRect();

  const dot = document.createElement("span");
  dot.className = "af-fly-item";
  dot.style.left = `${sourceRect.left + sourceRect.width / 2}px`;
  dot.style.top = `${sourceRect.top + sourceRect.height / 2}px`;
  document.body.appendChild(dot);

  const deltaX =
    targetRect.left +
    targetRect.width / 2 -
    (sourceRect.left + sourceRect.width / 2);
  const deltaY =
    targetRect.top +
    targetRect.height / 2 -
    (sourceRect.top + sourceRect.height / 2);

  if (dot.animate) {
    const animation = dot.animate(
      [
        { transform: "translate(0, 0) scale(1)", opacity: 0.95 },
        { transform: `translate(${deltaX}px, ${deltaY}px) scale(0.35)`, opacity: 0 }
      ],
      { duration: 600, easing: "ease-in-out" }
    );
    animation.onfinish = () => dot.remove();
  } else {
    dot.remove();
  }
}

function renderCart() {
  const contexts = [
    {
      list: document.getElementById("cartList"),
      totalEl: document.getElementById("cartTotal")
    },
    {
      list: document.getElementById("cartListOverlay"),
      totalEl: document.getElementById("cartTotalOverlay")
    }
  ];

  const total = getCartTotal();

  contexts.forEach(({ list, totalEl }) => {
    if (!list || !totalEl) return;
    list.innerHTML = "";

    cart.forEach((item, index) => {
      const li = document.createElement("li");
      li.className = "af-cart-item";

      li.innerHTML = `
        <div class="af-cart-item-info">
          <span class="af-cart-item-name">${item.name}</span>
          <span class="af-cart-item-meta">₦${item.price.toLocaleString()} × ${item.qty}</span>
        </div>
        <div class="af-cart-actions">
          <button class="af-qty-btn" data-action="dec" data-index="${index}">-</button>
          <button class="af-qty-btn" data-action="inc" data-index="${index}">+</button>
          <button class="af-qty-btn" data-action="remove" data-index="${index}">×</button>
        </div>
      `;
      list.appendChild(li);
    });

    totalEl.textContent = "₦" + total.toLocaleString();
  });

  updateCartCount();
}

function addToCart(item) {
  if (!item?.id) {
    alert("Missing menu item ID; please refresh and try again.");
    return;
  }
  const name = item.name;
  const existing = cart.find((i) => i.name === name);
  if (existing) {
    existing.qty += 1;
  } else {
    cart.push({ id: item.id, name, price: item.price || 0, qty: 1 });
  }
  renderCart();
}

function bindAddToCartButtons() {
  document.querySelectorAll("[data-item]").forEach((btn) => {
    if (btn.dataset.bound === "1") return;
    btn.dataset.bound = "1";
    btn.addEventListener("click", () => {
      const name = btn.getAttribute("data-item");
      const id = parseInt(btn.getAttribute("data-item-id"), 10);
      const soldOut = btn.getAttribute("data-sold-out") === "1";
      if (soldOut) {
        alert("Sorry, this item is sold out.");
        return;
      }
      const priceEl = btn.closest("article")?.querySelector(".af-price");
      const priceAttr = btn.getAttribute("data-item-price");
      const price = priceAttr
        ? parseFloat(priceAttr)
        : priceEl
          ? parseInt(priceEl.textContent.replace(/[^\d]/g, ""), 10)
          : 0;
      addToCart({ id, name, price: isNaN(price) ? 0 : price });
      flyToCart(btn);
    });
  });
}

// Cart quantity buttons
["cartList", "cartListOverlay"].forEach((listId) => {
  const listEl = document.getElementById(listId);
  if (!listEl) return;
  listEl.addEventListener("click", (e) => {
    const btn = e.target.closest(".af-qty-btn");
    if (!btn) return;

    const index = parseInt(btn.getAttribute("data-index"), 10);
    const action = btn.getAttribute("data-action");
    const item = cart[index];
    if (!item) return;

    if (action === "inc") item.qty += 1;
    if (action === "dec") item.qty = Math.max(1, item.qty - 1);
    if (action === "remove") cart.splice(index, 1);

    renderCart();
  });
});

// Initialize displayed count
updateCartCount();

// Dynamic menu loading
const slugify = (text) =>
  (text || "menu")
    .toString()
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, "-")
    .replace(/^-+|-+$/g, "") || "menu";

let activeFilter = "all";

function bindFilterButtons() {
  const chips = menuFilters
    ? menuFilters.querySelectorAll(".af-chip")
    : document.querySelectorAll(".af-menu-filters .af-chip");

  chips.forEach((chipBtn) => {
    chipBtn.addEventListener("click", () => {
      activeFilter = chipBtn.getAttribute("data-filter") || "all";
      chips.forEach((c) => c.classList.remove("af-chip-active"));
      chipBtn.classList.add("af-chip-active");
      applyFilter();
    });
  });
}

function renderFilters(categories) {
  if (!menuFilters) return;
  const chips = [
    { slug: "all", name: "All", active: true },
    ...categories.map((c) => ({
      slug: slugify(c.name),
      name: c.name,
      active: false
    }))
  ];

  menuFilters.innerHTML = chips
    .map(
      (chip) => `
        <button class="af-chip ${chip.active ? "af-chip-active" : ""}" data-filter="${chip.slug}">
          ${chip.name}
        </button>
      `
    )
    .join("");

  bindFilterButtons();
}

function renderFeatured(items) {
  if (!featuredGrid) return;
  if (!items.length) {
    featuredGrid.innerHTML =
      '<p style="grid-column:1/-1;text-align:center;">Featured items coming soon.</p>';
    return;
  }

  const topThree = items.slice(0, 3);
  featuredGrid.innerHTML = topThree
    .map((item) => {
      const catName = item.category?.name || "Signature";
      return `
        <article class="af-card" data-category="${slugify(catName)}">
          <img src="${item.image_url || "assets/meal-1.jpg"}" alt="${item.name}" class="af-card-img" />
          <div class="af-card-body">
            <div class="af-card-top">
              <h3>${item.name}</h3>
              <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
                <span class="af-tag">${catName}</span>
                ${
                  item.is_sold_out
                    ? '<span class="af-pill" style="background:#fef2f2;color:#b91c1c;border-color:#fecdd3;">Sold Out</span>'
                    : ""
                }
              </div>
            </div>
            <p>${item.description || "Fresh from our kitchen."}</p>
            <div class="af-card-footer">
              <span class="af-price">₦${Number(item.price).toLocaleString()}</span>
              <button
                class="af-btn af-btn-sm af-btn-primary"
                data-item="${item.name}"
                data-item-id="${item.id}"
                data-item-price="${item.price}"
                data-sold-out="${item.is_sold_out ? "1" : "0"}"
                ${item.is_sold_out ? "disabled" : ""}
              >
                ${item.is_sold_out ? "Sold Out" : "Add to Cart"}
              </button>
            </div>
          </div>
        </article>
      `;
    })
    .join("");

  bindAddToCartButtons();
}

function renderMenu(items) {
  if (!menuGrid) return;
  if (!items.length) {
    menuGrid.innerHTML =
      '<p style="grid-column:1/-1;text-align:center;">Menu is coming soon. Please check back.</p>';
    return;
  }

  menuGrid.innerHTML = items
    .map((item) => {
      const catName = item.category?.name || "Menu";
      const catSlug = slugify(catName);
      return `
        <article class="af-menu-item" data-category="${catSlug}">
          <div class="af-menu-head">
            <h3>${item.name}</h3>
            <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
              <span class="af-pill">${catName}</span>
              ${
                item.is_sold_out
                  ? '<span class="af-pill" style="background:#fef2f2;color:#b91c1c;border-color:#fecdd3;">Sold Out</span>'
                  : ""
              }
            </div>
          </div>
          <p>${item.description || "Freshly prepared from our kitchen."}</p>
          <div class="af-menu-footer">
            <span class="af-price">₦${Number(item.price).toLocaleString()}</span>
            <button
              class="af-btn af-btn-sm af-btn-outline"
              data-item="${item.name}"
              data-item-id="${item.id}"
              data-item-price="${item.price}"
              data-sold-out="${item.is_sold_out ? "1" : "0"}"
              ${item.is_sold_out ? "disabled" : ""}
            >
              ${item.is_sold_out ? "Sold Out" : "Add to Cart"}
            </button>
          </div>
        </article>
      `;
    })
    .join("");

  bindAddToCartButtons();
  applyFilter();
}

function applyFilter() {
  if (!menuGrid) return;
  menuGrid.querySelectorAll(".af-menu-item").forEach((item) => {
    const category = item.getAttribute("data-category");
    item.style.display =
      activeFilter === "all" || category === activeFilter ? "block" : "none";
  });
}

async function loadMenuData() {
  if (!menuGrid && !featuredGrid && !menuFilters) return;
  try {
    const [itemsRes, categoriesRes] = await Promise.all([
      fetch("/api/menu-items?active_only=1"),
      fetch("/api/categories?active_only=1")
    ]);

    if (!itemsRes.ok || !categoriesRes.ok) {
      throw new Error("Could not load menu data.");
    }

    const [items, categories] = await Promise.all([
      itemsRes.json(),
      categoriesRes.json()
    ]);

    renderFilters(categories || []);
    renderMenu(items || []);
    renderFeatured(items || []);
  } catch (err) {
    if (featuredGrid) {
      featuredGrid.innerHTML =
        '<p style="grid-column:1/-1;text-align:center;">Unable to load menu right now.</p>';
    }
    if (menuGrid) {
      menuGrid.innerHTML =
        '<p style="grid-column:1/-1;text-align:center;">Unable to load menu right now.</p>';
    }
    console.error(err);
  }
}

// Checkout buttons

async function handlePaystack(form, triggerBtn) {
  if (!cart.length) {
    alert("Your cart is empty.");
    return;
  }
  if (!form) {
    alert("Please fill your details first.");
    return;
  }

  const formData = new FormData(form);
  const name = formData.get("name");
  const phone = formData.get("phone");

  const payload = {
    channel: "web",
    customer_name: name,
    customer_phone: phone,
    items: cart.map((item) => ({
      menu_item_id: item.id,
      quantity: item.qty,
      price: item.price
    })),
    payment: {
      amount: getCartTotal(),
      method: "web-paystack",
      reference: `WEB-${Date.now()}`
    }
  };

  if (payload.items.some((i) => !i.menu_item_id)) {
    alert("Missing menu item IDs; please refresh the page.");
    return;
  }

  const resetBtnState = () => {
    if (!triggerBtn) return;
    triggerBtn.removeAttribute("aria-busy");
    triggerBtn.removeAttribute("disabled");
    triggerBtn.textContent = "Pay with Card / Transfer (Paystack)";
  };

  if (triggerBtn) {
    triggerBtn.setAttribute("aria-busy", "true");
    triggerBtn.setAttribute("disabled", "disabled");
    triggerBtn.textContent = "Placing order...";
  }

  try {
    const res = await fetch("/api/orders", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json"
      },
      body: JSON.stringify(payload)
    });

    if (!res.ok) {
      let message = "Could not place your order. Please try again.";
      try {
        const error = await res.json();
        if (error?.message) message = error.message;
        if (error?.errors?.items?.[0]) message = error.errors.items[0];
      } catch (err) {
        // ignore JSON parse issues
      }
      throw new Error(message);
    }

    const order = await res.json();
    alert(
      `Order placed! Your code is ${order.code || "pending"}. We will confirm shortly.`
    );
    cart = [];
    renderCart();
    form.reset();
    closeCartOverlay();
  } catch (err) {
    alert(err.message || "Something went wrong while placing your order.");
  } finally {
    resetBtnState();
  }
}

function handleWhatsApp(form) {
  if (!cart.length) {
    alert("Your cart is empty.");
    return;
  }
  if (!form) {
    alert("Please fill your details first.");
    return;
  }

  const formData = new FormData(form);

  const name = formData.get("name");
  const phone = formData.get("phone");
  const service = formData.get("service");
  const time = formData.get("time");
  const note = formData.get("note");

  let message = `New Order - Acie Fraiche Cafe%0A%0A`;
  message += `Name: ${name}%0A`;
  message += `Phone: ${phone}%0A`;
  message += `Service: ${service}%0A`;
  message += `Time: ${time}%0A`;
  if (note) message += `Note: ${note}%0A`;
  message += `%0AItems:%0A`;

  cart.forEach((item) => {
    message += `- ${item.name} (₦${item.price.toLocaleString()} × ${item.qty})%0A`;
  });

  message += `%0ATotal: ₦${getCartTotal().toLocaleString()}%0A`;
  message += `%0AOrder Source: Website`;

  const whatsappNumber = "2347015862018"; // TODO: replace with real number
  const url = `https://wa.me/${whatsappNumber}?text=${message}`;
  window.open(url, "_blank");
}

// Kick off dynamic menu load
loadMenuData();
bindFilterButtons();

// Removed periodic refresh that was causing flickering
// setInterval(loadMenuData, 15000);

// Attach checkout handlers for all buttons
document.querySelectorAll("[data-paystack-btn]").forEach((btn) => {
  btn.addEventListener("click", () => {
    const formId = btn.getAttribute("data-form");
    const form = formId ? document.getElementById(formId) : null;
    handlePaystack(form, btn);
  });
});

document.querySelectorAll("[data-whatsapp-btn]").forEach((btn) => {
  btn.addEventListener("click", () => {
    const formId = btn.getAttribute("data-form");
    const form = formId ? document.getElementById(formId) : null;
    handleWhatsApp(form);
  });
});
