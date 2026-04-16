const menuToggle = document.querySelector(".menu-toggle");
const siteNav = document.querySelector(".site-nav");
const navLinks = [...document.querySelectorAll(".site-nav a")];
const sections = [...document.querySelectorAll("main section[id]")];
const filterChips = [...document.querySelectorAll(".filter-chip")];
const projectCards = [...document.querySelectorAll(".project-card")];
const showcaseTabs = [...document.querySelectorAll(".showcase-tab")];
const showcaseImage = document.querySelector("#showcase-image");
const showcaseKicker = document.querySelector("#showcase-kicker");
const showcaseTitle = document.querySelector("#showcase-title");
const showcaseDescription = document.querySelector("#showcase-description");
const showcaseList = document.querySelector("#showcase-list");
const showcaseStats = document.querySelector("#showcase-stats");
const quoteForm = document.querySelector("#quote-form");
const formNote = document.querySelector("#form-note");

const showcaseContent = {
  integrations: {
    kicker: "Integration Example",
    title: "Connected checkout, CRM, and delivery signals in one operating flow.",
    description:
      "Designed for teams that need the customer journey and the system behavior to stay aligned from the first click through fulfillment and reporting.",
    image:
      "https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&w=1400&q=80",
    alt: "Business dashboard with connected reporting visuals and workflow tracking",
    points: [
      "Payment, CRM, inventory, and notifications aligned inside a single experience.",
      "Operational issues surfaced quickly with clean state changes and clearer customer follow-up.",
    ],
    stats: ["Payments + CRM", "Live status UI", "Analytics sync"],
  },
  design: {
    kicker: "Design Example",
    title: "A UI system that keeps visual polish without slowing product delivery.",
    description:
      "Built for teams that want premium visual rhythm, consistent components, and a system that scales across launches, dashboards, and campaign pages.",
    image:
      "https://images.unsplash.com/photo-1522542550221-31fd19575a2d?auto=format&fit=crop&w=1400&q=80",
    alt: "Designer and engineer reviewing interface layouts on a large screen",
    points: [
      "Shared component logic, spacing rules, and motion cues for a more consistent brand feel.",
      "Clear enough for product teams, flexible enough for special-purpose pages and new features.",
    ],
    stats: ["Component rules", "Responsive patterns", "Premium visual tone"],
  },
  systems: {
    kicker: "Complete System",
    title: "A full working product surface for teams with real internal complexity.",
    description:
      "Designed around handoffs, approvals, queue visibility, and role-based actions so the system works cleanly under real operating pressure.",
    image:
      "https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=1400&q=80",
    alt: "Operations team reviewing a complete dashboard system on multiple screens",
    points: [
      "Interfaces shaped around the workflow, not just the screen layout.",
      "Product logic, backend state, and user action designed as one connected system.",
    ],
    stats: ["Role-based views", "Workflow state", "Operational clarity"],
  },
};

if (menuToggle && siteNav) {
  menuToggle.addEventListener("click", () => {
    const isOpen = siteNav.classList.toggle("is-open");
    menuToggle.setAttribute("aria-expanded", String(isOpen));
  });
}

const setActiveLink = () => {
  const offset = window.scrollY + 160;
  const currentSection = sections.find((section) => {
    return offset >= section.offsetTop && offset < section.offsetTop + section.offsetHeight;
  });

  navLinks.forEach((link) => {
    const isActive = currentSection && link.getAttribute("href") === `#${currentSection.id}`;
    link.classList.toggle("is-active", Boolean(isActive));
  });
};

setActiveLink();
window.addEventListener("scroll", setActiveLink, { passive: true });

navLinks.forEach((link) => {
  link.addEventListener("click", () => {
    siteNav?.classList.remove("is-open");
    menuToggle?.setAttribute("aria-expanded", "false");
  });
});

filterChips.forEach((chip) => {
  chip.addEventListener("click", () => {
    const filter = chip.dataset.filter;

    filterChips.forEach((item) => item.classList.toggle("is-active", item === chip));

    projectCards.forEach((card) => {
      const categories = (card.dataset.category || "").split(" ");
      const visible = filter === "all" || categories.includes(filter);
      card.classList.toggle("is-hidden", !visible);
    });
  });
});

const renderShowcase = (key) => {
  const entry = showcaseContent[key];

  if (!entry || !showcaseImage || !showcaseKicker || !showcaseTitle) {
    return;
  }

  showcaseKicker.textContent = entry.kicker;
  showcaseTitle.textContent = entry.title;
  showcaseDescription.textContent = entry.description;
  showcaseImage.src = entry.image;
  showcaseImage.alt = entry.alt;

  showcaseList.innerHTML = "";
  entry.points.forEach((point) => {
    const item = document.createElement("li");
    item.textContent = point;
    showcaseList.appendChild(item);
  });

  showcaseStats.innerHTML = "";
  entry.stats.forEach((stat) => {
    const item = document.createElement("span");
    item.textContent = stat;
    showcaseStats.appendChild(item);
  });

  showcaseTabs.forEach((tab) => {
    tab.classList.toggle("is-active", tab.dataset.showcase === key);
  });
};

showcaseTabs.forEach((tab) => {
  tab.addEventListener("click", () => {
    const key = tab.dataset.showcase;

    if (key) {
      renderShowcase(key);
    }
  });
});

renderShowcase("integrations");

if (quoteForm && formNote) {
  quoteForm.addEventListener("submit", (event) => {
    event.preventDefault();

    const data = new FormData(quoteForm);
    const name = data.get("name");
    const projectType = data.get("projectType");
    const timeline = data.get("timeline");

    formNote.textContent = `${name}, your ${projectType.toLowerCase()} request for ${timeline} is ready for follow-up.`;
    quoteForm.reset();
  });
}
