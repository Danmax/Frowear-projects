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
const accessTabs = [...document.querySelectorAll(".access-tab")];
const accessPanes = [...document.querySelectorAll(".access-pane")];
const loginUiForm = document.querySelector("#login-ui-form");
const loginUiNote = document.querySelector("#login-ui-note");
const signupUiForm = document.querySelector("#signup-ui-form");
const signupUiNote = document.querySelector("#signup-ui-note");
const resetUiForm = document.querySelector("#reset-ui-form");
const resetUiNote = document.querySelector("#reset-ui-note");
const verifyUiForm = document.querySelector("#verify-ui-form");
const verifyUiNote = document.querySelector("#verify-ui-note");
const profileUiForm = document.querySelector("#profile-ui-form");
const profileUiNote = document.querySelector("#profile-ui-note");
const companyAccessForm = document.querySelector("#company-access-form");
const companyAccessNote = document.querySelector("#company-access-note");
const profilePreviewName = document.querySelector("#profilePreviewName");
const profilePreviewRole = document.querySelector("#profilePreviewRole");
const profilePreviewBio = document.querySelector("#profilePreviewBio");
const profilePreviewStatus = document.querySelector("#profilePreviewStatus");
const profilePreviewCity = document.querySelector("#profilePreviewCity");
const profilePreviewNewsletter = document.querySelector("#profilePreviewNewsletter");
const skillsManagerPreview = document.querySelector("#skillsManagerPreview");
const profileLinksPreview = document.querySelector("#profileLinksPreview");
const profilePreferencesPreview = document.querySelector("#profilePreferencesPreview");
const resumeHtmlPreview = document.querySelector("#resumeHtmlPreview");
const flowAccountStatus = document.querySelector("#flowAccountStatus");
const flowAccountDetail = document.querySelector("#flowAccountDetail");
const flowVerifyStatus = document.querySelector("#flowVerifyStatus");
const flowVerifyDetail = document.querySelector("#flowVerifyDetail");
const flowResetStatus = document.querySelector("#flowResetStatus");
const flowResetDetail = document.querySelector("#flowResetDetail");
const flowCompanyStatus = document.querySelector("#flowCompanyStatus");
const flowCompanyDetail = document.querySelector("#flowCompanyDetail");

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
        { field: "image", label: "Image", type: "image" },
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
        { field: "city", label: "City" },
        { field: "bio", label: "Bio", type: "textarea" },
        { field: "skills", label: "Skills", type: "list" },
        { field: "availability", label: "Availability" },
        { field: "profileStatus", label: "Profile status" },
        { field: "newsletter", label: "Newsletter" },
        { field: "interests", label: "Interests", type: "list" },
        { field: "preferences", label: "Preferences", type: "list" },
        { field: "profileLinks", label: "Profile links", type: "list" },
        { field: "resumeHtml", label: "Resume HTML", type: "textarea" },
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

function compactProfileLine(profile) {
  return [profile.availability, profile.city].filter(Boolean).join(" • ");
}

