const defaultContent = window.__FW_SITE_DATA__ || {};
const adminEndpoint = window.__FW_ADMIN_ENDPOINT__ || "admin.php";

const siteState = structuredClone(defaultContent);
let adminDraft = structuredClone(siteState);
let activeProjectFilter = "all";
let isAdminUnlocked = Boolean(window.__FW_ADMIN_AUTHENTICATED__);
let activeAdminTab = "companyInfo";

const menuToggle = document.querySelector(".menu-toggle");
const siteNav = document.querySelector(".site-nav");
const adminTrigger = document.querySelector("#adminTrigger");
const adminPanel = document.querySelector("#adminPanel");
const adminBackdrop = document.querySelector("#adminBackdrop");
const adminClose = document.querySelector("#adminClose");
const adminAuth = document.querySelector("#adminAuth");
const adminEditor = document.querySelector("#adminEditor");
const adminLoginForm = document.querySelector("#adminLoginForm");
const adminKeyInput = document.querySelector("#adminKeyInput");
const adminAuthNote = document.querySelector("#adminAuthNote");
const adminTabList = document.querySelector("#adminTabList");
const adminSections = document.querySelector("#adminSections");
const adminSave = document.querySelector("#adminSave");
const adminReset = document.querySelector("#adminReset");
const adminLogout = document.querySelector("#adminLogout");
const adminSaveNote = document.querySelector("#adminSaveNote");
const quoteForm = document.querySelector("#quote-form");
const formNote = document.querySelector("#form-note");

const navLinks = [...document.querySelectorAll(".site-nav a")];
const sections = [...document.querySelectorAll("main section[id]")];

const textTargets = {
  brandMark: ["companyInfo", "brandMark"],
  brandName: ["companyInfo", "name"],
  brandTagline: ["companyInfo", "tagline"],
  heroEyebrow: ["companyInfo", "heroEyebrow"],
  heroTitle: ["companyInfo", "heroTitle"],
  heroDescription: ["companyInfo", "heroDescription"],
  companyHeading: ["companyInfo", "companyHeading"],
  companyIntro: ["companyInfo", "companyIntro"],
  companyStudioLabel: ["companyInfo", "studioLabel"],
  companyName: ["companyInfo", "name"],
  companyNarrative: ["companyInfo", "companyNarrative"],
  brandPositioning: ["branding", "positioning"],
  brandVoice: ["branding", "voice"],
  primaryCta: ["companyInfo", "primaryCtaLabel"],
  secondaryCta: ["companyInfo", "secondaryCtaLabel"],
  quoteDescription: ["companyInfo", "responseWindow"],
  footerBrand: ["companyInfo", "name"],
};

const themeFieldMap = {
  bg: "--bg",
  bgDeep: "--bg-deep",
  panel: "--panel",
  panelStrong: "--panel-strong",
  line: "--line",
  lineStrong: "--line-strong",
  text: "--text",
  muted: "--muted",
  cyan: "--cyan",
  green: "--green",
  pink: "--pink",
};

