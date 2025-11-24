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

  let total = 0;
  cart.forEach((item) => {
    total += item.price * item.qty;
  });

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

function addToCart(name, price) {
  const existing = cart.find((i) => i.name === name);
  if (existing) {
    existing.qty += 1;
  } else {
    cart.push({ name, price, qty: 1 });
  }
  renderCart();
}

// Attach to "Add to Cart" buttons
document.querySelectorAll("[data-item]").forEach((btn) => {
  btn.addEventListener("click", () => {
    const name = btn.getAttribute("data-item");
    // TODO: Get real price from dataset or DB; using placeholder for now
    const priceEl = btn.closest("article")?.querySelector(".af-price");
    const price = priceEl
      ? parseInt(priceEl.textContent.replace(/[^\d]/g, ""), 10)
      : 0;
    addToCart(name, price);
    flyToCart(btn);
  });
});

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

// Menu filters
document.querySelectorAll(".af-chip").forEach((chip) => {
  chip.addEventListener("click", () => {
    const filter = chip.getAttribute("data-filter");
    document
      .querySelectorAll(".af-chip")
      .forEach((c) => c.classList.remove("af-chip-active"));
    chip.classList.add("af-chip-active");

    document.querySelectorAll(".af-menu-item").forEach((item) => {
      const category = item.getAttribute("data-category");
      item.style.display =
        filter === "all" || filter === category ? "block" : "none";
    });
  });
});

// Checkout buttons (stubs)

function handlePaystack(form) {
  if (!cart.length) {
    alert("Your cart is empty.");
    return;
  }
  alert("Paystack integration will go here.");
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

  let message = `New Order - Acie Fraiche Café%0A%0A`;
  message += `Name: ${name}%0A`;
  message += `Phone: ${phone}%0A`;
  message += `Service: ${service}%0A`;
  message += `Time: ${time}%0A`;
  if (note) message += `Note: ${note}%0A`;
  message += `%0AItems:%0A`;

  let total = 0;
  cart.forEach((item) => {
    total += item.price * item.qty;
    message += `- ${item.name} (₦${item.price.toLocaleString()} × ${
      item.qty
    })%0A`;
  });

  message += `%0ATotal: ₦${total.toLocaleString()}%0A`;
  message += `%0AOrder Source: Website`;

  const whatsappNumber = "2349012345678"; // TODO: replace with real number
  const url = `https://wa.me/${whatsappNumber}?text=${message}`;
  window.open(url, "_blank");
}

// Attach checkout handlers for all buttons
document.querySelectorAll("[data-paystack-btn]").forEach((btn) => {
  btn.addEventListener("click", () => {
    const formId = btn.getAttribute("data-form");
    const form = formId ? document.getElementById(formId) : null;
    handlePaystack(form);
  });
});

document.querySelectorAll("[data-whatsapp-btn]").forEach((btn) => {
  btn.addEventListener("click", () => {
    const formId = btn.getAttribute("data-form");
    const form = formId ? document.getElementById(formId) : null;
    handleWhatsApp(form);
  });
});
