<?php
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$initialContent = get_site_content();
$isAdminAuthenticated = is_admin_authenticated();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Frowear Productions | UI Engineering Portfolio</title>
    <meta
      name="description"
      content="Frowear Productions builds high-tech websites, complete systems, APIs, integrations, opportunity networks, and talent-ready project platforms."
    />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&amp;family=Space+Grotesk:wght@500;700&amp;display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="style.css" />
  </head>
  <body>
    <header class="site-header">
      <a class="brand" href="#home" aria-label="Frowear Productions home">
        <span class="brand-mark" id="brandMark">FP</span>
        <span class="brand-lockup">
          <strong id="brandName">Frowear Productions</strong>
          <small id="brandTagline">Design-led engineering for digital systems</small>
        </span>
      </a>

      <div class="header-actions">
        <button class="admin-trigger" id="adminTrigger" type="button">Admin</button>
        <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="site-nav">
          Menu
        </button>
      </div>

      <nav id="site-nav" class="site-nav" aria-label="Primary">
        <a href="#company">Company</a>
        <a href="#work">Projects</a>
        <a href="#services">Services</a>
        <a href="#opportunities">Opportunities</a>
        <a href="#companies">Companies</a>
        <a href="#talent">Talent</a>
        <a href="#branding">Branding</a>
        <a href="#quote">Free Quote</a>
      </nav>
    </header>

    <main>
      <section class="hero" id="home">
        <div class="hero-backdrop"></div>
        <div class="hero-content">
          <p class="eyebrow" id="heroEyebrow"></p>
          <h1 id="heroTitle"></h1>
          <p class="hero-copy" id="heroDescription"></p>
          <div class="hero-actions">
            <a class="button button-primary" id="primaryCta" href="#quote"></a>
            <a class="button button-secondary" id="secondaryCta" href="#work"></a>
          </div>
          <ul class="hero-metrics" id="heroMetrics" aria-label="Studio metrics"></ul>
        </div>
      </section>

      <section class="signal-strip" id="signalStrip" aria-label="Capabilities overview"></section>

      <section class="section" id="company">
        <div class="section-header">
          <div>
            <p class="eyebrow">Company Info</p>
            <h2 id="companyHeading"></h2>
          </div>
          <p class="section-copy" id="companyIntro"></p>
        </div>

        <div class="company-layout">
          <article class="company-card company-main">
            <p class="company-kicker" id="companyStudioLabel"></p>
            <h3 id="companyName"></h3>
            <p id="companyNarrative"></p>
            <div class="company-facts" id="companyFacts"></div>
          </article>

          <article class="company-card company-side">
            <p class="company-kicker">Brand Positioning</p>
            <h3 id="brandPositioning"></h3>
            <p id="brandVoice"></p>
            <div class="chip-cloud" id="focusAreas"></div>
          </article>
        </div>
      </section>

      <section class="section" id="work">
        <div class="section-header">
          <div>
            <p class="eyebrow">Project Gallery</p>
            <h2>Dynamic project cards with teams, needs, and stack visibility</h2>
          </div>
          <p class="section-copy">
            Projects now surface status, company ownership, stack, required skills, and a stronger
            card layout so the gallery reads like live work instead of flat placeholders.
          </p>
        </div>

        <div class="filter-bar" id="projectFilters" role="toolbar" aria-label="Project filters"></div>
        <div class="project-grid" id="projectGrid"></div>
      </section>

      <section class="section" id="services">
        <div class="section-header">
          <div>
            <p class="eyebrow">Services</p>
            <h2>What customers can bring in</h2>
          </div>
          <p class="section-copy">
            Service lanes remain editable in admin and now align directly with company profiles,
            opportunities, and project creation.
          </p>
        </div>

        <div class="service-grid" id="serviceGrid"></div>
      </section>

      <section class="section" id="skills">
        <div class="section-header">
          <div>
            <p class="eyebrow">Skills</p>
            <h2>Engineering and design strengths</h2>
          </div>
          <p class="section-copy">
            Skills can describe studio capabilities, company needs, and talent strengths in one shared
            vocabulary.
          </p>
        </div>

        <div class="skill-grid" id="skillGrid"></div>
      </section>

      <section class="section" id="opportunities">
        <div class="section-header">
          <div>
            <p class="eyebrow">Opportunities</p>
            <h2>Open opportunities ready for companies and talent</h2>
          </div>
          <p class="section-copy">
            Companies can list what they need, and individual talent can review openings before moving
            into project work.
          </p>
        </div>

        <div class="opportunity-grid" id="opportunityGrid"></div>
      </section>

      <section class="section" id="companies">
        <div class="section-header">
          <div>
            <p class="eyebrow">Company Profiles</p>
            <h2>Other companies can create profiles and list project needs</h2>
          </div>
          <p class="section-copy">
            This section is shaped for a multi-company network where each organization can show its
            profile, skills, and active opportunity pipeline.
          </p>
        </div>

        <div class="network-grid" id="companiesGrid"></div>
      </section>

      <section class="section" id="talent">
        <div class="section-header">
          <div>
            <p class="eyebrow">Talent Profiles</p>
            <h2>Individuals can present bio, skills, and project interest</h2>
          </div>
          <p class="section-copy">
            Talent profiles are structured for bio, skills, availability, and opportunity alignment so
            the platform can grow past a single company portfolio.
          </p>
        </div>

        <div class="network-grid" id="talentGrid"></div>
      </section>

      <section class="section" id="branding">
        <div class="section-header">
          <div>
            <p class="eyebrow">Branding + Theme</p>
            <h2>Visible brand and theme options</h2>
          </div>
          <p class="section-copy">
            Brand highlights, focus areas, and theme colors remain editable from the admin workspace
            and reflected live across the site.
          </p>
        </div>

        <div class="branding-layout">
          <article class="brand-card">
            <p class="company-kicker">Brand Highlights</p>
            <div class="brand-highlights" id="brandHighlights"></div>
          </article>

          <article class="brand-card">
            <p class="company-kicker">Theme Palette</p>
            <div class="theme-swatches" id="themeSwatches"></div>
          </article>
        </div>
      </section>

      <section class="section quote-section" id="quote">
        <div class="quote-layout">
          <div class="quote-copy">
            <p class="eyebrow">Free Quote</p>
            <h2>Bring the next project in.</h2>
            <p id="quoteDescription"></p>
            <ul class="quote-points" id="quotePoints"></ul>
          </div>

          <form class="quote-form" id="quote-form">
            <label>
              Name
              <input type="text" name="name" placeholder="Your name" required />
            </label>
            <label>
              Email
              <input type="email" name="email" placeholder="you@company.com" required />
            </label>
            <label>
              Project Type
              <select name="projectType" id="quoteProjectType" required></select>
            </label>
            <label>
              Target Window
              <input type="text" name="timeline" placeholder="Month, quarter, or deadline" required />
            </label>
            <label class="form-span">
              Project Brief
              <textarea
                name="details"
                rows="5"
                placeholder="What are you building, who is it for, and what needs to happen?"
                required
              ></textarea>
            </label>
            <button class="button button-primary" type="submit">Request Free Quote</button>
            <p class="form-note" id="form-note" aria-live="polite"></p>
          </form>
        </div>
      </section>
    </main>

    <footer class="site-footer">
      <p id="footerBrand"></p>
      <a href="#quote">Start a new build</a>
    </footer>

    <aside class="admin-panel" id="adminPanel" aria-hidden="true">
      <div class="admin-panel__header">
        <div>
          <p class="eyebrow">Admin</p>
          <h2>Content Controls</h2>
        </div>
        <button class="admin-close" id="adminClose" type="button" aria-label="Close admin panel">
          Close
        </button>
      </div>

      <div class="admin-auth<?= $isAdminAuthenticated ? ' is-hidden' : '' ?>" id="adminAuth">
        <p class="admin-note">
          Admin access is verified on the server with <code>FW_ADMIN_KEY</code> from your Hostinger
          environment or <code>.env</code> file.
        </p>
        <form class="admin-login" id="adminLoginForm">
          <label>
            Admin key
            <input type="password" id="adminKeyInput" placeholder="Enter FW_ADMIN_KEY" required />
          </label>
          <button class="button button-primary" type="submit">Unlock Admin</button>
          <p class="form-note" id="adminAuthNote" aria-live="polite"></p>
        </form>
      </div>

      <div class="admin-editor<?= $isAdminAuthenticated ? '' : ' is-hidden' ?>" id="adminEditor">
        <div class="admin-toolbar">
          <button class="button button-primary" id="adminSave" type="button">Save Changes</button>
          <button class="button button-secondary" id="adminReset" type="button">Reset Defaults</button>
          <button class="button button-secondary" id="adminLogout" type="button">Lock</button>
        </div>
        <div class="admin-tablist" id="adminTabList" role="tablist" aria-label="Admin sections"></div>
        <div class="admin-sections" id="adminSections"></div>
        <p class="form-note" id="adminSaveNote" aria-live="polite"></p>
      </div>
    </aside>

    <div class="admin-backdrop" id="adminBackdrop" hidden></div>

    <script>
      window.__FW_SITE_DATA__ = <?= json_encode($initialContent, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
      window.__FW_ADMIN_AUTHENTICATED__ = <?= $isAdminAuthenticated ? 'true' : 'false' ?>;
      window.__FW_ADMIN_ENDPOINT__ = "admin.php";
    </script>
    <script src="script.js"></script>
  </body>
</html>