const adminTabConfig = [
  {
    key: "companyInfo",
    label: "Company",
    render: () =>
      renderObjectSection({
        title: "Company Info",
        section: "companyInfo",
        fields: [
          { field: "name", label: "Company name" },
          { field: "brandMark", label: "Brand mark" },
          { field: "tagline", label: "Tagline" },
          { field: "heroEyebrow", label: "Hero eyebrow" },
          { field: "heroTitle", label: "Hero title", type: "textarea" },
          { field: "heroDescription", label: "Hero description", type: "textarea" },
          { field: "primaryCtaLabel", label: "Primary CTA label" },
          { field: "secondaryCtaLabel", label: "Secondary CTA label" },
          { field: "companyHeading", label: "Company heading" },
          { field: "companyIntro", label: "Company intro", type: "textarea" },
          { field: "companyNarrative", label: "Company narrative", type: "textarea" },
          { field: "studioLabel", label: "Studio label" },
          { field: "responseWindow", label: "Quote response line" },
          { field: "location", label: "Location" },
          { field: "email", label: "Email" },
        ],
      }),
  },
  {
    key: "projects",
    label: "Projects",
    render: () =>
      renderCollectionSection("Projects", "projects", [
        { field: "title", label: "Title" },
        { field: "category", label: "Category" },
        { field: "status", label: "Status" },
        { field: "company", label: "Company" },
        { field: "stage", label: "Stage" },
        { field: "image", label: "Image URL" },
        { field: "alt", label: "Image alt text" },
        { field: "summary", label: "Summary", type: "textarea" },
        { field: "points", label: "Bullet points", type: "list" },
        { field: "stack", label: "Stack tags", type: "list" },
        { field: "needs", label: "Needed skills", type: "list" },
      ]),
  },
  {
    key: "services",
    label: "Services",
    render: () =>
      renderCollectionSection("Services", "services", [
        { field: "title", label: "Title" },
        { field: "description", label: "Description", type: "textarea" },
      ]),
  },
  {
    key: "skills",
    label: "Skills",
    render: () =>
      renderCollectionSection("Skills", "skills", [
        { field: "name", label: "Skill name" },
        { field: "summary", label: "Summary", type: "textarea" },
      ]),
  },
  {
    key: "opportunities",
    label: "Opportunities",
    render: () =>
      renderCollectionSection("Opportunities", "opportunities", [
        { field: "title", label: "Title" },
        { field: "company", label: "Company" },
        { field: "summary", label: "Summary", type: "textarea" },
        { field: "commitment", label: "Commitment" },
        { field: "focus", label: "Focus" },
        { field: "skills", label: "Skills", type: "list" },
        { field: "applyLabel", label: "Apply label" },
      ]),
  },
  {
    key: "companies",
    label: "Companies",
    render: () =>
      renderCollectionSection("Companies", "companies", [
        { field: "name", label: "Company name" },
        { field: "industry", label: "Industry" },
        { field: "location", label: "Location" },
        { field: "bio", label: "Bio", type: "textarea" },
        { field: "skills", label: "Skills", type: "list" },
        { field: "opportunities", label: "Opportunity labels", type: "list" },
      ]),
  },
  {
    key: "talent",
    label: "Talent",
    render: () =>
      renderCollectionSection("Talent", "talent", [
        { field: "name", label: "Name" },
        { field: "role", label: "Role" },
        { field: "bio", label: "Bio", type: "textarea" },
        { field: "skills", label: "Skills", type: "list" },
        { field: "availability", label: "Availability" },
        { field: "interests", label: "Interests", type: "list" },
      ]),
  },
  {
    key: "branding",
    label: "Branding",
    render: () =>
      renderObjectSection({
        title: "Branding",
        section: "branding",
        fields: [
          { field: "positioning", label: "Positioning", type: "textarea" },
          { field: "voice", label: "Voice", type: "textarea" },
          { field: "focusAreas", label: "Focus areas", type: "list" },
          { field: "highlights", label: "Brand highlights", type: "list" },
        ],
      }),
  },
  {
    key: "theme",
    label: "Theme",
    render: () =>
      renderObjectSection({
        title: "Theme",
        section: "theme",
        fields: Object.keys(siteState.theme).map((field) => ({ field, label: field })),
      }),
  },
];

function getValue(path) {
  return path.reduce((accumulator, key) => accumulator?.[key], siteState);
}

function replaceSiteContent(nextContent) {
  Object.keys(siteState).forEach((key) => delete siteState[key]);
  Object.assign(siteState, structuredClone(nextContent));
  adminDraft = structuredClone(siteState);
}

function renderTextTargets() {
  Object.entries(textTargets).forEach(([id, path]) => {
    const node = document.getElementById(id);
    if (!node) {
      return;
    }

    node.textContent = getValue(path) || "";
  });
}

