document.addEventListener("DOMContentLoaded", () => {
  const track = document.querySelector(".carousel-track");
  document.querySelectorAll(".carousel-arrow").forEach((arrow) => {
    arrow.addEventListener("click", () => {
      if (!track) return;
      const dir = Number(arrow.dataset.dir || 1);
      track.scrollBy({ left: track.clientWidth * dir, behavior: "smooth" });
    });
  });

  const filterBar = document.querySelector("[data-filter-bar]");
  const grid = document.querySelector("[data-product-grid]");

  if (filterBar && grid) {
    const buttons = Array.from(filterBar.querySelectorAll(".filter[data-color]"));
    const sortSelect = filterBar.querySelector(".sort");
    const cards = Array.from(grid.querySelectorAll(".product-card"));
    let activeColor = (buttons.find((b) => b.classList.contains("active"))?.dataset.color) || "Todas";

    const applyFilterAndSort = () => {
      const sortMode = sortSelect ? sortSelect.value : "default";

      const sorted = [...cards].sort((a, b) => {
        if (sortMode === "sold") return Number(b.dataset.sold || 0) - Number(a.dataset.sold || 0);
        if (sortMode === "rating") return Number(b.dataset.rating || 0) - Number(a.dataset.rating || 0);
        if (sortMode === "price-asc") return Number(a.dataset.price || 0) - Number(b.dataset.price || 0);
        if (sortMode === "price-desc") return Number(b.dataset.price || 0) - Number(a.dataset.price || 0);
        return 0;
      });

      sorted.forEach((card) => {
        const matches = activeColor === "Todas" || card.dataset.color === activeColor;
        card.style.display = matches ? "" : "none";
        grid.appendChild(card);
      });
    };

    buttons.forEach((btn) => {
      btn.addEventListener("click", () => {
        buttons.forEach((b) => b.classList.remove("active"));
        btn.classList.add("active");
        activeColor = btn.dataset.color || "Todas";
        applyFilterAndSort();
      });
    });

    if (sortSelect) {
      sortSelect.addEventListener("change", applyFilterAndSort);
    }

    applyFilterAndSort();
  }

  const menuBtn = document.querySelector(".menu-toggle");
  const header = document.querySelector("header");
  if (menuBtn && header) {
    menuBtn.addEventListener("click", () => {
      const open = header.classList.toggle("nav-open");
      menuBtn.setAttribute("aria-expanded", open ? "true" : "false");
    });
  }
});