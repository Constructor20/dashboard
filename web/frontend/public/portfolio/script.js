const dots = document.querySelectorAll(".nav-dot");
const sections = document.querySelectorAll("section");

function updateActiveNav() {
  let current = "";
  sections.forEach((sec) => {
    const top = sec.offsetTop - 200;
    if (window.scrollY >= top) current = sec.id;
  });
  dots.forEach((dot) => dot.classList.toggle("active", dot.dataset.target === current));
}

window.addEventListener("scroll", updateActiveNav);
window.addEventListener("load", updateActiveNav);

dots.forEach((dot) => {
  dot.addEventListener("click", (e) => {
    e.preventDefault();
    const target = document.getElementById(dot.dataset.target);
    if (target) target.scrollIntoView({ behavior: "smooth" });
  });
});

function formatDate(dateStr) {
  if (!dateStr) return "";
  const d = new Date(dateStr);
  return d.toLocaleDateString("fr-FR", {
    day: "numeric", month: "long", year: "numeric",
    hour: "2-digit", minute: "2-digit"
  });
}

function microSummary(text) {
  if (!text) return "";
  const clean = text.replace(/<[^>]*>/g, "").replace(/&\w+;/g, " ").trim();
  return clean.length > 80 ? clean.slice(0, 80) + "..." : clean;
}

function stripHtml(text) {
  if (!text) return "";
  return text.replace(/<[^>]*>/g, "").replace(/&\w+;/g, " ").replace(/\s+/g, " ").trim();
}

let allNews = [];

function openArticle(item) {
  const modal = document.getElementById("news-modal");
  const body = modal.querySelector(".news-modal-body");
  body.innerHTML = `
    <div class="modal-article-source">${item.source || "Actualité"}</div>
    <div class="modal-article-title">${item.title}</div>
    <div class="modal-article-date">${formatDate(item.pubDate || item.date)}</div>
    <div class="modal-article-desc">${stripHtml(item.description || "Aucune description.")}</div>
    <a href="${item.link}" target="_blank" class="modal-article-link">
      <span>Lire l'article original</span>
      <span>→</span>
    </a>
  `;
  modal.classList.remove("hidden");
}

function closeArticle() {
  document.getElementById("news-modal").classList.add("hidden");
}

function renderNews() {
  const container = document.getElementById("news-container");
  const items = allNews.slice(0, 3);
  if (!items.length) {
    container.innerHTML = '<div class="loading-news">Aucune actualité pour le moment.</div>';
    return;
  }
  container.innerHTML = items.map((item, idx) => `
    <div class="news-card" data-idx="${idx}">
      <div class="news-source">${item.source || "Actualité"}</div>
      <div class="news-title">${item.title}</div>
      <div class="news-micro">${microSummary(item.description)}</div>
      <div class="news-date">${formatDate(item.pubDate || item.date)}</div>
      <div class="news-expand-hint">CLIQUER POUR DÉTAIL →</div>
    </div>
  `).join("");

  container.querySelectorAll(".news-card").forEach((card) => {
    card.addEventListener("click", () => {
      const idx = parseInt(card.dataset.idx);
      openArticle(allNews[idx]);
    });
  });

  const existingBtn = container.parentElement.querySelector(".news-toggle");
  if (existingBtn) existingBtn.remove();

  if (allNews.length > 3) {
    const link = document.createElement("a");
    link.className = "news-toggle";
    link.href = "actualites.html";
    link.innerHTML = '<span class="btn-glass">→ Voir toutes les actus (' + allNews.length + ')</span>';
    container.after(link);
  }
}

async function loadNews() {
  const updatedEl = document.getElementById("news-updated");
  try {
    const res = await fetch("data/news.json?_=" + Date.now());
    if (!res.ok) throw new Error("Not found");
    const data = await res.json();
    allNews = data.items || [];
    renderNews();
    if (data.updated) updatedEl.textContent = "Dernière mise à jour : " + formatDate(data.updated);
  } catch {
    document.getElementById("news-container").innerHTML =
      '<div class="loading-news">Actualités bientôt disponibles.</div>';
  }
}

document.getElementById("news-modal").querySelector(".news-modal-close").addEventListener("click", closeArticle);
document.getElementById("news-modal").querySelector(".news-modal-backdrop").addEventListener("click", closeArticle);
document.addEventListener("keydown", (e) => { if (e.key === "Escape") closeArticle(); });

document.addEventListener("DOMContentLoaded", loadNews);