function renderHeroMetrics() {
  const metrics = [
    { title: siteState.services[0]?.title || "Web + UI", detail: siteState.services[0]?.description || "" },
    { title: siteState.services[1]?.title || "Systems", detail: siteState.services[1]?.description || "" },
    { title: siteState.services[5]?.title || "Talent Platforms", detail: siteState.services[5]?.description || "" },
  ];

  document.querySelector("#heroMetrics").innerHTML = metrics
    .map(
      (metric) => `
        <li>
          <strong>${escapeHtml(metric.title)}</strong>
          <span>${escapeHtml(metric.detail)}</span>
        </li>
      `
    )
    .join("");
}

function renderSignalStrip() {
  const items = siteState.services.slice(0, 4);
  document.querySelector("#signalStrip").innerHTML = items
    .map(
      (item) => `
        <div class="signal-item">
          <span class="company-kicker">${escapeHtml(item.title)}</span>
          <p>${escapeHtml(item.description)}</p>
        </div>
      `
    )
    .join("");
}

function renderCompanyFacts() {
  const facts = [
    { label: "Location", value: siteState.companyInfo.location },
    { label: "Contact", value: siteState.companyInfo.email },
    { label: "Response", value: siteState.companyInfo.responseWindow },
    { label: "Companies", value: `${siteState.companies.length} active profiles` },
  ];

  document.querySelector("#companyFacts").innerHTML = facts
    .map(
      (fact) => `
        <div class="fact-item">
          <span>${escapeHtml(fact.label)}</span>
          <strong>${escapeHtml(fact.value)}</strong>
        </div>
      `
    )
    .join("");
}

function renderFocusAreas() {
  document.querySelector("#focusAreas").innerHTML = siteState.branding.focusAreas
    .map((item) => `<div class="focus-chip">${escapeHtml(item)}</div>`)
    .join("");
}

function buildProjectCategories() {
  const categories = [...new Set(siteState.projects.map((project) => project.category).filter(Boolean))];
  return ["all", ...categories];
}

function labelFromCategory(category) {
  return category
    .split("-")
    .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
    .join(" ");
}

function renderProjectFilters() {
  document.querySelector("#projectFilters").innerHTML = buildProjectCategories()
    .map(
      (category) => `
        <button
          class="filter-chip${category === activeProjectFilter ? " is-active" : ""}"
          type="button"
          data-filter="${escapeAttribute(category)}"
        >
          ${escapeHtml(category === "all" ? "All" : labelFromCategory(category))}
        </button>
      `
    )
    .join("");
}

function renderProjects() {
  document.querySelector("#projectGrid").innerHTML = siteState.projects
    .map((project) => {
      const hidden = activeProjectFilter !== "all" && project.category !== activeProjectFilter;
      return `
        <article class="project-card${hidden ? " is-hidden" : ""}" data-category="${escapeAttribute(project.category)}">
          <img src="${escapeAttribute(project.image)}" alt="${escapeAttribute(project.alt)}" />
          <div class="project-body">
            <div class="project-meta">
              <span>${escapeHtml(labelFromCategory(project.category))}</span>
              <span>${escapeHtml(project.status)}</span>
              <span>${escapeHtml(project.stage || "")}</span>
            </div>
            <h3>${escapeHtml(project.title)}</h3>
            <p class="project-company">${escapeHtml(project.company || "")}</p>
            <p>${escapeHtml(project.summary)}</p>
            <ul class="project-points">
              ${(project.points || []).map((point) => `<li>${escapeHtml(point)}</li>`).join("")}
            </ul>
            <div class="project-tag-group">
              <strong>Stack</strong>
              <div class="chip-cloud">
                ${(project.stack || []).map((item) => `<div class="focus-chip">${escapeHtml(item)}</div>`).join("")}
              </div>
            </div>
            <div class="project-tag-group">
              <strong>Needed</strong>
              <div class="chip-cloud">
                ${(project.needs || []).map((item) => `<div class="focus-chip">${escapeHtml(item)}</div>`).join("")}
              </div>
            </div>
          </div>
        </article>
      `;
    })
    .join("");
}

