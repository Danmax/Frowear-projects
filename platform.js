/* ============================================================
   platform.js — Frowear Platform SPA
   Vanilla JS, no dependencies. Works alongside style.css and platform.css.
   ============================================================ */

(function () {
  'use strict';

  /* ----------------------------------------------------------
     Constants
  ---------------------------------------------------------- */

  const API_BASE = 'api/';

  const POST_TYPE_LABELS = {
    update:        'Update',
    opportunity:   'Opportunity',
    project:       'Project',
    event:         'Event',
    collaboration: 'Collaboration',
    skill_share:   'Skill Share',
    news:          'News',
    celebration:   'Celebration',
    achievement:   'Achievement',
  };

  const FEED_FILTERS = ['All', ...Object.values(POST_TYPE_LABELS)];

  /* ----------------------------------------------------------
     State
  ---------------------------------------------------------- */

  const state = {
    user: window.__PF_USER__ || null,
    authenticated: window.__PF_AUTHENTICATED__ === true,
    currentView: 'feed',
    feedFilter: 'All',
    feedPage: 1,
    feedDone: false,
    feedPosts: [],
    activeConvoId: null,
    pollInterval: null,
    unreadMessages: 0,
    unreadNotifs: 0,
    bidsTab: 'received',
  };

  /* ----------------------------------------------------------
     DOM refs
  ---------------------------------------------------------- */

  const body               = document.body;
  const pfAuthWall         = document.getElementById('pfAuthWall');
  const pfApp              = document.getElementById('pfApp');

  // Auth
  const pfLoginForm        = document.getElementById('pfLoginForm');
  const pfRegisterForm     = document.getElementById('pfRegisterForm');
  const pfForgotForm       = document.getElementById('pfForgotForm');
  const pfLoginNote        = document.getElementById('pfLoginNote');
  const pfRegisterNote     = document.getElementById('pfRegisterNote');
  const pfForgotNote       = document.getElementById('pfForgotNote');

  // Nav
  const pfLogoutBtn        = document.getElementById('pfLogoutBtn');
  const pfMsgBadge         = document.getElementById('pfMsgBadge');
  const pfNotifBadge       = document.getElementById('pfNotifBadge');
  const pfMsgBadgeMobile   = document.getElementById('pfMsgBadgeMobile');
  const pfNotifBadgeMobile = document.getElementById('pfNotifBadgeMobile');

  // Feed
  const pfFeedFilters      = document.getElementById('pfFeedFilters');
  const pfPostForm         = document.getElementById('pfPostForm');
  const pfFeedList         = document.getElementById('pfFeedList');
  const pfFeedLoadMore     = document.getElementById('pfFeedLoadMore');

  // Messages
  const pfConvos           = document.getElementById('pfConvos');
  const pfConversationList = document.getElementById('pfConversationList');
  const pfThread           = document.getElementById('pfThread');
  const pfNewConvoBtn      = document.getElementById('pfNewConvoBtn');

  // Bids
  const pfBidsList         = document.getElementById('pfBidsList');
  const pfNewBidBtn        = document.getElementById('pfNewBidBtn');

  // Notifications
  const pfNotifList        = document.getElementById('pfNotifList');
  const pfMarkAllRead      = document.getElementById('pfMarkAllRead');

  // Profile
  const pfProfileContent   = document.getElementById('pfProfileContent');

  /* ----------------------------------------------------------
     Utilities
  ---------------------------------------------------------- */

  function esc(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;');
  }

  function initials(name) {
    if (!name) return '?';
    return name
      .trim()
      .split(/\s+/)
      .slice(0, 2)
      .map(w => w[0].toUpperCase())
      .join('');
  }

  function relativeTime(dateStr) {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    if (isNaN(d)) return '';
    const diff = Math.floor((Date.now() - d.getTime()) / 1000);
    if (diff < 60) return 'just now';
    if (diff < 3600) return Math.floor(diff / 60) + 'm ago';
    if (diff < 86400) return Math.floor(diff / 3600) + 'h ago';
    if (diff < 604800) return Math.floor(diff / 86400) + 'd ago';
    return d.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
  }

  function setNote(el, msg, type) {
    if (!el) return;
    el.textContent = msg;
    el.className = 'pf-auth-note' + (type ? ' is-' + type : '');
  }

  async function apiFetch(path, options) {
    try {
      const res = await fetch(API_BASE + path, {
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        credentials: 'same-origin',
        ...options,
      });
      const data = await res.json().catch(() => ({}));
      return { ok: res.ok, status: res.status, data };
    } catch (err) {
      return { ok: false, status: 0, data: { message: 'Network error.' } };
    }
  }

  async function apiPost(path, body) {
    return apiFetch(path, {
      method: 'POST',
      body: JSON.stringify(body),
    });
  }

  /* ----------------------------------------------------------
     Auth — tab switching
  ---------------------------------------------------------- */

  function initAuthTabs() {
    const tabs = pfAuthWall ? pfAuthWall.querySelectorAll('.pf-auth-tab') : [];
    const forms = pfAuthWall ? pfAuthWall.querySelectorAll('.pf-auth-form') : [];

    tabs.forEach(tab => {
      tab.addEventListener('click', () => {
        const view = tab.dataset.authView;
        tabs.forEach(t => t.classList.toggle('is-active', t.dataset.authView === view));
        forms.forEach(f => f.classList.toggle('is-hidden', f.dataset.authView !== view));
        // Clear notes
        [pfLoginNote, pfRegisterNote, pfForgotNote].forEach(n => {
          if (n) n.textContent = '';
        });
      });
    });
  }

  /* ----------------------------------------------------------
     Auth — login
  ---------------------------------------------------------- */

  function initLoginForm() {
    if (!pfLoginForm) return;
    pfLoginForm.addEventListener('submit', async e => {
      e.preventDefault();
      const btn = pfLoginForm.querySelector('button[type="submit"]');
      btn.disabled = true;
      btn.textContent = 'Signing in…';
      setNote(pfLoginNote, '', '');

      const fd = new FormData(pfLoginForm);
      const result = await apiPost('auth/login', {
        email: fd.get('email'),
        password: fd.get('password'),
      });

      if (result.ok && result.data.ok) {
        setNote(pfLoginNote, 'Signed in! Loading…', 'success');
        window.location.reload();
      } else {
        setNote(pfLoginNote, result.data.message || 'Login failed.', 'error');
        btn.disabled = false;
        btn.textContent = 'Sign In';
      }
    });
  }

  /* ----------------------------------------------------------
     Auth — register
  ---------------------------------------------------------- */

  function initRegisterForm() {
    if (!pfRegisterForm) return;
    pfRegisterForm.addEventListener('submit', async e => {
      e.preventDefault();
      const btn = pfRegisterForm.querySelector('button[type="submit"]');
      btn.disabled = true;
      btn.textContent = 'Creating…';
      setNote(pfRegisterNote, '', '');

      const fd = new FormData(pfRegisterForm);
      const result = await apiPost('auth/register', {
        email: fd.get('email'),
        password: fd.get('password'),
        display_name: fd.get('display_name'),
        role: fd.get('role'),
      });

      if (result.ok && result.data.ok) {
        setNote(pfRegisterNote, 'Account created! Signing you in…', 'success');
        window.location.reload();
      } else {
        setNote(pfRegisterNote, result.data.message || 'Registration failed.', 'error');
        btn.disabled = false;
        btn.textContent = 'Create Account';
      }
    });
  }

  /* ----------------------------------------------------------
     Auth — forgot password
  ---------------------------------------------------------- */

  function initForgotForm() {
    if (!pfForgotForm) return;
    pfForgotForm.addEventListener('submit', async e => {
      e.preventDefault();
      const btn = pfForgotForm.querySelector('button[type="submit"]');
      btn.disabled = true;
      btn.textContent = 'Sending…';
      setNote(pfForgotNote, '', '');

      const fd = new FormData(pfForgotForm);
      const result = await apiPost('auth/forgot', {
        email: fd.get('email'),
      });

      if (result.ok) {
        setNote(pfForgotNote, result.data.message || 'If that email is registered, a reset link has been sent.', 'success');
        pfForgotForm.reset();
      } else {
        setNote(pfForgotNote, result.data.message || 'Request failed.', 'error');
      }
      btn.disabled = false;
      btn.textContent = 'Send Reset Link';
    });
  }

  /* ----------------------------------------------------------
     Logout
  ---------------------------------------------------------- */

  function initLogout() {
    if (!pfLogoutBtn) return;
    pfLogoutBtn.addEventListener('click', async () => {
      await apiPost('auth/logout', {});
      window.location.reload();
    });
  }

  /* ----------------------------------------------------------
     View navigation
  ---------------------------------------------------------- */

  function switchView(view) {
    if (view === state.currentView && view !== 'feed') return;
    state.currentView = view;

    const views = document.querySelectorAll('.pf-view');
    views.forEach(v => {
      const isTarget = v.dataset.view === view;
      v.classList.toggle('is-active', isTarget);
      v.classList.toggle('is-hidden', !isTarget);
    });

    const allNavItems = document.querySelectorAll('.pf-nav-item[data-view]');
    allNavItems.forEach(btn => {
      btn.classList.toggle('is-active', btn.dataset.view === view);
      if (btn.hasAttribute('aria-current')) {
        btn.setAttribute('aria-current', btn.dataset.view === view ? 'page' : 'false');
      }
    });

    // Load data for view
    if (view === 'feed') loadFeed(true);
    if (view === 'messages') loadConversations();
    if (view === 'bids') loadBids();
    if (view === 'notifications') loadNotifications();
    if (view === 'profile') loadProfile();
  }

  function initNavigation() {
    document.querySelectorAll('.pf-nav-item[data-view]').forEach(btn => {
      btn.addEventListener('click', () => switchView(btn.dataset.view));
    });
  }

  /* ----------------------------------------------------------
     Feed — filter chips
  ---------------------------------------------------------- */

  function renderFeedFilters() {
    if (!pfFeedFilters) return;
    pfFeedFilters.innerHTML = '';
    FEED_FILTERS.forEach(label => {
      const btn = document.createElement('button');
      btn.className = 'pf-filter-chip' + (label === state.feedFilter ? ' is-active' : '');
      btn.type = 'button';
      btn.textContent = label;
      btn.addEventListener('click', () => {
        state.feedFilter = label;
        document.querySelectorAll('.pf-filter-chip').forEach(c => {
          c.classList.toggle('is-active', c.textContent === label);
        });
        loadFeed(true);
      });
      pfFeedFilters.appendChild(btn);
    });
  }

  /* ----------------------------------------------------------
     Feed — load posts
  ---------------------------------------------------------- */

  async function loadFeed(reset) {
    if (!pfFeedList) return;
    if (reset) {
      state.feedPage = 1;
      state.feedDone = false;
      state.feedPosts = [];
      pfFeedList.innerHTML = '<div class="pf-loading">Loading feed…</div>';
      if (pfFeedLoadMore) pfFeedLoadMore.hidden = true;
    }

    const params = new URLSearchParams({ page: state.feedPage });
    if (state.feedFilter !== 'All') {
      const typeKey = Object.entries(POST_TYPE_LABELS).find(([, v]) => v === state.feedFilter)?.[0];
      if (typeKey) params.set('type', typeKey);
    }

    const result = await apiFetch('feed/posts?' + params.toString());
    if (!result.ok) {
      if (reset) pfFeedList.innerHTML = '<div class="pf-empty"><strong>Could not load feed.</strong><span>The feed API is not yet available.</span></div>';
      return;
    }

    const posts = Array.isArray(result.data.data) ? result.data.data : [];
    const hasMore = result.data.page < result.data.pages;

    state.feedPosts = reset ? posts : [...state.feedPosts, ...posts];
    state.feedDone = !hasMore;

    if (reset) pfFeedList.innerHTML = '';
    if (state.feedPosts.length === 0) {
      pfFeedList.innerHTML = '<div class="pf-empty"><strong>Nothing here yet.</strong><span>Be the first to post something.</span></div>';
      return;
    }

    posts.forEach(post => pfFeedList.appendChild(buildPostCard(post)));
    if (pfFeedLoadMore) pfFeedLoadMore.hidden = !hasMore;
  }

  function buildPostCard(post) {
    const card = document.createElement('article');
    card.className = 'pf-post-card';
    card.dataset.postId = post.id;

    const name = esc(post.display_name || post.author || 'Unknown');
    const typeKey = post.post_type || 'update';
    const typeLabel = POST_TYPE_LABELS[typeKey] || typeKey;
    const avatarLetter = initials(post.display_name || post.author || '?');
    const avatarSrc = post.avatar_url ? `<img src="${esc(post.avatar_url)}" alt="${name}" />` : avatarLetter;

    card.innerHTML = `
      <div class="pf-post-header">
        <div class="pf-post-avatar">${avatarSrc}</div>
        <div class="pf-post-meta">
          <span class="pf-post-author">${name}</span>
          <span class="pf-post-time">${relativeTime(post.created_at)}</span>
          <span class="pf-post-type-chip pf-post-type-chip--${esc(typeKey)}">${esc(typeLabel)}</span>
        </div>
      </div>
      <div class="pf-post-body">${esc(post.body || '')}</div>
      <div class="pf-post-actions">
        <button class="pf-action-btn pf-like-btn${post.liked_by_me ? ' is-active' : ''}" data-post-id="${post.id}" type="button">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="${post.liked_by_me ? 'currentColor' : 'none'}" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
          <span>${post.likes_count || 0}</span>
        </button>
        <button class="pf-action-btn pf-comment-toggle-btn" data-post-id="${post.id}" type="button">
          <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
          <span>${post.comments_count || 0}</span>
        </button>
      </div>
      <div class="pf-comments-section is-hidden" id="pf-comments-${post.id}">
        <div class="pf-comments-list"></div>
        <form class="pf-comment-form" data-post-id="${post.id}">
          <input class="pf-comment-input" type="text" placeholder="Write a comment…" required />
          <button class="button button-primary" type="submit" style="min-height:2.4rem;padding:0.55rem 1rem;">Post</button>
        </form>
      </div>
    `;

    // Like
    card.querySelector('.pf-like-btn').addEventListener('click', () => toggleLike(post.id, card));

    // Comments toggle
    card.querySelector('.pf-comment-toggle-btn').addEventListener('click', () => {
      const section = document.getElementById('pf-comments-' + post.id);
      if (!section) return;
      const isOpen = !section.classList.contains('is-hidden');
      section.classList.toggle('is-hidden', isOpen);
      if (!isOpen) loadComments(post.id, section.querySelector('.pf-comments-list'));
    });

    // Comment submit
    card.querySelector('.pf-comment-form').addEventListener('submit', e => {
      e.preventDefault();
      const input = e.target.querySelector('.pf-comment-input');
      submitComment(post.id, input.value.trim(), card, input);
    });

    return card;
  }

  async function toggleLike(postId, card) {
    const btn = card.querySelector('.pf-like-btn[data-post-id="' + postId + '"]');
    const isLiked = btn.classList.contains('is-active');
    btn.disabled = true;

    const result = await apiPost('feed/react', { post_id: postId, reaction: 'like' });
    if (result.ok) {
      btn.classList.toggle('is-active', !isLiked);
      const count = btn.querySelector('span');
      if (count) count.textContent = result.data.likes_count ?? (isLiked ? parseInt(count.textContent, 10) - 1 : parseInt(count.textContent, 10) + 1);
      const svgPath = btn.querySelector('path');
      if (svgPath) svgPath.setAttribute('fill', !isLiked ? 'currentColor' : 'none');
    }
    btn.disabled = false;
  }

  async function loadComments(postId, container) {
    if (!container) return;
    container.innerHTML = '<div class="pf-loading" style="padding:0.75rem 0;">Loading…</div>';
    const result = await apiFetch('feed/comments?post_id=' + postId);
    container.innerHTML = '';
    if (!result.ok || !Array.isArray(result.data.data)) return;
    result.data.data.forEach(c => container.appendChild(buildComment(c)));
  }

  function buildComment(comment) {
    const wrap = document.createElement('div');
    wrap.className = 'pf-comment';
    const name = esc(comment.display_name || comment.author || 'Unknown');
    const avatarLetter = initials(comment.display_name || comment.author || '?');
    const avatarSrc = comment.avatar_url ? `<img src="${esc(comment.avatar_url)}" alt="${name}" />` : avatarLetter;
    wrap.innerHTML = `
      <div class="pf-comment-avatar">${avatarSrc}</div>
      <div class="pf-comment-body">
        <span class="pf-comment-author">${name}</span>
        <span class="pf-comment-text">${esc(comment.body || '')}</span>
        <span class="pf-comment-time">${relativeTime(comment.created_at)}</span>
      </div>
    `;
    return wrap;
  }

  async function submitComment(postId, body, card, input) {
    if (!body) return;
    const result = await apiPost('feed/comments', { post_id: postId, body });
    if (result.ok && result.data.comment) {
      const section = document.getElementById('pf-comments-' + postId);
      const list = section ? section.querySelector('.pf-comments-list') : null;
      if (list) list.appendChild(buildComment(result.data.comment));
      input.value = '';
      const countSpan = card.querySelector('.pf-comment-toggle-btn span');
      if (countSpan) countSpan.textContent = parseInt(countSpan.textContent, 10) + 1;
    }
  }

  function initPostForm() {
    if (!pfPostForm) return;
    pfPostForm.addEventListener('submit', async e => {
      e.preventDefault();
      const btn = pfPostForm.querySelector('button[type="submit"]');
      btn.disabled = true;
      btn.textContent = 'Posting…';

      const fd = new FormData(pfPostForm);
      const result = await apiPost('feed/posts', {
        body: fd.get('body'),
        post_type: fd.get('post_type'),
        visibility: fd.get('visibility'),
      });

      if (result.ok && result.data.post) {
        pfPostForm.reset();
        const card = buildPostCard(result.data.post);
        card.style.animation = 'none';
        pfFeedList.insertBefore(card, pfFeedList.firstChild);
        const empty = pfFeedList.querySelector('.pf-empty');
        if (empty) empty.remove();
      }
      btn.disabled = false;
      btn.textContent = 'Post';
    });
  }

  function initFeedLoadMore() {
    if (!pfFeedLoadMore) return;
    pfFeedLoadMore.addEventListener('click', () => {
      state.feedPage += 1;
      loadFeed(false);
    });
  }

  /* ----------------------------------------------------------
     Messages
  ---------------------------------------------------------- */

  async function loadConversations() {
    if (!pfConvos) return;
    pfConvos.innerHTML = '<div class="pf-loading">Loading…</div>';
    const result = await apiFetch('messages/conversations');
    pfConvos.innerHTML = '';

    if (!result.ok || !Array.isArray(result.data.data)) {
      pfConvos.innerHTML = '<div class="pf-empty"><strong>No conversations yet.</strong></div>';
      return;
    }

    const convos = result.data.data;
    if (convos.length === 0) {
      pfConvos.innerHTML = '<div class="pf-empty"><strong>No conversations yet.</strong><span>Start a new one above.</span></div>';
      return;
    }

    convos.forEach(convo => {
      const item = document.createElement('div');
      item.className = 'pf-conv-item' + (convo.id === state.activeConvoId ? ' is-active' : '');
      item.dataset.convoId = convo.id;

      const otherName = esc(convo.other_display_name || convo.other_name || 'Unknown');
      const preview = esc((convo.last_message_body || '').substring(0, 72));
      const time = relativeTime(convo.last_message_at || convo.updated_at);

      item.innerHTML = `
        <div class="pf-conv-name">${otherName}</div>
        <div class="pf-conv-preview">${preview || 'No messages yet.'}</div>
        <div class="pf-conv-time">${time}</div>
      `;
      item.addEventListener('click', () => openConversation(convo));
      pfConvos.appendChild(item);
    });
  }

  async function openConversation(convo) {
    state.activeConvoId = convo.id;

    // Update active state in list
    pfConvos.querySelectorAll('.pf-conv-item').forEach(i => {
      i.classList.toggle('is-active', i.dataset.convoId == convo.id);
    });

    const otherName = esc(convo.other_display_name || convo.other_name || 'Unknown');

    pfThread.innerHTML = `
      <div class="pf-thread-header">
        <button class="pf-back-btn" id="pfThreadBack" type="button">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>
          Back
        </button>
        <span>${otherName}</span>
      </div>
      <div class="pf-thread-messages" id="pfThreadMessages"></div>
      <form class="pf-message-form" id="pfMessageForm">
        <input class="pf-message-input" type="text" placeholder="Message ${otherName}…" autocomplete="off" required />
        <button class="button button-primary pf-message-send-btn" type="submit">Send</button>
      </form>
    `;

    const backBtn = document.getElementById('pfThreadBack');
    if (backBtn) {
      backBtn.addEventListener('click', () => {
        pfThread.classList.remove('is-open');
        pfConversationList.classList.remove('has-open');
      });
    }

    pfThread.classList.add('is-open');
    pfConversationList.classList.add('has-open');

    await loadThreadMessages(convo.id);

    const form = document.getElementById('pfMessageForm');
    if (form) {
      form.addEventListener('submit', async e => {
        e.preventDefault();
        const input = form.querySelector('.pf-message-input');
        const body = input.value.trim();
        if (!body) return;
        input.value = '';
        const result = await apiPost('messages/send', { conversation_id: convo.id, body });
        if (result.ok && result.data.message) {
          appendMessage(result.data.message, true);
        }
      });
    }
  }

  async function loadThreadMessages(convoId) {
    const container = document.getElementById('pfThreadMessages');
    if (!container) return;
    container.innerHTML = '<div class="pf-loading">Loading…</div>';

    const result = await apiFetch('messages/thread?conversation_id=' + convoId);
    container.innerHTML = '';

    if (!result.ok || !Array.isArray(result.data.data)) {
      container.innerHTML = '<div class="pf-empty" style="flex:1;"><strong>No messages yet.</strong></div>';
      return;
    }

    result.data.data.forEach(msg => {
      const isMine = state.user && (msg.sender_id === state.user.id || msg.is_mine);
      appendMessage(msg, isMine);
    });

    container.scrollTop = container.scrollHeight;
  }

  function appendMessage(msg, isMine) {
    const container = document.getElementById('pfThreadMessages');
    if (!container) return;

    const wrap = document.createElement('div');
    wrap.className = 'pf-message ' + (isMine ? 'is-mine' : 'is-theirs');
    wrap.innerHTML = `
      <span class="pf-message-text">${esc(msg.body || '')}</span>
      <span class="pf-message-time">${relativeTime(msg.created_at)}</span>
    `;
    container.appendChild(wrap);
    container.scrollTop = container.scrollHeight;
  }

  function initNewConvoBtn() {
    if (!pfNewConvoBtn) return;
    pfNewConvoBtn.addEventListener('click', () => openNewConvoModal());
  }

  function openNewConvoModal() {
    const backdrop = document.createElement('div');
    backdrop.className = 'pf-modal-backdrop';
    backdrop.innerHTML = `
      <div class="pf-modal" role="dialog" aria-modal="true" aria-label="New conversation">
        <div class="pf-modal-header">
          <h3>New Conversation</h3>
          <button class="pf-modal-close" type="button" aria-label="Close">✕</button>
        </div>
        <form>
          <label>
            Recipient email or username
            <input type="text" name="recipient" placeholder="user@example.com" required autocomplete="off" />
          </label>
          <label>
            Message
            <textarea name="body" rows="3" placeholder="Say something…" required></textarea>
          </label>
          <button class="button button-primary" type="submit">Send Message</button>
          <p class="pf-modal-note" aria-live="polite"></p>
        </form>
      </div>
    `;

    backdrop.querySelector('.pf-modal-close').addEventListener('click', () => backdrop.remove());
    backdrop.addEventListener('click', e => { if (e.target === backdrop) backdrop.remove(); });

    backdrop.querySelector('form').addEventListener('submit', async e => {
      e.preventDefault();
      const fd = new FormData(e.target);
      const note = backdrop.querySelector('.pf-modal-note');
      const btn = e.target.querySelector('button[type="submit"]');
      btn.disabled = true;
      btn.textContent = 'Sending…';
      note.textContent = '';

      const result = await apiPost('messages/conversations', {
        recipient: fd.get('recipient'),
        body: fd.get('body'),
      });

      if (result.ok && result.data.ok) {
        backdrop.remove();
        await loadConversations();
        if (result.data.conversation) openConversation(result.data.conversation);
      } else {
        note.textContent = result.data.message || 'Could not start conversation.';
        note.className = 'pf-modal-note is-error';
        btn.disabled = false;
        btn.textContent = 'Send Message';
      }
    });

    document.body.appendChild(backdrop);
    backdrop.querySelector('input').focus();
  }

  /* ----------------------------------------------------------
     Bids
  ---------------------------------------------------------- */

  async function loadBids() {
    if (!pfBidsList) return;
    pfBidsList.innerHTML = '<div class="pf-loading">Loading…</div>';

    const result = await apiFetch('bids?tab=' + state.bidsTab);
    pfBidsList.innerHTML = '';

    if (!result.ok || !Array.isArray(result.data.data)) {
      pfBidsList.innerHTML = '<div class="pf-empty"><strong>Could not load bids.</strong><span>The bids API is not yet available.</span></div>';
      return;
    }

    const bids = result.data.data;
    if (bids.length === 0) {
      pfBidsList.innerHTML = '<div class="pf-empty"><strong>No bids here yet.</strong></div>';
      return;
    }

    bids.forEach(bid => pfBidsList.appendChild(buildBidCard(bid)));
  }

  function buildBidCard(bid) {
    const card = document.createElement('div');
    card.className = 'pf-bid-card';

    const status = bid.status || 'pending';
    const rate = bid.rate ? '$' + esc(String(bid.rate)) : '—';
    const title = esc(bid.title || bid.opportunity_title || 'Untitled');
    const party = bid.state === 'sent'
      ? (bid.company_name ? 'To: ' + esc(bid.company_name) : '')
      : (bid.display_name ? 'From: ' + esc(bid.display_name) : '');

    card.innerHTML = `
      <div class="pf-bid-header">
        <div>
          <div class="pf-bid-title">${title}</div>
          ${party ? `<div class="pf-bid-party">${party}</div>` : ''}
        </div>
        <div class="pf-bid-rate">${rate}</div>
      </div>
      ${bid.message ? `<div class="pf-bid-body">${esc(bid.message)}</div>` : ''}
      <div class="pf-bid-footer">
        <span class="pf-bid-status pf-bid-status--${esc(status)}">${esc(status)}</span>
        <span class="pf-bid-date">${relativeTime(bid.created_at)}</span>
      </div>
    `;

    return card;
  }

  function initBidsTabs() {
    document.querySelectorAll('.pf-tab[data-bids-tab]').forEach(tab => {
      tab.addEventListener('click', () => {
        state.bidsTab = tab.dataset.bidsTab;
        document.querySelectorAll('.pf-tab[data-bids-tab]').forEach(t => {
          t.classList.toggle('is-active', t.dataset.bidsTab === state.bidsTab);
        });
        loadBids();
      });
    });
  }

  function initNewBidBtn() {
    if (!pfNewBidBtn) return;
    pfNewBidBtn.addEventListener('click', () => openNewBidModal());
  }

  function openNewBidModal() {
    const backdrop = document.createElement('div');
    backdrop.className = 'pf-modal-backdrop';
    backdrop.innerHTML = `
      <div class="pf-modal" role="dialog" aria-modal="true" aria-label="Place a bid">
        <div class="pf-modal-header">
          <h3>Place a Bid</h3>
          <button class="pf-modal-close" type="button" aria-label="Close">✕</button>
        </div>
        <form>
          <label>
            Opportunity / Project Title
            <input type="text" name="title" placeholder="What are you bidding on?" required />
          </label>
          <label>
            Proposed Rate (USD)
            <input type="number" name="rate" min="0" step="0.01" placeholder="0.00" required />
          </label>
          <label>
            Message
            <textarea name="message" rows="3" placeholder="Describe your proposal…" required></textarea>
          </label>
          <button class="button button-primary" type="submit">Submit Bid</button>
          <p class="pf-modal-note" aria-live="polite"></p>
        </form>
      </div>
    `;

    backdrop.querySelector('.pf-modal-close').addEventListener('click', () => backdrop.remove());
    backdrop.addEventListener('click', e => { if (e.target === backdrop) backdrop.remove(); });

    backdrop.querySelector('form').addEventListener('submit', async e => {
      e.preventDefault();
      const fd = new FormData(e.target);
      const note = backdrop.querySelector('.pf-modal-note');
      const btn = e.target.querySelector('button[type="submit"]');
      btn.disabled = true;
      btn.textContent = 'Submitting…';

      const result = await apiPost('bids', {
        title: fd.get('title'),
        rate: fd.get('rate'),
        message: fd.get('message'),
      });

      if (result.ok && result.data.ok) {
        backdrop.remove();
        loadBids();
      } else {
        note.textContent = result.data.message || 'Could not submit bid.';
        note.className = 'pf-modal-note is-error';
        btn.disabled = false;
        btn.textContent = 'Submit Bid';
      }
    });

    document.body.appendChild(backdrop);
    backdrop.querySelector('input').focus();
  }

  /* ----------------------------------------------------------
     Notifications
  ---------------------------------------------------------- */

  async function loadNotifications() {
    if (!pfNotifList) return;
    pfNotifList.innerHTML = '<div class="pf-loading">Loading…</div>';

    const result = await apiFetch('notifications');
    pfNotifList.innerHTML = '';

    if (!result.ok || !Array.isArray(result.data.data)) {
      pfNotifList.innerHTML = '<div class="pf-empty"><strong>Could not load notifications.</strong><span>The notifications API is not yet available.</span></div>';
      return;
    }

    const notifs = result.data.data;
    if (notifs.length === 0) {
      pfNotifList.innerHTML = '<div class="pf-empty"><strong>All caught up!</strong><span>No notifications.</span></div>';
      return;
    }

    notifs.forEach(n => pfNotifList.appendChild(buildNotifItem(n)));
    updateNotifBadge(notifs.filter(n => !n.read_at).length);
  }

  function buildNotifItem(notif) {
    const item = document.createElement('div');
    item.className = 'pf-notif-item' + (!notif.read_at ? ' is-unread' : '');
    item.dataset.notifId = notif.id;

    const iconChar = notifIcon(notif.type);
    item.innerHTML = `
      <div class="pf-notif-icon" aria-hidden="true">${iconChar}</div>
      <div class="pf-notif-content">
        <div class="pf-notif-title">${esc(notif.title || notif.type || 'Notification')}</div>
        <div class="pf-notif-body">${esc(notif.body || notif.message || '')}</div>
        <div class="pf-notif-time">${relativeTime(notif.created_at)}</div>
      </div>
    `;

    if (!notif.read_at) {
      item.addEventListener('click', () => markNotifRead(notif.id, item));
    }

    return item;
  }

  function notifIcon(type) {
    if (!type) return '●';
    if (type.includes('message')) return '💬';
    if (type.includes('bid')) return '$';
    if (type.includes('like')) return '♥';
    if (type.includes('comment')) return '◎';
    if (type.includes('follow')) return '★';
    return '●';
  }

  async function markNotifRead(id, item) {
    const result = await apiPost('notifications/read', { notification_id: id });
    if (result.ok) {
      item.classList.remove('is-unread');
      updateNotifBadge(document.querySelectorAll('.pf-notif-item.is-unread').length);
    }
  }

  async function initMarkAllRead() {
    if (!pfMarkAllRead) return;
    pfMarkAllRead.addEventListener('click', async () => {
      const result = await apiPost('notifications/read', {});
      if (result.ok) {
        document.querySelectorAll('.pf-notif-item.is-unread').forEach(i => i.classList.remove('is-unread'));
        updateNotifBadge(0);
      }
    });
  }

  function updateNotifBadge(count) {
    state.unreadNotifs = count;
    const show = count > 0;
    [pfNotifBadge, pfNotifBadgeMobile].forEach(b => {
      if (!b) return;
      b.hidden = !show;
      b.textContent = count > 99 ? '99+' : String(count);
    });
  }

  function updateMsgBadge(count) {
    state.unreadMessages = count;
    const show = count > 0;
    [pfMsgBadge, pfMsgBadgeMobile].forEach(b => {
      if (!b) return;
      b.hidden = !show;
      b.textContent = count > 99 ? '99+' : String(count);
    });
  }

  /* ----------------------------------------------------------
     Profile
  ---------------------------------------------------------- */

  async function loadProfile() {
    if (!pfProfileContent || !state.user) return;
    pfProfileContent.innerHTML = '<div class="pf-loading">Loading…</div>';

    const result = await apiFetch('profile');
    const user = result.ok && result.data.user ? result.data.user : state.user;

    renderProfile(user);
  }

  function renderProfile(user) {
    if (!pfProfileContent) return;

    const name = esc(user.display_name || 'Unknown');
    const email = esc(user.email || '');
    const role = esc(user.role || '');
    const bio = esc(user.bio || '');
    const avatarLetter = initials(user.display_name || '?');
    const avatarSrc = user.avatar_url ? `<img src="${esc(user.avatar_url)}" alt="${name}" />` : avatarLetter;

    pfProfileContent.innerHTML = `
      <div class="pf-profile-header">
        <div class="pf-profile-banner"></div>
        <div class="pf-profile-avatar-wrap">
          <div class="pf-profile-avatar-lg">${avatarSrc}</div>
        </div>
      </div>
      <div class="pf-profile-info">
        <div class="pf-profile-name">${name}</div>
        <div class="pf-profile-role">${role}</div>
        <div class="pf-profile-email">${email}</div>
        ${bio ? `<p class="pf-profile-bio">${bio}</p>` : ''}
      </div>

      <div class="pf-profile-body">
        <div class="pf-profile-section">
          <div class="pf-profile-section-title">Edit Profile</div>
          <form class="pf-profile-form" id="pfEditProfileForm">
            <label>
              Display Name
              <input type="text" name="display_name" value="${esc(user.display_name || '')}" placeholder="Your name" />
            </label>
            <label>
              Bio
              <textarea name="bio" rows="4" placeholder="Short professional bio">${esc(user.bio || '')}</textarea>
            </label>
            <label>
              City
              <input type="text" name="city" value="${esc(user.city || '')}" placeholder="City, State" />
            </label>
            <label>
              Availability
              <input type="text" name="availability" value="${esc(user.availability || '')}" placeholder="Open for project work, full time, etc." />
            </label>
            <div class="pf-profile-actions">
              <button class="button button-primary" type="submit">Save Changes</button>
            </div>
            <p class="pf-auth-note" id="pfProfileSaveNote" aria-live="polite"></p>
          </form>
        </div>

        <div class="pf-profile-section">
          <div class="pf-profile-section-title">Change Password</div>
          <form class="pf-profile-form" id="pfChangePasswordForm">
            <label>
              Current Password
              <input type="password" name="current_password" placeholder="Current password" autocomplete="current-password" required />
            </label>
            <label>
              New Password
              <input type="password" name="new_password" placeholder="New password" autocomplete="new-password" required />
            </label>
            <div class="pf-profile-actions">
              <button class="button button-primary" type="submit">Update Password</button>
            </div>
            <p class="pf-auth-note" id="pfPasswordNote" aria-live="polite"></p>
          </form>
        </div>
      </div>
    `;

    const editForm = document.getElementById('pfEditProfileForm');
    if (editForm) {
      editForm.addEventListener('submit', async e => {
        e.preventDefault();
        const btn = editForm.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.textContent = 'Saving…';
        const note = document.getElementById('pfProfileSaveNote');
        const fd = new FormData(editForm);
        const result = await apiPost('profile', {
          display_name: fd.get('display_name'),
          bio: fd.get('bio'),
          city: fd.get('city'),
          availability: fd.get('availability'),
        });
        if (result.ok && result.data.ok) {
          if (result.data.user) {
            state.user = { ...state.user, ...result.data.user };
            window.__PF_USER__ = state.user;
          }
          setNote(note, 'Profile saved.', 'success');
        } else {
          setNote(note, result.data.message || 'Save failed.', 'error');
        }
        btn.disabled = false;
        btn.textContent = 'Save Changes';
      });
    }

    const pwForm = document.getElementById('pfChangePasswordForm');
    if (pwForm) {
      pwForm.addEventListener('submit', async e => {
        e.preventDefault();
        const btn = pwForm.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.textContent = 'Updating…';
        const note = document.getElementById('pfPasswordNote');
        const fd = new FormData(pwForm);
        const result = await apiPost('profile/password', {
          current_password: fd.get('current_password'),
          new_password: fd.get('new_password'),
        });
        if (result.ok && result.data.ok) {
          setNote(note, 'Password updated.', 'success');
          pwForm.reset();
        } else {
          setNote(note, result.data.message || 'Update failed.', 'error');
        }
        btn.disabled = false;
        btn.textContent = 'Update Password';
      });
    }
  }

  /* ----------------------------------------------------------
     Polling for badges
  ---------------------------------------------------------- */

  async function pollBadges() {
    const result = await apiFetch('notifications/counts');
    if (result.ok && result.data) {
      if (result.data.unread_messages != null) updateMsgBadge(result.data.unread_messages);
      if (result.data.unread_notifications != null) updateNotifBadge(result.data.unread_notifications);
    }
  }

  function startPolling() {
    if (state.pollInterval) clearInterval(state.pollInterval);
    state.pollInterval = setInterval(pollBadges, 30000);
  }

  /* ----------------------------------------------------------
     Boot
  ---------------------------------------------------------- */

  function boot() {
    if (!state.authenticated) {
      // Auth wall mode — init auth forms only
      initAuthTabs();
      initLoginForm();
      initRegisterForm();
      initForgotForm();
      return;
    }

    // Platform app mode
    initNavigation();
    initLogout();
    renderFeedFilters();
    initPostForm();
    initFeedLoadMore();
    initBidsTabs();
    initNewBidBtn();
    initNewConvoBtn();
    initMarkAllRead();

    // Load initial view
    loadFeed(true);

    // Start badge polling
    pollBadges();
    startPolling();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