function renderTalent() {
  document.querySelector("#talentGrid").innerHTML = siteState.talent
    .map(
      (profile) => `
        <article class="network-card">
          <p class="company-kicker">${escapeHtml(profile.role)}</p>
          <h3>${escapeHtml(profile.name)}</h3>
          <p class="project-company">${escapeHtml(compactProfileLine(profile))}</p>
          <p>${escapeHtml(profile.bio)}</p>
          <div class="chip-cloud">
            ${profile.profileStatus ? `<div class="focus-chip">${escapeHtml(profile.profileStatus)}</div>` : ""}
            ${profile.newsletter ? `<div class="focus-chip">${escapeHtml(profile.newsletter)}</div>` : ""}
            ${profile.resumeHtml ? `<div class="focus-chip">Resume Ready</div>` : ""}
          </div>
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
          <div class="project-tag-group">
            <strong>Preferences</strong>
            <div class="chip-cloud">
              ${(profile.preferences || []).map((item) => `<div class="focus-chip">${escapeHtml(item)}</div>`).join("")}
            </div>
          </div>
          <div class="project-tag-group">
            <strong>Links</strong>
            <div class="chip-cloud">
              ${(profile.profileLinks || []).map((item) => `<div class="focus-chip">${escapeHtml(item)}</div>`).join("")}
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

  if (fieldConfig.type === "image") {
    const inputId = `img-file-${section}-${index ?? "obj"}-${fieldConfig.field}`;
    return `
      <div class="admin-field admin-image-field">
        <label>
          ${escapeHtml(fieldConfig.label)}
          <input
            type="text"
            value="${escapeAttribute(value || "")}"
            ${attributes.join(" ")}
            class="admin-image-url"
            placeholder="https://… or upload below"
          />
        </label>
        <div class="admin-image-controls">
          <input
            type="file"
            accept="image/*"
            class="admin-image-file"
            id="${escapeAttribute(inputId)}"
            hidden
            data-section="${escapeAttribute(section)}"
            data-field="${escapeAttribute(fieldConfig.field)}"
            ${index !== null ? `data-index="${index}"` : ""}
          />
          <label for="${escapeAttribute(inputId)}" class="ghost-button">Upload WebP</label>
          <span class="admin-image-status"></span>
        </div>
        ${value
          ? `<img class="admin-image-preview" src="${escapeAttribute(value)}" alt="" loading="lazy" />`
          : `<div class="admin-image-placeholder">No image set</div>`}
      </div>
    `;
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
      city: "",
      bio: "",
      skills: ["Add skill"],
      availability: "",
      profileStatus: "Public",
      newsletter: "Subscribed",
      interests: ["Add interest"],
      preferences: ["Add preference"],
      profileLinks: ["https://example.com"],
      resumeHtml: "<section><h1>Resume</h1><p>Add summary</p></section>",
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

function splitLines(value) {
  return String(value || "")
    .split("\n")
    .map((item) => item.trim())
    .filter(Boolean);
}

function renderChipList(target, values, fallback) {
  if (!target) {
    return;
  }

  if (!values.length) {
    target.innerHTML = `<div class="focus-chip">${escapeHtml(fallback)}</div>`;
    return;
  }

  target.innerHTML = values.map((value) => `<div class="focus-chip">${escapeHtml(value)}</div>`).join("");
}

function setMessageState(statusNode, detailNode, variant, status, detail) {
  if (!statusNode || !detailNode) {
    return;
  }

  statusNode.className = `message-state message-state--${variant}`;
  statusNode.textContent = status;
  detailNode.textContent = detail;
}

function renderProfileUiPreview() {
  if (!profileUiForm) {
    return;
  }

  const data = new FormData(profileUiForm);
  const fullName = String(data.get("fullName") || "").trim() || "Your Name";
  const role = String(data.get("role") || "").trim() || "Role";
  const availability = String(data.get("availability") || "").trim();
  const city = String(data.get("city") || "").trim();
  const bio = String(data.get("bio") || "").trim() || "Profile summary updates as the form changes.";
  const status = String(data.get("profileStatus") || "Public").trim();
  const newsletterEnabled = profileUiForm.elements.newsletter?.checked;
  const publicResume = profileUiForm.elements.publicResume?.checked;
  const skills = splitLines(data.get("skills"));
  const preferences = splitLines(data.get("preferences"));
  const links = ["portfolio", "linkedin", "github"]
    .map((field) => String(data.get(field) || "").trim())
    .filter(Boolean);
  const resumeMarkup = String(data.get("resumeHtml") || "").trim();

  if (profilePreviewName) {
    profilePreviewName.textContent = fullName;
  }

  if (profilePreviewRole) {
    profilePreviewRole.textContent = [role, availability].filter(Boolean).join(" • ") || role;
  }

  if (profilePreviewBio) {
    profilePreviewBio.textContent = bio;
  }

  if (profilePreviewStatus) {
    profilePreviewStatus.textContent = status;
  }

  if (profilePreviewCity) {
    profilePreviewCity.textContent = city || "City not set";
  }

  if (profilePreviewNewsletter) {
    profilePreviewNewsletter.textContent = newsletterEnabled ? "Newsletter on" : "Newsletter off";
  }

  renderChipList(skillsManagerPreview, skills, "Add skills");
  renderChipList(profileLinksPreview, links, "Add profile links");

  const preferenceChips = [
    ...preferences,
    profileUiForm.elements.jobAlerts?.checked ? "Opportunity alerts" : "",
    publicResume ? "Public resume" : "Private resume",
  ].filter(Boolean);
  renderChipList(profilePreferencesPreview, preferenceChips, "Add preferences");

  if (resumeHtmlPreview) {
    resumeHtmlPreview.textContent = resumeMarkup || "<section>Resume markup preview</section>";
  }
}

async function compressToWebp(file, maxKilobytes = 700) {
  return new Promise((resolve, reject) => {
    const img = new Image();
    const src = URL.createObjectURL(file);

    img.onload = () => {
      URL.revokeObjectURL(src);

      const MAX_DIM = 2400;
      let w = img.naturalWidth;
      let h = img.naturalHeight;
      if (w > MAX_DIM || h > MAX_DIM) {
        const scale = Math.min(MAX_DIM / w, MAX_DIM / h);
        w = Math.round(w * scale);
        h = Math.round(h * scale);
      }

      const canvas = document.createElement('canvas');
      canvas.width = w;
      canvas.height = h;
      canvas.getContext('2d').drawImage(img, 0, 0, w, h);

      const attempt = (quality) => {
        canvas.toBlob((blob) => {
          if (!blob) { reject(new Error('Canvas conversion failed.')); return; }
          if (blob.size <= maxKilobytes * 1024 || quality <= 0.1) {
            const name = file.name.replace(/\.[^.]+$/, '.webp');
            resolve(new File([blob], name, { type: 'image/webp' }));
          } else {
            attempt(parseFloat((quality - 0.08).toFixed(2)));
          }
        }, 'image/webp', quality);
      };

      attempt(0.88);
    };

    img.onerror = () => { URL.revokeObjectURL(src); reject(new Error('Image failed to load.')); };
    img.src = src;
  });
}

async function uploadAdminImage(file, statusEl, urlInput, section, field, index) {
  statusEl.textContent = 'Compressing…';
  try {
    const compressed = await compressToWebp(file);
    const kb = (compressed.size / 1024).toFixed(0);
    statusEl.textContent = `Uploading ${kb} KB…`;

    const form = new FormData();
    form.append('file', compressed);

    const response = await fetch(adminEndpoint, {
      method: 'POST',
      credentials: 'same-origin',
      body: form,
    });

    const result = await response.json().catch(() => ({}));
    if (!response.ok || !result.ok) throw new Error(result.message || 'Upload failed.');

    urlInput.value = result.url;
    updateDraftField(section, field, result.url, index !== '' ? index : null);

    const wrapper = urlInput.closest('.admin-image-field');
    if (wrapper) {
      const old = wrapper.querySelector('.admin-image-preview, .admin-image-placeholder');
      if (old) old.remove();
      const preview = document.createElement('img');
      preview.className = 'admin-image-preview';
      preview.src = result.url;
      preview.alt = '';
      preview.loading = 'lazy';
      wrapper.append(preview);
    }

    statusEl.textContent = `Saved · ${kb} KB`;
  } catch (err) {
    statusEl.textContent = err.message;
  }
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

adminSections?.addEventListener("change", (event) => {
  const fileInput = event.target.closest(".admin-image-file");
  if (!fileInput || !fileInput.files?.[0]) return;

  const { section, field, index } = fileInput.dataset;
  const wrapper = fileInput.closest(".admin-image-field");
  const statusEl = wrapper?.querySelector(".admin-image-status");
  const urlInput = wrapper?.querySelector(".admin-image-url");
  if (!statusEl || !urlInput) return;

  uploadAdminImage(fileInput.files[0], statusEl, urlInput, section, field, index ?? null);
  fileInput.value = "";
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

accessTabs.forEach((tab) => {
  tab.addEventListener("click", () => {
    const key = tab.dataset.accessTab;
    accessTabs.forEach((item) => item.classList.toggle("is-active", item === tab));
    accessPanes.forEach((pane) => {
      pane.classList.toggle("is-hidden", pane.dataset.accessPane !== key);
    });
  });
});

if (loginUiForm && loginUiNote) {
  loginUiForm.addEventListener("submit", (event) => {
    event.preventDefault();
    const data = new FormData(loginUiForm);
    const remembered = loginUiForm.elements.rememberSession?.checked ? " Session persistence is on." : "";
    loginUiNote.textContent = `${data.get("email")}, the login UI is ready for account auth.${remembered}`;
  });
}

if (signupUiForm && signupUiNote) {
  signupUiForm.addEventListener("submit", (event) => {
    event.preventDefault();
    const data = new FormData(signupUiForm);
    const email = String(data.get("email") || "").trim();
    const accountType = String(data.get("accountType") || "Talent").trim();

    signupUiNote.textContent = `${data.get("fullName")}, your ${accountType.toLowerCase()} signup flow is staged. Confirmation and verification emails are ready to send to ${email}.`;
    setMessageState(
      flowAccountStatus,
      flowAccountDetail,
      "sent",
      "Created",
      `Account setup prepared for ${email} with ${accountType.toLowerCase()} access.`
    );
    setMessageState(
      flowVerifyStatus,
      flowVerifyDetail,
      "sent",
      "Sent",
      `Verification email queued for ${email} with a confirmation link and backup code.`
    );
  });
}

if (resetUiForm && resetUiNote) {
  resetUiForm.addEventListener("submit", (event) => {
    event.preventDefault();
    const data = new FormData(resetUiForm);
    const email = String(data.get("email") || "").trim();
    const reason = String(data.get("reason") || "").trim();

    resetUiNote.textContent = `Recovery instructions are prepared for ${email}. Reason logged: ${reason.toLowerCase()}.`;
    setMessageState(
      flowResetStatus,
      flowResetDetail,
      "sent",
      "Sent",
      `Password reset email prepared for ${email} with a secure time-limited link.`
    );
  });
}

if (verifyUiForm && verifyUiNote) {
  verifyUiForm.addEventListener("submit", (event) => {
    event.preventDefault();
    const data = new FormData(verifyUiForm);
    const email = String(data.get("email") || "").trim();

    verifyUiNote.textContent = `${email} is marked verified in the UI flow. The next backend step is token validation and account activation.`;
    setMessageState(
      flowVerifyStatus,
      flowVerifyDetail,
      "ready",
      "Verified",
      `Email verified for ${email}. Welcome and profile completion messages can follow.`
    );
  });
}

profileUiForm?.addEventListener("input", renderProfileUiPreview);

if (profileUiForm && profileUiNote) {
  profileUiForm.addEventListener("submit", (event) => {
    event.preventDefault();
    const data = new FormData(profileUiForm);
    const profileStatus = String(data.get("profileStatus") || "Public").toLowerCase();
    profileUiNote.textContent = `${data.get("fullName")}, your ${profileStatus} profile settings are ready for account-backed saving.`;
    renderProfileUiPreview();
  });
}

if (companyAccessForm && companyAccessNote) {
  companyAccessForm.addEventListener("submit", (event) => {
    event.preventDefault();
    const data = new FormData(companyAccessForm);
    companyAccessNote.textContent = `${data.get("companyName")} request captured in the UI. The next backend step is request submission and approval workflow.`;
    setMessageState(
      flowCompanyStatus,
      flowCompanyDetail,
      "review",
      "Review",
      `${data.get("companyName")} access request is staged for ${data.get("requestedRole")} review.`
    );
  });
}

renderSite();
renderProfileUiPreview();
setActiveLink();
syncAdminVisibility();

if (isAdminUnlocked) {
  renderAdminEditor();
}