function renderServices() {
  document.querySelector("#serviceGrid").innerHTML = siteState.services
    .map(
      (service) => `
        <article class="service-card">
          <h3>${escapeHtml(service.title)}</h3>
          <p>${escapeHtml(service.description)}</p>
        </article>
      `
    )
    .join("");
}

function renderSkills() {
  document.querySelector("#skillGrid").innerHTML = siteState.skills
    .map(
      (skill) => `
        <article class="skill-card">
          <h3>${escapeHtml(skill.name)}</h3>
          <p>${escapeHtml(skill.summary)}</p>
        </article>
      `
    )
    .join("");
}

function renderOpportunities() {
  document.querySelector("#opportunityGrid").innerHTML = siteState.opportunities
    .map(
      (item) => `
        <article class="opportunity-card">
          <p class="company-kicker">${escapeHtml(item.commitment)}</p>
          <h3>${escapeHtml(item.title)}</h3>
          <p class="project-company">${escapeHtml(item.company || "")}</p>
          <p>${escapeHtml(item.summary)}</p>
          <ul class="opportunity-meta">
            <li>${escapeHtml(item.focus)}</li>
          </ul>
          <div class="chip-cloud">
            ${(item.skills || []).map((skill) => `<div class="focus-chip">${escapeHtml(skill)}</div>`).join("")}
          </div>
          <div class="card-action-row">
            <button class="ghost-button" type="button">${escapeHtml(item.applyLabel || "Apply")}</button>
          </div>
        </article>
      `
    )
    .join("");
}

function renderCompanies() {
  document.querySelector("#companiesGrid").innerHTML = siteState.companies
    .map(
      (company) => `
        <article class="network-card">
          <p class="company-kicker">${escapeHtml(company.industry)}</p>
          <h3>${escapeHtml(company.name)}</h3>
          <p class="project-company">${escapeHtml(company.location)}</p>
          <p>${escapeHtml(company.bio)}</p>
          <div class="project-tag-group">
            <strong>Skills</strong>
            <div class="chip-cloud">
              ${(company.skills || []).map((skill) => `<div class="focus-chip">${escapeHtml(skill)}</div>`).join("")}
            </div>
          </div>
          <div class="project-tag-group">
            <strong>Open work</strong>
            <div class="chip-cloud">
              ${(company.opportunities || []).map((item) => `<div class="focus-chip">${escapeHtml(item)}</div>`).join("")}
            </div>
          </div>
        </article>
      `
    )
    .join("");
}

function renderTalent() {
  document.querySelector("#talentGrid").innerHTML = siteState.talent
    .map(
      (profile) => `
        <article class="network-card">
          <p class="company-kicker">${escapeHtml(profile.role)}</p>
          <h3>${escapeHtml(profile.name)}</h3>
          <p class="project-company">${escapeHtml(profile.availability)}</p>
          <p>${escapeHtml(profile.bio)}</p>
          <div class="project-tag-group">
            <strong>Skills</strong>
            <div class="chip-cloud">
              ${(profile.skills || []).map((skill) => `<div class="focus-chip">${escapeHtml(skill)}</div>`).join("")}
            </div>
          </div>
          <div class="project-tag-group">
            <strong>Interested in</strong>
            <div class="chip-cloud">
              ${(profile.interests || []).map((item) => `<div class="focus-chip">${escapeHtml(item)}</div>`).join("")}
            </div>
          </div>
        </article>
      `
    )
    .join("");
}

function renderBranding() {
  document.querySelector("#brandHighlights").innerHTML = siteState.branding.highlights
    .map(
      (item) => `
        <div class="highlight-item">
          <span>Brand</span>
          <strong>${escapeHtml(item)}</strong>
        </div>
      `
    )
    .join("");

  document.querySelector("#themeSwatches").innerHTML = Object.entries(siteState.theme)
    .map(
      ([key, value]) => `
        <div class="theme-swatch">
          <i style="background:${escapeAttribute(value)}"></i>
          <span>${escapeHtml(key)}</span>
        </div>
      `
    )
    .join("");
}

