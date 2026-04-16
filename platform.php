<?php
declare(strict_types=1);
require __DIR__ . '/includes/bootstrap.php';
// Try to get current user — auth.php may not exist yet, so wrap in file_exists check
$platformUser = null;
if (file_exists(__DIR__ . '/includes/auth.php')) {
    require_once __DIR__ . '/includes/auth.php';
    $platformUser = fw_get_session_user();
}
$isAuthenticated = $platformUser !== null;
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Frowear Platform</title>
    <meta name="description" content="Frowear Productions platform — feed, messages, bids, and profile." />
    <meta name="theme-color" content="#4be7ff" />
    <meta name="mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
    <meta name="apple-mobile-web-app-title" content="Frowear" />
    <meta property="og:title" content="Frowear Platform" />
    <meta property="og:description" content="Frowear Productions platform for talent, companies, and collaboration." />
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
    <link rel="stylesheet" href="platform.css" />
  </head>
  <body data-auth="<?= $isAuthenticated ? 'true' : 'false' ?>">

    <!-- ===================== AUTH WALL ===================== -->
    <div class="pf-auth-wall" id="pfAuthWall">
      <div class="pf-auth-container">
        <a class="pf-auth-brand" href="index.php">← Back to site</a>
        <div class="pf-auth-card">
          <!-- Tab switcher -->
          <div class="pf-auth-tabs">
            <button class="pf-auth-tab is-active" data-auth-view="login" type="button">Login</button>
            <button class="pf-auth-tab" data-auth-view="register" type="button">Register</button>
            <button class="pf-auth-tab" data-auth-view="forgot" type="button">Forgot</button>
          </div>

          <!-- Login form -->
          <form class="pf-auth-form" id="pfLoginForm" data-auth-view="login">
            <p class="eyebrow">Welcome back</p>
            <h2>Sign in to your workspace</h2>
            <label>
              Email
              <input type="email" name="email" required placeholder="you@example.com" autocomplete="email" />
            </label>
            <label>
              Password
              <input type="password" name="password" required placeholder="Your password" autocomplete="current-password" />
            </label>
            <button class="button button-primary" type="submit">Sign In</button>
            <p class="pf-auth-note" id="pfLoginNote" aria-live="polite"></p>
          </form>

          <!-- Register form -->
          <form class="pf-auth-form is-hidden" id="pfRegisterForm" data-auth-view="register">
            <p class="eyebrow">Join the platform</p>
            <h2>Create your account</h2>
            <label>
              Display Name
              <input type="text" name="display_name" required placeholder="Your name" autocomplete="name" />
            </label>
            <label>
              Email
              <input type="email" name="email" required placeholder="you@example.com" autocomplete="email" />
            </label>
            <label>
              Password
              <input type="password" name="password" required placeholder="Create a strong password" autocomplete="new-password" />
            </label>
            <label>
              Role
              <select name="role">
                <option value="talent">Talent</option>
                <option value="company_owner">Company Owner</option>
              </select>
            </label>
            <button class="button button-primary" type="submit">Create Account</button>
            <p class="pf-auth-note" id="pfRegisterNote" aria-live="polite"></p>
          </form>

          <!-- Forgot password form -->
          <form class="pf-auth-form is-hidden" id="pfForgotForm" data-auth-view="forgot">
            <p class="eyebrow">Account recovery</p>
            <h2>Reset your password</h2>
            <label>
              Email
              <input type="email" name="email" required placeholder="you@example.com" autocomplete="email" />
            </label>
            <button class="button button-primary" type="submit">Send Reset Link</button>
            <p class="pf-auth-note" id="pfForgotNote" aria-live="polite"></p>
          </form>
        </div>
      </div>
    </div>

    <!-- ===================== PLATFORM APP ===================== -->
    <div class="pf-app" id="pfApp">

      <!-- Sidebar nav (desktop) -->
      <nav class="pf-sidebar" id="pfSidebar" aria-label="Platform navigation">
        <div class="pf-sidebar-brand">
          <span class="brand-mark">FP</span>
          <strong>Frowear</strong>
        </div>

        <div class="pf-sidebar-nav">
          <button class="pf-nav-item is-active" data-view="feed" type="button" aria-current="page">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>
            </svg>
            Feed
          </button>
          <button class="pf-nav-item" data-view="messages" type="button">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
            Messages
            <span class="pf-badge" id="pfMsgBadge" hidden>0</span>
          </button>
          <button class="pf-nav-item" data-view="bids" type="button">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
            Bids
          </button>
          <button class="pf-nav-item" data-view="notifications" type="button">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
            Notifications
            <span class="pf-badge" id="pfNotifBadge" hidden>0</span>
          </button>
          <button class="pf-nav-item" data-view="profile" type="button">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
            </svg>
            Profile
          </button>
        </div>

        <div class="pf-sidebar-footer">
          <a href="index.php" class="pf-sidebar-link">← Main site</a>
          <button class="pf-sidebar-link" id="pfLogoutBtn" type="button">Sign out</button>
        </div>
      </nav>

      <!-- Mobile header -->
      <header class="pf-mobile-header" aria-label="Mobile platform navigation">
        <span class="brand-mark">FP</span>
        <nav class="pf-mobile-nav" aria-label="Mobile navigation">
          <button class="pf-nav-item is-active" data-view="feed" type="button">Feed</button>
          <button class="pf-nav-item" data-view="messages" type="button">
            Msgs
            <span class="pf-badge" id="pfMsgBadgeMobile" hidden></span>
          </button>
          <button class="pf-nav-item" data-view="bids" type="button">Bids</button>
          <button class="pf-nav-item" data-view="notifications" type="button">
            Notifs
            <span class="pf-badge" id="pfNotifBadgeMobile" hidden></span>
          </button>
          <button class="pf-nav-item" data-view="profile" type="button">Profile</button>
        </nav>
      </header>

      <!-- Main content area -->
      <main class="pf-main" id="pfMain">

        <!-- FEED VIEW -->
        <section class="pf-view is-active" data-view="feed" aria-label="Feed">
          <div class="pf-view-header">
            <h2>Feed</h2>
            <div class="pf-feed-filters" id="pfFeedFilters"></div>
          </div>

          <!-- Post composer -->
          <div class="pf-composer" id="pfComposer">
            <form id="pfPostForm">
              <textarea name="body" rows="3" placeholder="Share an update, project, opportunity, or achievement…" required></textarea>
              <div class="pf-composer-footer">
                <select name="post_type" id="pfPostType" aria-label="Post type">
                  <option value="update">Update</option>
                  <option value="opportunity">Opportunity</option>
                  <option value="project">Project</option>
                  <option value="event">Event</option>
                  <option value="collaboration">Collaboration</option>
                  <option value="skill_share">Skill Share</option>
                  <option value="news">News</option>
                  <option value="celebration">Celebration</option>
                  <option value="achievement">Achievement</option>
                </select>
                <select name="visibility" aria-label="Visibility">
                  <option value="public">Public</option>
                  <option value="connections">Connections</option>
                  <option value="private">Only me</option>
                </select>
                <button class="button button-primary" type="submit">Post</button>
              </div>
            </form>
          </div>

          <!-- Feed list -->
          <div class="pf-feed-list" id="pfFeedList">
            <div class="pf-loading">Loading feed…</div>
          </div>
          <button class="pf-load-more" id="pfFeedLoadMore" hidden type="button">Load more</button>
        </section>

        <!-- MESSAGES VIEW -->
        <section class="pf-view is-hidden" data-view="messages" aria-label="Messages">
          <div class="pf-messages-layout">
            <div class="pf-conversation-list" id="pfConversationList">
              <div class="pf-view-header">
                <h2>Messages</h2>
                <button class="ghost-button" id="pfNewConvoBtn" type="button">+ New</button>
              </div>
              <div id="pfConvos"><div class="pf-loading">Loading…</div></div>
            </div>
            <div class="pf-thread" id="pfThread">
              <div class="pf-thread-empty">Select a conversation</div>
            </div>
          </div>
        </section>

        <!-- BIDS VIEW -->
        <section class="pf-view is-hidden" data-view="bids" aria-label="Bids">
          <div class="pf-view-header">
            <h2>Bids</h2>
            <button class="button button-primary" id="pfNewBidBtn" type="button">Place a Bid</button>
          </div>
          <div class="pf-bids-tabs">
            <button class="pf-tab is-active" data-bids-tab="received" type="button">Received</button>
            <button class="pf-tab" data-bids-tab="sent" type="button">Sent</button>
            <button class="pf-tab" data-bids-tab="contracts" type="button">Contracts</button>
          </div>
          <div id="pfBidsList"><div class="pf-loading">Loading…</div></div>
        </section>

        <!-- NOTIFICATIONS VIEW -->
        <section class="pf-view is-hidden" data-view="notifications" aria-label="Notifications">
          <div class="pf-view-header">
            <h2>Notifications</h2>
            <button class="ghost-button" id="pfMarkAllRead" type="button">Mark all read</button>
          </div>
          <div id="pfNotifList"><div class="pf-loading">Loading…</div></div>
        </section>

        <!-- PROFILE VIEW -->
        <section class="pf-view is-hidden" data-view="profile" aria-label="Profile">
          <div id="pfProfileContent"><div class="pf-loading">Loading…</div></div>
        </section>

      </main>
    </div>

    <script>
      window.__PF_USER__ = <?= $isAuthenticated ? json_encode(['id' => $platformUser['id'], 'display_name' => $platformUser['display_name'], 'email' => $platformUser['email'], 'role' => $platformUser['role'], 'avatar_url' => $platformUser['avatar_url'] ?? null], JSON_UNESCAPED_SLASHES) : 'null' ?>;
      window.__PF_AUTHENTICATED__ = <?= $isAuthenticated ? 'true' : 'false' ?>;
    </script>
    <script src="platform.js"></script>
  </body>
</html>
