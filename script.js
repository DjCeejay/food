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

function renderCart() {
  const list = document.getElementById("cartList");
  const totalEl = document.getElementById("cartTotal");
  list.innerHTML = "";

  let total = 0;

  cart.forEach((item, index) => {
    total += item.price * item.qty;

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
  });
});

// Cart quantity buttons
document.getElementById("cartList").addEventListener("click", (e) => {
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

// 1. Paystack placeholder – here you’ll later call your PHP endpoint or Paystack inline
document.getElementById("paystackBtn").addEventListener("click", () => {
  if (!cart.length) {
    alert("Your cart is empty.");
    return;
  }
  alert("Paystack integration will go here.");
});

// 2. WhatsApp order – build a message and redirect
document.getElementById("whatsappBtn").addEventListener("click", () => {
  if (!cart.length) {
    alert("Your cart is empty.");
    return;
  }

  const form = document.getElementById("checkoutForm");
  const formData = new FormData(form);

  const name = formData.get("name");
  const phone = formData.get("phone");
  const address = formData.get("address");
  const note = formData.get("note");

  let message = `New Order - Acie Fraiche Café%0A%0A`;
  message += `Name: ${name}%0A`;
  message += `Phone: ${phone}%0A`;
  message += `Address: ${address}%0A`;
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
});