function renderQuoteOptions() {
  const select = document.querySelector("#quoteProjectType");
  const options = siteState.services.map((service) => service.title);
  select.innerHTML = `
    <option value="">Select a project type</option>
    ${options.map((option) => `<option value="${escapeAttribute(option)}">${escapeHtml(option)}</option>`).join("")}
  `;

  document.querySelector("#quotePoints").innerHTML = options
    .slice(0, 4)
    .map((option) => `<li>${escapeHtml(option)}</li>`)
    .join("");
}

function applyTheme() {
  Object.entries(themeFieldMap).forEach(([field, variable]) => {
    document.documentElement.style.setProperty(variable, siteState.theme[field]);
  });
}

function renderSite() {
  applyTheme();
  renderTextTargets();
  renderHeroMetrics();
  renderSignalStrip();
  renderCompanyFacts();
  renderFocusAreas();
  renderProjectFilters();
  renderProjects();
  renderServices();
  renderSkills();
  renderOpportunities();
  renderCompanies();
  renderTalent();
  renderBranding();
  renderQuoteOptions();
}

function setActiveLink() {
  const offset = window.scrollY + 160;
  const currentSection = sections.find((section) => offset >= section.offsetTop && offset < section.offsetTop + section.offsetHeight);

  navLinks.forEach((link) => {
    const isActive = currentSection && link.getAttribute("href") === `#${currentSection.id}`;
    link.classList.toggle("is-active", Boolean(isActive));
  });
}

function openAdminPanel() {
  adminPanel.classList.add("is-open");
  adminPanel.setAttribute("aria-hidden", "false");
  adminBackdrop.hidden = false;
  document.body.classList.add("admin-open");
}

function closeAdminPanel() {
  adminPanel.classList.remove("is-open");
  adminPanel.setAttribute("aria-hidden", "true");
  adminBackdrop.hidden = true;
  document.body.classList.remove("admin-open");
}

function syncAdminVisibility() {
  adminAuth.classList.toggle("is-hidden", isAdminUnlocked);
  adminEditor.classList.toggle("is-hidden", !isAdminUnlocked);
}

function renderAdminEditor() {
  const activeTabExists = adminTabConfig.some((tab) => tab.key === activeAdminTab);
  if (!activeTabExists) {
    activeAdminTab = adminTabConfig[0].key;
  }

  adminTabList.innerHTML = adminTabConfig
    .map(
      (tab) => `
        <button
          class="admin-tab${tab.key === activeAdminTab ? " is-active" : ""}"
          type="button"
          role="tab"
          data-admin-tab="${escapeAttribute(tab.key)}"
          aria-selected="${tab.key === activeAdminTab ? "true" : "false"}"
        >
          ${escapeHtml(tab.label)}
        </button>
      `
    )
    .join("");

  adminSections.innerHTML = adminTabConfig
    .map(
      (tab) => `
        <section class="admin-pane${tab.key === activeAdminTab ? "" : " is-hidden"}" data-admin-pane="${escapeAttribute(tab.key)}">
          ${tab.render()}
        </section>
      `
    )
    .join("");
}

function renderObjectSection({ title, section, fields }) {
  return `
    <section class="admin-section">
      <div class="admin-section__header">
        <h3>${escapeHtml(title)}</h3>
      </div>
      <div class="admin-grid">
        ${fields.map((fieldConfig) => renderField(section, null, fieldConfig)).join("")}
      </div>
    </section>
  `;
}

function renderCollectionSection(title, section, fields) {
  return `
    <section class="admin-section">
      <div class="admin-section__header">
        <h3>${escapeHtml(title)}</h3>
        <button class="ghost-button" type="button" data-action="add-item" data-collection="${escapeAttribute(section)}">
          Add
        </button>
      </div>
      <div class="admin-collection">
        ${adminDraft[section]
          .map(
            (_, index) => `
              <article class="admin-collection-item">
                <div class="admin-collection-item__header">
                  <strong>${escapeHtml(title)} ${index + 1}</strong>
                  <button
                    class="ghost-button"
                    type="button"
                    data-action="remove-item"
                    data-collection="${escapeAttribute(section)}"
                    data-index="${index}"
                  >
                    Remove
                  </button>
                </div>
                <div class="admin-grid">
                  ${fields.map((fieldConfig) => renderField(section, index, fieldConfig)).join("")}
                </div>
              </article>
            `
          )
          .join("")}
      </div>
    </section>
  `;
}

