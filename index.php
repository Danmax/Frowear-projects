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
    <meta name="theme-color" content="#4be7ff" />
    <meta name="mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <meta name="apple-mobile-web-app-title" content="Frowear" />
    <meta property="og:title" content="Frowear Productions" />
    <meta property="og:description" content="Design-led engineering for websites, systems, APIs, and talent platforms." />
    <meta property="og:type" content="website" />
    <link rel="manifest" href="manifest.json" />
    <link rel="apple-touch-icon" href="icons/icon.svg" />
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
        <a href="#access">Access</a>
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

      <section class="section" id="access">
        <div class="section-header">
          <div>
            <p class="eyebrow">Platform Access</p>
            <h2>Join the network. Build together.</h2>
          </div>
          <p class="section-copy">
            Create a talent or company account to post to the feed, message collaborators, bid on
            projects, and manage your profile inside the platform.
          </p>
        </div>

        <div class="access-gateway">
          <div class="gateway-card">
            <p class="company-kicker">Talent</p>
            <h3>Join as a builder</h3>
            <p>Create a profile, list your skills, browse open opportunities, and collaborate on projects through the platform.</p>
            <ul class="access-list">
              <li>Public or invite-only profile visibility</li>
              <li>Bid on project and contract work</li>
              <li>Direct messaging with companies and talent</li>
              <li>Feed posts: achievements, skill shares, news</li>
            </ul>
            <a class="button button-primary" href="platform.php">Create Talent Account</a>
          </div>

          <div class="gateway-card">
            <p class="company-kicker">Company</p>
            <h3>Join as a company</h3>
            <p>Post opportunities, manage a company profile, review incoming bids, and build a team through the project network.</p>
            <ul class="access-list">
              <li>Company profile with skills and open work</li>
              <li>Post opportunities and project needs</li>
              <li>Review bids and issue contracts</li>
              <li>Feed posts: launches, events, collaborations</li>
            </ul>
            <a class="button button-primary" href="platform.php">Create Company Account</a>
          </div>

          <div class="gateway-card gateway-card--signin">
            <p class="company-kicker">Returning?</p>
            <h3>Sign back in</h3>
            <p>Pick up where you left off — your feed, messages, bids, and profile are waiting.</p>
            <a class="button button-secondary" href="platform.php">Sign In to Platform</a>
          </div>
        </div>

        <!-- legacy access-layout kept for JS compatibility, hidden visually -->
        <div class="access-layout" style="display:none;" aria-hidden="true">
          <aside class="access-rail" role="tablist" aria-label="Access options">
            <button class="access-tab is-active" type="button" data-access-tab="login">Login</button>
            <button class="access-tab" type="button" data-access-tab="signup">Sign Up</button>
            <button class="access-tab" type="button" data-access-tab="reset">Reset</button>
            <button class="access-tab" type="button" data-access-tab="verify">Verify</button>
            <button class="access-tab" type="button" data-access-tab="profile">Profile</button>
            <button class="access-tab" type="button" data-access-tab="company">Company Access</button>
          </aside>

          <div class="access-stage">
            <section class="access-pane" data-access-pane="login">
              <div class="access-card">
                <p class="company-kicker">Login UI</p>
                <h3>Access your workspace</h3>
                <form class="access-form" id="login-ui-form">
                  <label>
                    Email
                    <input type="email" name="email" placeholder="you@company.com" required />
                  </label>
                  <label>
                    Password
                    <input type="password" name="password" placeholder="Enter password" required />
                  </label>
                  <div class="form-span access-toggle-grid">
                    <label class="toggle-option">
                      <input type="checkbox" name="rememberSession" checked />
                      Keep this device signed in
                    </label>
                  </div>
                  <button class="button button-primary" type="submit">Login to Dashboard</button>
                  <p class="form-note" id="login-ui-note" aria-live="polite"></p>
                </form>
              </div>
              <div class="access-card">
                <p class="company-kicker">Workspace Scope</p>
                <ul class="access-list">
                  <li>Manage personal profile, links, and visibility</li>
                  <li>Track newsletter and opportunity alert preferences</li>
                  <li>Review assigned work, applications, and requested access</li>
                  <li>Move into company controls after approval</li>
                </ul>
              </div>
            </section>

            <section class="access-pane is-hidden" data-access-pane="signup">
              <div class="access-card">
                <p class="company-kicker">Account Creation</p>
                <h3>Create a talent or company-ready account</h3>
                <form class="access-form" id="signup-ui-form">
                  <label>
                    Full Name
                    <input type="text" name="fullName" placeholder="Your full name" required />
                  </label>
                  <label>
                    Email
                    <input type="email" name="email" placeholder="you@example.com" required />
                  </label>
                  <label>
                    Password
                    <input type="password" name="password" placeholder="Create password" required />
                  </label>
                  <label>
                    Account Type
                    <select name="accountType" required>
                      <option value="Talent">Talent</option>
                      <option value="Company">Company</option>
                      <option value="Both">Both</option>
                    </select>
                  </label>
                  <div class="form-span access-toggle-grid">
                    <label class="toggle-option">
                      <input type="checkbox" name="newsletter" checked />
                      Receive newsletter updates
                    </label>
                    <label class="toggle-option">
                      <input type="checkbox" name="opportunityAlerts" checked />
                      Receive new opportunity alerts
                    </label>
                  </div>
                  <button class="button button-primary" type="submit">Create Account</button>
                  <p class="form-note" id="signup-ui-note" aria-live="polite"></p>
                </form>
              </div>
              <div class="access-card">
                <p class="company-kicker">Onboarding Flow</p>
                <ul class="access-list">
                  <li>Create account with role type and email preferences</li>
                  <li>Receive confirmation and verification emails</li>
                  <li>Complete profile status, city, links, and skills</li>
                  <li>Request company-level access when needed</li>
                </ul>
              </div>
            </section>

            <section class="access-pane is-hidden" data-access-pane="reset">
              <div class="access-card">
                <p class="company-kicker">Password Reset</p>
                <h3>Send recovery instructions</h3>
                <form class="access-form" id="reset-ui-form">
                  <label>
                    Email
                    <input type="email" name="email" placeholder="you@example.com" required />
                  </label>
                  <label>
                    Reset Reason
                    <select name="reason" required>
                      <option value="Forgot password">Forgot password</option>
                      <option value="Lost device">Lost device</option>
                      <option value="Security update">Security update</option>
                    </select>
                  </label>
                  <button class="button button-primary" type="submit">Send Reset Email</button>
                  <p class="form-note" id="reset-ui-note" aria-live="polite"></p>
                </form>
              </div>
              <div class="access-card">
                <p class="company-kicker">Recovery Notes</p>
                <ul class="access-list">
                  <li>Reset email should carry a single-use secure link</li>
                  <li>Links should expire and require a fresh request after timeout</li>
                  <li>Password updates should confirm success with a follow-up email</li>
                </ul>
              </div>
            </section>

            <section class="access-pane is-hidden" data-access-pane="verify">
              <div class="access-card">
                <p class="company-kicker">Email Confirmation</p>
                <h3>Verify account access</h3>
                <form class="access-form" id="verify-ui-form">
                  <label>
                    Email
                    <input type="email" name="email" placeholder="you@example.com" required />
                  </label>
                  <label>
                    Verification Code
                    <input type="text" name="code" placeholder="Enter code from email" required />
                  </label>
                  <button class="button button-primary" type="submit">Verify Email</button>
                  <p class="form-note" id="verify-ui-note" aria-live="polite"></p>
                </form>
              </div>
              <div class="access-card">
                <p class="company-kicker">Styled Message Flow</p>
                <div class="message-flow">
                  <article class="message-card">
                    <span class="message-state message-state--waiting" id="flowAccountStatus">Pending</span>
                    <strong>Create account</strong>
                    <p id="flowAccountDetail">Signup confirmation will appear here once an account is created.</p>
                  </article>
                  <article class="message-card">
                    <span class="message-state message-state--waiting" id="flowVerifyStatus">Pending</span>
                    <strong>Verify email</strong>
                    <p id="flowVerifyDetail">Verification email subject and delivery note will update here.</p>
                  </article>
                  <article class="message-card">
                    <span class="message-state message-state--waiting" id="flowResetStatus">Pending</span>
                    <strong>Password reset</strong>
                    <p id="flowResetDetail">Recovery messages will update here after a reset request.</p>
                  </article>
                  <article class="message-card">
                    <span class="message-state message-state--waiting" id="flowCompanyStatus">Pending</span>
                    <strong>Company access review</strong>
                    <p id="flowCompanyDetail">Approval routing for company access requests will appear here.</p>
                  </article>
                </div>
              </div>
            </section>

            <section class="access-pane is-hidden" data-access-pane="profile">
              <div class="access-card">
                <p class="company-kicker">Profile Settings</p>
                <h3>Set preferences, links, skills, and resume markup</h3>
                <form class="access-form" id="profile-ui-form">
                  <label>
                    Full Name
                    <input type="text" name="fullName" placeholder="Your full name" required />
                  </label>
                  <label>
                    Role
                    <input type="text" name="role" placeholder="Frontend Engineer, Designer, etc." required />
                  </label>
                  <label>
                    City Location
                    <input type="text" name="city" placeholder="City, State" />
                  </label>
                  <label>
                    Availability
                    <input type="text" name="availability" placeholder="Open for sprint work, full time, etc." />
                  </label>
                  <label>
                    Profile Status
                    <select name="profileStatus">
                      <option value="Public">Public</option>
                      <option value="Private">Private</option>
                      <option value="Invite Only">Invite Only</option>
                    </select>
                  </label>
                  <label>
                    Portfolio URL
                    <input type="url" name="portfolio" placeholder="https://portfolio.example.com" />
                  </label>
                  <label>
                    LinkedIn
                    <input type="url" name="linkedin" placeholder="https://linkedin.com/in/username" />
                  </label>
                  <label>
                    GitHub
                    <input type="url" name="github" placeholder="https://github.com/username" />
                  </label>
                  <div class="form-span access-toggle-grid">
                    <label class="toggle-option">
                      <input type="checkbox" name="newsletter" checked />
                      Receive newsletter
                    </label>
                    <label class="toggle-option">
                      <input type="checkbox" name="jobAlerts" checked />
                      Receive opportunity alerts
                    </label>
                    <label class="toggle-option">
                      <input type="checkbox" name="publicResume" checked />
                      Show resume on public profile
                    </label>
                  </div>
                  <label class="form-span">
                    Bio
                    <textarea name="bio" rows="4" placeholder="Short professional bio" required></textarea>
                  </label>
                  <label class="form-span">
                    Skills Manager
                    <textarea name="skills" rows="4" placeholder="One skill per line" required></textarea>
                  </label>
                  <label class="form-span">
                    Preferences
                    <textarea name="preferences" rows="3" placeholder="Remote, contract, leadership, product systems"></textarea>
                  </label>
                  <label class="form-span">
                    Resume HTML
                    <textarea name="resumeHtml" rows="5" placeholder="<section><h1>Your Resume</h1><p>Highlights...</p></section>"></textarea>
                  </label>
                  <button class="button button-primary" type="submit">Save Profile UI</button>
                  <p class="form-note" id="profile-ui-note" aria-live="polite"></p>
                </form>
              </div>
              <div class="access-card">
                <p class="company-kicker">Live Profile Preview</p>
                <div class="profile-preview">
                  <div class="profile-meta-row">
                    <span class="focus-chip" id="profilePreviewStatus">Public</span>
                    <span class="focus-chip" id="profilePreviewCity">City not set</span>
                    <span class="focus-chip" id="profilePreviewNewsletter">Newsletter on</span>
                  </div>
                  <h3 id="profilePreviewName">Your Name</h3>
                  <p class="project-company" id="profilePreviewRole">Role and availability</p>
                  <p id="profilePreviewBio">Profile summary updates as the form changes.</p>
                  <div class="project-tag-group">
                    <strong>Skills</strong>
                    <div class="chip-cloud" id="skillsManagerPreview"></div>
                  </div>
                  <div class="project-tag-group">
                    <strong>Links</strong>
                    <div class="chip-cloud" id="profileLinksPreview"></div>
                  </div>
                  <div class="project-tag-group">
                    <strong>Preferences</strong>
                    <div class="chip-cloud" id="profilePreferencesPreview"></div>
                  </div>
                  <div class="profile-resume-preview">
                    <strong>Resume HTML</strong>
                    <pre class="resume-code" id="resumeHtmlPreview">&lt;section&gt;Resume markup preview&lt;/section&gt;</pre>
                  </div>
                </div>
              </div>
            </section>

            <section class="access-pane is-hidden" data-access-pane="company">
              <div class="access-card">
                <p class="company-kicker">Company Ownership Request</p>
                <h3>Request company profile access</h3>
                <form class="access-form" id="company-access-form">
                  <label>
                    Company Name
                    <input type="text" name="companyName" placeholder="Company requesting access" required />
                  </label>
                  <label>
                    Work Email
                    <input type="email" name="companyEmail" placeholder="name@company.com" required />
                  </label>
                  <label>
                    Requested Role
                    <input type="text" name="requestedRole" placeholder="Owner, Admin, Hiring Lead" required />
                  </label>
                  <label>
                    Company URL
                    <input type="url" name="companyUrl" placeholder="https://company.com" />
                  </label>
                  <label class="form-span">
                    Request Details
                    <textarea name="requestDetails" rows="4" placeholder="Why you need company access and what records you need to manage" required></textarea>
                  </label>
                  <button class="button button-primary" type="submit">Request Company Access</button>
                  <p class="form-note" id="company-access-note" aria-live="polite"></p>
                </form>
              </div>
              <div class="access-card">
                <p class="company-kicker">Approval Route</p>
                <ul class="access-list">
                  <li>Request captured with company contact and requested role</li>
                  <li>Ownership review checks company identity and management scope</li>
                  <li>Approved accounts can manage projects, opportunities, and skills</li>
                </ul>
              </div>
            </section>
          </div>
        </div>
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