function renderField(section, index, fieldConfig) {
  const value = index === null ? adminDraft[section][fieldConfig.field] : adminDraft[section][index][fieldConfig.field];
  const attributes = [
    `data-section="${escapeAttribute(section)}"`,
    `data-field="${escapeAttribute(fieldConfig.field)}"`,
  ];

  if (index !== null) {
    attributes.push(`data-index="${index}"`);
  }

  if (fieldConfig.type === "textarea") {
    return `
      <label class="admin-field">
        ${escapeHtml(fieldConfig.label)}
        <textarea rows="4" ${attributes.join(" ")}>${escapeHtml(value || "")}</textarea>
      </label>
    `;
  }

  if (fieldConfig.type === "list") {
    return `
      <label class="admin-field">
        ${escapeHtml(fieldConfig.label)}
        <textarea rows="4" ${attributes.join(" ")}>${escapeHtml((value || []).join("\n"))}</textarea>
      </label>
    `;
  }

  return `
    <label class="admin-field">
      ${escapeHtml(fieldConfig.label)}
      <input type="text" value="${escapeAttribute(value || "")}" ${attributes.join(" ")} />
    </label>
  `;
}

function updateDraftField(section, field, value, index) {
  if (index === null || index === undefined || index === "") {
    const current = adminDraft[section][field];
    adminDraft[section][field] = Array.isArray(current)
      ? value.split("\n").map((item) => item.trim()).filter(Boolean)
      : value;
    return;
  }

  const target = adminDraft[section][Number(index)];
  if (!target) {
    return;
  }

  target[field] = Array.isArray(target[field])
    ? value.split("\n").map((item) => item.trim()).filter(Boolean)
    : value;
}

function addCollectionItem(collection) {
  const templates = {
    projects: {
      title: "New Project",
      category: "websites",
      status: "New",
      company: "",
      stage: "",
      image: "",
      alt: "",
      summary: "",
      points: ["Add project detail"],
      stack: ["Add stack"],
      needs: ["Add needed skill"],
    },
    services: { title: "New Service", description: "" },
    skills: { name: "New Skill", summary: "" },
    opportunities: {
      title: "New Opportunity",
      company: "",
      summary: "",
      commitment: "",
      focus: "",
      skills: ["Add skill"],
      applyLabel: "Apply",
    },
    companies: {
      name: "New Company",
      industry: "",
      location: "",
      bio: "",
      skills: ["Add company skill"],
      opportunities: ["Add opportunity"],
    },
    talent: {
      name: "New Talent",
      role: "",
      bio: "",
      skills: ["Add skill"],
      availability: "",
      interests: ["Add interest"],
    },
  };

  adminDraft[collection].push(structuredClone(templates[collection]));
  renderAdminEditor();
}

function removeCollectionItem(collection, index) {
  adminDraft[collection].splice(Number(index), 1);
  renderAdminEditor();
}

async function postAdmin(action, extra = {}) {
  const response = await fetch(adminEndpoint, {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    credentials: "same-origin",
    body: JSON.stringify({ action, ...extra }),
  });

  const payload = await response.json().catch(() => ({}));
  if (!response.ok || payload.ok === false) {
    throw new Error(payload.message || "Request failed.");
  }

  return payload;
}

function lockAdminUI() {
  isAdminUnlocked = false;
  syncAdminVisibility();
  adminAuthNote.textContent = "";
  adminSaveNote.textContent = "";
  adminKeyInput.value = "";
}

function escapeHtml(value) {
  return String(value)
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#39;");
}

function escapeAttribute(value) {
  return escapeHtml(value);
}

document.addEventListener("click", (event) => {
  const filterButton = event.target.closest("[data-filter]");
  if (filterButton) {
    activeProjectFilter = filterButton.dataset.filter || "all";
    renderProjectFilters();
    renderProjects();
  }
});

document.addEventListener("scroll", setActiveLink, { passive: true });

navLinks.forEach((link) => {
  link.addEventListener("click", () => {
    siteNav?.classList.remove("is-open");
    menuToggle?.setAttribute("aria-expanded", "false");
  });
});

if (menuToggle && siteNav) {
  menuToggle.addEventListener("click", () => {
    const isOpen = siteNav.classList.toggle("is-open");
    menuToggle.setAttribute("aria-expanded", String(isOpen));
  });
}

adminTrigger?.addEventListener("click", openAdminPanel);
adminClose?.addEventListener("click", closeAdminPanel);
adminBackdrop?.addEventListener("click", closeAdminPanel);

adminLoginForm?.addEventListener("submit", async (event) => {
  event.preventDefault();
  adminAuthNote.textContent = "Checking admin key...";

  try {
    const payload = await postAdmin("login", { key: adminKeyInput.value });
    replaceSiteContent(payload.content);
    isAdminUnlocked = true;
    syncAdminVisibility();
    renderSite();
    renderAdminEditor();
    adminAuthNote.textContent = "";
    adminSaveNote.textContent = "Admin unlocked.";
    adminKeyInput.value = "";
  } catch (error) {
    adminAuthNote.textContent = error.message;
  }
});

adminTabList?.addEventListener("click", (event) => {
  const tab = event.target.closest("[data-admin-tab]");
  if (!tab) {
    return;
  }

  activeAdminTab = tab.dataset.adminTab;
  renderAdminEditor();
});

adminSections?.addEventListener("input", (event) => {
  const field = event.target.closest("[data-section][data-field]");
  if (!field) {
    return;
  }

  updateDraftField(field.dataset.section, field.dataset.field, field.value, field.dataset.index);
});

adminSections?.addEventListener("click", (event) => {
  const button = event.target.closest("[data-action]");
  if (!button) {
    return;
  }

  const action = button.dataset.action;
  const collection = button.dataset.collection;
  const index = button.dataset.index;

  if (action === "add-item" && collection) {
    addCollectionItem(collection);
  }

  if (action === "remove-item" && collection && index !== undefined) {
    removeCollectionItem(collection, index);
  }
});

adminSave?.addEventListener("click", async () => {
  adminSaveNote.textContent = "Saving changes...";

  try {
    const payload = await postAdmin("save", { content: adminDraft });
    replaceSiteContent(payload.content);
    renderSite();
    renderAdminEditor();
    adminSaveNote.textContent = payload.message || "Content saved.";
  } catch (error) {
    adminSaveNote.textContent = error.message;
  }
});

adminReset?.addEventListener("click", async () => {
  adminSaveNote.textContent = "Resetting content...";

  try {
    const payload = await postAdmin("reset");
    replaceSiteContent(payload.content);
    activeProjectFilter = "all";
    renderSite();
    renderAdminEditor();
    adminSaveNote.textContent = payload.message || "Default content restored.";
  } catch (error) {
    adminSaveNote.textContent = error.message;
  }
});

adminLogout?.addEventListener("click", async () => {
  try {
    await postAdmin("logout");
  } catch {
    // Ignore logout transport errors and still lock the UI.
  }

  lockAdminUI();
});

if (quoteForm && formNote) {
  quoteForm.addEventListener("submit", (event) => {
    event.preventDefault();
    const data = new FormData(quoteForm);
    const name = data.get("name");
    const projectType = data.get("projectType");
    const timeline = data.get("timeline");

    formNote.textContent = `${name}, your ${String(projectType).toLowerCase()} request for ${timeline} is ready for follow-up.`;
    quoteForm.reset();
    renderQuoteOptions();
  });
}

renderSite();
setActiveLink();
syncAdminVisibility();

if (isAdminUnlocked) {
  renderAdminEditor();
}
