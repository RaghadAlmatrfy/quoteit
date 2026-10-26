/*
 * Tuwaiq Quotes - interactions
 * 1) Click sounds           6) Copy link + share menu
 * 2) Burst animation        7) Display settings (theme, font size, sound)
 * 3) Toast message          8) Welcome splash + log in / sign up choice
 * 4) Like / dislike         9) Character counter + media preview
 * 5) Save to favorites     10) Chat: scroll + auto refresh
 * 11) Style preview + schedule  13) Statuses (10 s each, 24 h)
 * 12) Notifications bell        14) Welcome tour
 */

const i18n = JSON.parse(document.getElementById('i18n')?.textContent || '{}');
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

const storage = {
    get(key) {
        try { return localStorage.getItem(key); } catch { return null; }
    },
    set(key, value) {
        try { localStorage.setItem(key, value); } catch { /* storage blocked: still works for this page */ }
    },
};

/* ---------------------------------------------------------------
 * 1) Click sounds (made with the Web Audio API, no audio files)
 * ------------------------------------------------------------- */
let audioContext;

// Which sound the buttons make. Chosen from the speaker menu and remembered.
const soundStyles = ['star', 'bubble', 'soft', 'chime', 'piano', 'custom', 'off'];
let soundStyle = storage.get('soundStyle') || (storage.get('sound') === 'off' ? 'off' : 'star');
if (!soundStyles.includes(soundStyle)) soundStyle = 'star';

// Each action starts on a slightly different note
const notes = { like: 1568, dislike: 1175, save: 1397, copy: 1760, share: 1661, send: 1480, open: 1319, close: 1047, tap: 1568 };

// Your own music clips: put .mp3 files in public/sounds (click.mp3, like.mp3, share.mp3 ...)
const soundFiles = JSON.parse(document.body.dataset.soundFiles || '{}');
const clips = {};
const clipSeconds = 1.6;

function playClip(name) {
    const url = soundFiles[name] || soundFiles.click;
    if (!url) return false;

    clips[url] ??= new Audio(url);
    const clip = clips[url];
    clip.pause();
    clip.currentTime = 0;
    clip.volume = 0.6;
    clip.play().catch(() => {});
    clearTimeout(clip.stopTimer);
    clip.stopTimer = setTimeout(() => clip.pause(), clipSeconds * 1000);
    return true;
}

// One short note: frequency, wave type, volume, length, optional pitch slide
function tone({ frequency, type = 'sine', volume = 0.1, length = 0.2, delay = 0, slideTo = null }) {
    const start = audioContext.currentTime + delay;
    const oscillator = audioContext.createOscillator();
    const gain = audioContext.createGain();
    oscillator.type = type;
    oscillator.frequency.setValueAtTime(frequency, start);
    if (slideTo) oscillator.frequency.exponentialRampToValueAtTime(slideTo, start + length * 0.6);
    gain.gain.setValueAtTime(0.0001, start);
    gain.gain.exponentialRampToValueAtTime(volume, start + 0.01);
    gain.gain.exponentialRampToValueAtTime(0.0001, start + length);
    oscillator.connect(gain).connect(audioContext.destination);
    oscillator.start(start);
    oscillator.stop(start + length + 0.05);
}

const soundMakers = {
    // three bright little notes going up
    star: (note) => [1, 1.26, 1.6].forEach((ratio, index) => {
        tone({ frequency: note * ratio, volume: 0.08, length: 0.32, delay: index * 0.055 });
        tone({ frequency: note * ratio * 2, type: 'triangle', volume: 0.025, length: 0.25, delay: index * 0.055 });
    }),
    // a round "pop" sliding up
    bubble: (note) => tone({ frequency: note / 3, slideTo: note / 1.5, volume: 0.16, length: 0.14 }),
    // very short and quiet, like a soft key press
    soft: (note) => tone({ frequency: note / 2, type: 'triangle', volume: 0.07, length: 0.06 }),
    // a small bell with a gentle ring
    chime: (note) => [1, 2.4, 3.9].forEach((ratio, index) => tone({ frequency: (note / 2) * ratio, volume: 0.07 / (index + 1), length: 0.9 })),
    // one piano-like note
    piano: (note) => {
        tone({ frequency: note / 2, type: 'triangle', volume: 0.14, length: 0.6 });
        tone({ frequency: note, type: 'sine', volume: 0.04, length: 0.4 });
    },
};

function playSound(name = 'tap') {
    if (soundStyle === 'off') return;
    if (soundStyle === 'custom' && playClip(name)) return;
    try {
        audioContext ??= new (window.AudioContext || window.webkitAudioContext)();
        (soundMakers[soundStyle] || soundMakers.star)(notes[name] || notes.tap);
    } catch {
        /* no audio support */
    }
}

document.addEventListener('click', (event) => {
    const element = event.target.closest('[data-sound]');
    if (element) playSound(element.dataset.sound);
});

/* ---------------------------------------------------------------
 * 2) Burst: copies of the button's icon fly out softly
 * ------------------------------------------------------------- */
const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

function burst(button) {
    if (reduceMotion) return;

    const icon = button.querySelector('svg');
    const rect = button.getBoundingClientRect();
    const centerX = rect.left + rect.width / 2;
    const centerY = rect.top + rect.height / 2;
    const color = getComputedStyle(button).backgroundColor;
    const count = 8;

    for (let index = 0; index < count; index++) {
        const particle = document.createElement('span');
        particle.className = 'burst-particle';
        particle.innerHTML = icon ? icon.outerHTML : '';

        const angle = (Math.PI * 2 * index) / count + Math.random() * 0.5;
        const distance = 34 + Math.random() * 26;
        particle.style.left = `${centerX}px`;
        particle.style.top = `${centerY}px`;
        particle.style.setProperty('--dx', `${Math.cos(angle) * distance}px`);
        particle.style.setProperty('--dy', `${Math.sin(angle) * distance}px`);
        particle.style.setProperty('--rot', `${Math.random() * 90 - 45}deg`);
        particle.style.setProperty('--particle-color', color === 'rgba(0, 0, 0, 0)' ? '#f2a54a' : color);

        document.body.appendChild(particle);
        particle.addEventListener('animationend', () => particle.remove());
    }

    button.classList.remove('is-popping');
    void button.offsetWidth; // restart the animation
    button.classList.add('is-popping');
}

document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-burst]');
    if (button) burst(button);
});

/* ---------------------------------------------------------------
 * 3) Toast message
 * ------------------------------------------------------------- */
const toast = document.getElementById('toast');
let toastTimer;

function showToast(message) {
    if (!toast || !message) return;
    toast.textContent = message;
    toast.classList.remove('opacity-0');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => toast.classList.add('opacity-0'), 1800);
}

/* ---------------------------------------------------------------
 * "My interests" panel: reload it after every like / save / copy / share
 * ------------------------------------------------------------- */
async function refreshInterests() {
    const panel = document.querySelector('[data-interests]');
    if (!panel) return;
    try {
        const html = await (await fetch(panel.dataset.refreshUrl, { headers: { Accept: 'text/html' } })).text();
        const fresh = new DOMParser().parseFromString(html, 'text/html').querySelector('[data-interests]');
        if (fresh) panel.replaceWith(fresh);
    } catch {
        /* try again next time */
    }
}

function trackShare(url, type) {
    if (!url || document.body.dataset.auth !== 'true') return;
    fetch(url, {
        method: 'POST',
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ type }),
    }).then(refreshInterests).catch(() => {});
}

/* ---------------------------------------------------------------
 * 4 + 5) Like / dislike and favorites without reloading the page
 * ------------------------------------------------------------- */
// "My interests" is refreshed a moment after the last click, not after every click
let interestsTimer;
function refreshInterestsSoon() {
    clearTimeout(interestsTimer);
    interestsTimer = setTimeout(refreshInterests, 1500);
}

// Each quote saves its clicks one after another, so fast double-clicks stay in order
const pendingSaves = new WeakMap();

function reactionState(actions) {
    const state = { reaction: null, likes: 0, dislikes: 0 };
    actions.querySelectorAll('form[data-ajax="reaction"]').forEach((reactionForm) => {
        const type = reactionForm.querySelector('input[name="type"]').value;
        if (reactionForm.querySelector('button').getAttribute('aria-pressed') === 'true') state.reaction = type;
    });
    state.likes = Number(actions.querySelector('[data-count="likes"]').textContent) || 0;
    state.dislikes = Number(actions.querySelector('[data-count="dislikes"]').textContent) || 0;
    return state;
}

function showReaction(actions, state) {
    actions.querySelectorAll('form[data-ajax="reaction"]').forEach((reactionForm) => {
        const type = reactionForm.querySelector('input[name="type"]').value;
        reactionForm.querySelector('button').setAttribute('aria-pressed', String(state.reaction === type));
    });
    actions.querySelector('[data-count="likes"]').textContent = state.likes;
    actions.querySelector('[data-count="dislikes"]').textContent = state.dislikes;
}

document.addEventListener('submit', (event) => {
    const form = event.target.closest('form[data-ajax]');
    if (!form) return;

    event.preventDefault();

    if (document.body.dataset.auth !== 'true') {
        showToast(i18n.loginFirst);
        setTimeout(() => (window.location.href = document.body.dataset.loginUrl), 700);
        return;
    }

    const actions = form.closest('[data-quote-actions]') ?? form;
    const isReaction = form.dataset.ajax === 'reaction';
    const button = form.querySelector('button');

    // 1) Change the color and the number straight away (no waiting for the server)
    let before;
    if (isReaction) {
        before = reactionState(actions);
        const type = form.querySelector('input[name="type"]').value;
        const after = { ...before, reaction: before.reaction === type ? null : type };
        if (before.reaction) after[before.reaction === 'like' ? 'likes' : 'dislikes'] -= 1;
        if (after.reaction) after[after.reaction === 'like' ? 'likes' : 'dislikes'] += 1;
        showReaction(actions, after);
    } else {
        before = button.getAttribute('aria-pressed') === 'true';
        button.setAttribute('aria-pressed', String(!before));
        showToast(!before ? i18n.saved : i18n.removed);
    }

    // 2) Save in the background; if it fails, put things back as they were
    const body = new FormData(form);
    const previous = pendingSaves.get(actions) ?? Promise.resolve();
    const save = previous.then(async () => {
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
                body,
            });
            if (response.status === 401) return (window.location.href = document.body.dataset.loginUrl);
            if (!response.ok) throw new Error(response.statusText);

            const data = await response.json();
            // Only the last click decides what is shown, so the numbers from the server win
            if (pendingSaves.get(actions) === save) {
                if (isReaction) showReaction(actions, data);
                else button.setAttribute('aria-pressed', String(data.saved));
            }
            refreshInterestsSoon();
        } catch {
            if (isReaction) showReaction(actions, before);
            else button.setAttribute('aria-pressed', String(before));
            showToast(i18n.notSaved);
        }
    });
    pendingSaves.set(actions, save);
});

/* ---------------------------------------------------------------
 * 6) Copy link + share menu
 * ------------------------------------------------------------- */
async function copyText(text, message = i18n.copied) {
    try {
        await navigator.clipboard.writeText(text);
        showToast(message);
        return true;
    } catch {
        showToast(i18n.copyFailed);
        return false;
    }
}

document.addEventListener('click', (event) => {
    const copyButton = event.target.closest('[data-copy-link]');
    if (copyButton) {
        copyText(copyButton.dataset.copyLink);
        trackShare(copyButton.dataset.trackUrl, 'copy');
    }
});

const shareMenu = document.getElementById('share-menu');
let shareData = { url: '', text: '' };

const shareLinks = {
    x: ({ url, text }) => `https://x.com/intent/post?text=${encodeURIComponent(text)}&url=${encodeURIComponent(url)}`,
    whatsapp: ({ url, text }) => `https://wa.me/?text=${encodeURIComponent(`${text}\n${url}`)}`,
    telegram: ({ url, text }) => `https://t.me/share/url?url=${encodeURIComponent(url)}&text=${encodeURIComponent(text)}`,
    linkedin: ({ url }) => `https://www.linkedin.com/sharing/share-offsite/?url=${encodeURIComponent(url)}`,
    facebook: ({ url }) => `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`,
    snapchat: ({ url }) => `https://www.snapchat.com/scan?attachmentUrl=${encodeURIComponent(url)}`,
    tiktok: () => 'https://www.tiktok.com/',
    email: ({ url, text }) => `mailto:?subject=${encodeURIComponent('Tuwaiq Quotes')}&body=${encodeURIComponent(`${text}\n${url}`)}`,
};

if (shareMenu && navigator.share) {
    shareMenu.querySelector('[data-share-target="native"]')?.removeAttribute('hidden');
}

document.addEventListener('click', (event) => {
    const shareButton = event.target.closest('[data-share]');
    if (!shareButton || !shareMenu) return;

    shareData = { url: shareButton.dataset.shareUrl, text: shareButton.dataset.shareText, trackUrl: shareButton.dataset.trackUrl };
    shareMenu.querySelectorAll('a[data-share-target]').forEach((link) => {
        const build = shareLinks[link.dataset.shareTarget];
        if (build) link.href = build(shareData);
    });
    shareMenu.showPopover();
});

shareMenu?.addEventListener('click', (event) => {
    const option = event.target.closest('[data-share-target]');
    if (!option) return;

    const target = option.dataset.shareTarget;
    playSound('share');
    burst(option.querySelector('span') || option);

    trackShare(shareData.trackUrl, target === 'copy' ? 'copy' : 'share');
    if (target === 'copy') copyText(shareData.url);
    if (target === 'tiktok') copyText(`${shareData.text}\n${shareData.url}`, i18n.tiktok);
    if (target === 'native') navigator.share?.({ title: 'Tuwaiq Quotes', text: shareData.text, url: shareData.url }).catch(() => {});
});

/* ---------------------------------------------------------------
 * 7) Display settings: theme, font size, sound
 * ------------------------------------------------------------- */
const root = document.documentElement;

function applyTheme(theme) {
    root.dataset.theme = theme;
    document.querySelectorAll('[data-theme-toggle]').forEach((toggle) => {
        toggle.setAttribute('aria-checked', String(theme === 'dark'));
        toggle.querySelector('[data-theme-icon-light]')?.classList.toggle('hidden', theme === 'dark');
        toggle.querySelector('[data-theme-icon-dark]')?.classList.toggle('hidden', theme !== 'dark');
    });
    storage.set('theme', theme);
}

document.querySelectorAll('[data-theme-toggle]').forEach((toggle) => {
    toggle.addEventListener('click', () => {
        applyTheme(root.dataset.theme === 'dark' ? 'light' : 'dark');
        playSound('tap');
    });
});
applyTheme(root.dataset.theme || 'dark');

const fontSizes = ['sm', 'md', 'lg', 'xl'];

document.querySelectorAll('[data-font-step]').forEach((button) => {
    button.addEventListener('click', () => {
        const current = fontSizes.indexOf(root.dataset.font || 'md');
        const next = fontSizes[Math.min(fontSizes.length - 1, Math.max(0, current + Number(button.dataset.fontStep)))];
        root.dataset.font = next;
        storage.set('fontSize', next);
        playSound('tap');
    });
});

// The speaker menu: pick a sound (the speaker icon is crossed out when "No sound" is chosen)
function applySound() {
    document.querySelectorAll('[data-sound-button]').forEach((button) => {
        button.querySelector('[data-sound-on]')?.classList.toggle('hidden', soundStyle === 'off');
        button.querySelector('[data-sound-off]')?.classList.toggle('hidden', soundStyle !== 'off');
    });
    document.querySelectorAll('[data-sound-style]').forEach((radio) => {
        radio.checked = radio.value === soundStyle;
    });
    // "My music file" only appears if there is an .mp3 in public/sounds
    const customOption = document.querySelector('[data-sound-custom-option]');
    if (customOption) customOption.hidden = Object.keys(soundFiles).length === 0;
}

document.querySelectorAll('[data-sound-style]').forEach((radio) => {
    radio.addEventListener('change', () => {
        soundStyle = radio.value;
        storage.set('soundStyle', soundStyle);
        applySound();
        playSound('like'); // let her hear the new sound
    });
});
applySound();

/* ---------------------------------------------------------------
 * 8) Welcome splash, then (for guests) log in / create account
 * ------------------------------------------------------------- */
const body = document.body;

// Remember who used this browser, to greet them next time (even after log out)
if (body.dataset.auth === 'true') {
    storage.set('lastUser', JSON.stringify({ name: body.dataset.userName, email: body.dataset.userEmail }));
}
const lastUser = (() => {
    try { return JSON.parse(storage.get('lastUser') || 'null'); } catch { return null; }
})();

const splash = document.getElementById('splash');
const authChoice = document.getElementById('auth-choice');
let splashSeen = false;
try { splashSeen = sessionStorage.getItem('splashSeen') === '1'; } catch { /* ignore */ }

let afterSplash = () => {};

function closeSplash() {
    if (!splash || splash.classList.contains('is-hidden')) return;
    splash.classList.add('is-hidden');
    setTimeout(() => afterSplash(), 400);
    try { sessionStorage.setItem('splashSeen', '1'); } catch { /* ignore */ }

    if (authChoice && body.dataset.auth !== 'true') {
        const title = authChoice.querySelector('[data-auth-choice-title]');
        if (lastUser?.name && title) title.textContent = title.dataset.titleBack.replace('__NAME__', lastUser.name);
        setTimeout(() => authChoice.showPopover(), 250);
    }
}

if (splash && !splashSeen) {
    const greeting = splash.querySelector('[data-splash-greeting]');
    if (body.dataset.auth !== 'true' && lastUser?.name && greeting) {
        greeting.textContent = greeting.dataset.greetingBack.replace('__NAME__', lastUser.name);
    }
    splash.classList.remove('is-hidden');
    splash.querySelector('[data-splash-close]')?.addEventListener('click', closeSplash);
    splash.addEventListener('click', (event) => event.target === splash && closeSplash());
    setTimeout(closeSplash, 3200);
}

// Login page: fill in the email of the last user
const rememberedEmail = document.querySelector('[data-remember-email]');
if (rememberedEmail && !rememberedEmail.value && lastUser?.email) {
    rememberedEmail.value = lastUser.email;
}

/* ---------------------------------------------------------------
 * 9) Character counter + media previews
 * ------------------------------------------------------------- */
document.querySelectorAll('textarea[data-counter]').forEach((textarea) => {
    const counter = document.getElementById(textarea.dataset.counter);
    const max = Number(textarea.getAttribute('maxlength')) || 255;
    const update = () => {
        if (!counter) return;
        counter.textContent = `${textarea.value.length} / ${max}`;
        counter.classList.toggle('text-red-500', textarea.value.length >= max - 20);
    };
    textarea.addEventListener('input', update);
    update();
});

document.querySelectorAll('input[type="file"][data-media-input]').forEach((input) => {
    input.addEventListener('change', () => {
        const preview = document.getElementById(input.dataset.mediaInput);
        const file = input.files?.[0];
        if (!preview) return;
        preview.innerHTML = '';
        preview.classList.toggle('hidden', !file);
        if (!file) return;

        const url = URL.createObjectURL(file);
        const element = document.createElement(file.type.startsWith('video/') ? 'video' : 'img');
        element.src = url;
        element.className = 'max-h-48 w-full object-cover';
        if (element.tagName === 'VIDEO') element.controls = true;
        preview.appendChild(element);
    });
});

document.querySelector('[data-avatar-input]')?.addEventListener('change', (event) => {
    const file = event.target.files?.[0];
    const image = document.getElementById('avatar-preview-img');
    if (file && image) image.src = URL.createObjectURL(file);
});

/* ---------------------------------------------------------------
 * 10) Chat: keep the newest message visible, refresh every 8 seconds
 * ------------------------------------------------------------- */
const chat = document.querySelector('[data-chat]');

if (chat) {
    chat.scrollTop = chat.scrollHeight;

    setInterval(async () => {
        if (document.hidden) return;
        try {
            const response = await fetch(chat.dataset.refreshUrl, { headers: { Accept: 'text/html' } });
            const html = new DOMParser().parseFromString(await response.text(), 'text/html');
            const fresh = html.querySelector('[data-chat]');
            if (fresh && fresh.innerHTML !== chat.innerHTML) {
                const atBottom = chat.scrollHeight - chat.scrollTop - chat.clientHeight < 40;
                chat.innerHTML = fresh.innerHTML;
                if (atBottom) chat.scrollTop = chat.scrollHeight;
                playSound('tap');
            }
        } catch {
            /* offline: try again later */
        }
    }, 8000);
}

/* ---------------------------------------------------------------
 * 11) Style preview (font, size, bold, italic, align, background)
 *     + schedule: send the chosen local time as UTC
 * ------------------------------------------------------------- */
const sizeClasses = ['text-base', 'text-lg', 'text-xl', 'text-2xl', 'text-3xl'];

function textColorFor(hex) {
    const [red, green, blue] = [1, 3, 5].map((start) => parseInt(hex.slice(start, start + 2), 16));
    return (0.299 * red + 0.587 * green + 0.114 * blue) / 255 > 0.6 ? '#2c2133' : '#ffffff';
}

document.querySelectorAll('form').forEach((form) => {
    const controls = form.querySelector('[data-style-controls]');
    const preview = form.querySelector('[data-style-preview]');
    if (!controls || !preview) return;

    const box = form.querySelector('[data-style-preview-box]');
    const source = form.querySelector('[data-style-source]');
    const defaultBoxClasses = box.className;

    const update = () => {
        const font = controls.querySelector('[data-style="font"]');
        const size = controls.querySelector('[data-style="size"]');
        const bold = controls.querySelector('[data-style="bold"]').checked;
        const italic = controls.querySelector('[data-style="italic"]').checked;
        const align = controls.querySelector('[data-style="align"]:checked')?.value || 'start';
        const bg = controls.querySelector('[data-style="bg"]:checked')?.value || '';

        const text = source?.value.trim();
        preview.textContent = text ? `“${text}”` : preview.dataset.placeholder || preview.textContent;
        preview.style.fontFamily = font.value === 'default' ? '' : font.selectedOptions[0].dataset.family;
        preview.classList.remove(...sizeClasses, 'text-start', 'text-right', 'text-center', 'text-left');
        preview.classList.add(size.selectedOptions[0].dataset.class, `text-${align}`);
        preview.classList.toggle('font-bold', bold);
        preview.classList.toggle('italic', italic);

        box.className = defaultBoxClasses;
        box.style.background = bg || '';
        box.style.color = bg ? textColorFor(bg) : '';
    };

    // the rainbow swatch opens a color picker
    const customColor = controls.querySelector('[data-custom-color]');
    const customRadio = controls.querySelector('[data-custom-color-radio]');
    customColor?.addEventListener('input', () => {
        customRadio.value = customColor.value;
        customRadio.checked = true;
        update();
    });

    controls.addEventListener('change', update);
    source?.addEventListener('input', update);
    update();
});

document.querySelectorAll('form[data-composer]').forEach((form) => {
    const local = form.querySelector('[data-schedule-local]');
    const utc = form.querySelector('[data-schedule-utc]');
    if (local) {
        const soon = new Date(Date.now() + 60 * 60 * 1000);
        soon.setMinutes(soon.getMinutes() - soon.getTimezoneOffset());
        local.min = new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().slice(0, 16);
        local.value ||= soon.toISOString().slice(0, 16);
    }
    form.addEventListener('submit', (event) => {
        const intent = event.submitter?.value;
        if (intent === 'schedule' && local?.value) {
            utc.value = new Date(local.value).toISOString();
        } else if (utc) {
            utc.value = '';
        }
    });
});

// Show scheduled times in the visitor's own time zone
document.querySelectorAll('time[data-local-time]').forEach((time) => {
    const date = new Date(time.getAttribute('datetime'));
    if (!Number.isNaN(date.getTime())) {
        time.textContent = date.toLocaleString(document.documentElement.lang, { dateStyle: 'medium', timeStyle: 'short' });
    }
});

/* ---------------------------------------------------------------
 * 12) Notifications bell: open under the bell, mark as read
 * ------------------------------------------------------------- */
// Open a menu next to the button that opened it: under it (top bar) or to its left (right bar)
function placeNextToButton(panel) {
    const button = [...document.querySelectorAll(`[popovertarget="${panel.id}"]`)].find((element) => element.getClientRects().length > 0);
    if (!button) return;
    const rect = button.getBoundingClientRect();
    const inRightBar = button.closest('.tool-rail');
    const rtl = document.documentElement.dir === 'rtl';

    Object.assign(panel.style, { position: 'fixed', margin: '0', bottom: 'auto' });
    if (inRightBar) {
        panel.style.top = `${Math.max(8, Math.min(rect.top, window.innerHeight - panel.offsetHeight - 8))}px`;
        panel.style.right = `${window.innerWidth - rect.left + 10}px`;
        panel.style.left = 'auto';
    } else {
        panel.style.top = `${rect.bottom + 8}px`;
        panel.style.left = rtl ? `${Math.max(8, rect.left)}px` : 'auto';
        panel.style.right = rtl ? 'auto' : `${Math.max(8, window.innerWidth - rect.right)}px`;
    }
}

['notifications-panel', 'sound-menu'].forEach((id) => {
    document.getElementById(id)?.addEventListener('toggle', (event) => {
        if (event.newState === 'open') placeNextToButton(event.target);
    });
});

// Opening the notifications marks them as read
const notificationsPanel = document.getElementById('notifications-panel');
notificationsPanel?.addEventListener('toggle', (event) => {
    const bells = document.querySelectorAll('[data-notifications-button]');
    if (event.newState !== 'open' || !bells.length || Number(bells[0].dataset.unread) === 0) return;

    bells.forEach((bell) => (bell.dataset.unread = '0'));
    fetch(bells[0].dataset.readUrl, {
        method: 'POST',
        headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
    }).then(() => document.querySelectorAll('[data-notifications-count]').forEach((badge) => badge.remove())).catch(() => {});
});

/* ---------------------------------------------------------------
 * 13) Statuses: each one shows for 10 seconds, then the next
 * ------------------------------------------------------------- */
const viewer = document.getElementById('status-viewer');

if (viewer) {
    const groups = JSON.parse(viewer.querySelector('[data-status-groups]')?.textContent || '[]');
    const seconds = Number(viewer.dataset.seconds) || 10;
    const bars = viewer.querySelector('[data-status-bars]');
    const content = viewer.querySelector('[data-status-content]');
    const stage = viewer.querySelector('[data-status-stage]');
    const deleteForm = viewer.querySelector('[data-status-delete]');
    let groupIndex = 0;
    let itemIndex = 0;
    let timer;
    let startedAt;
    let paused = false;
    let pausedProgress = 0;

    const seen = new Set((storage.get('seenStatuses') || '').split(',').filter(Boolean));
    const markRings = () => {
        document.querySelectorAll('[data-status-ids]').forEach((button) => {
            const ids = button.dataset.statusIds.split(',');
            button.querySelector('.status-ring')?.classList.toggle('is-seen', ids.every((id) => seen.has(id)));
        });
    };
    markRings();

    const close = () => {
        cancelAnimationFrame(timer);
        viewer.classList.add('hidden');
        viewer.classList.remove('flex');
        content.innerHTML = '';
        markRings();
    };

    const show = () => {
        const group = groups[groupIndex];
        const item = group?.items[itemIndex];
        if (!item) return close();

        seen.add(String(item.id));
        storage.set('seenStatuses', [...seen].slice(-300).join(','));

        viewer.querySelector('[data-status-avatar]').src = group.user.avatar;
        viewer.querySelector('[data-status-profile]').href = group.user.url;
        viewer.querySelector('[data-status-private]').hidden = !item.friendsOnly;

        // Like button on other people's statuses
        const likeButton = viewer.querySelector('[data-status-like]');
        likeButton.hidden = !item.likeUrl;
        likeButton.setAttribute('aria-pressed', String(item.liked));
        likeButton.classList.toggle('bg-pink-500/70', item.liked);
        likeButton.onclick = async (event) => {
            event.stopPropagation();
            try {
                const response = await fetch(item.likeUrl, { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken } });
                item.liked = (await response.json()).liked;
                likeButton.setAttribute('aria-pressed', String(item.liked));
                likeButton.classList.toggle('bg-pink-500/70', item.liked);
            } catch { /* ignore */ }
        };

        // Who viewed / liked (only on my own statuses)
        const viewersToggle = viewer.querySelector('[data-status-viewers-toggle]');
        const viewersPanel = viewer.querySelector('[data-status-viewers]');
        viewersPanel.hidden = true;
        viewersToggle.hidden = !item.viewers;
        if (item.viewers) {
            viewer.querySelector('[data-status-viewers-count]').textContent = item.viewers.length;
            viewersPanel.innerHTML = item.viewers.length
                ? item.viewers.map((person) => `<a href="${person.url}" class="flex items-center gap-3 rounded-xl p-2 hover:bg-white/10">
                        <img src="${person.avatar}" alt="" class="h-8 w-8 rounded-full object-cover">
                        <span class="flex-1 text-sm">${person.name.replace(/[<>&]/g, '')}</span>
                        ${person.liked ? '<span class="text-pink-400" aria-label="liked">&#9829;</span>' : ''}
                    </a>`).join('')
                : `<p class="p-2 text-sm opacity-75">${viewersPanel.dataset.empty}</p>`;
            viewersToggle.onclick = (event) => {
                event.stopPropagation();
                viewersPanel.hidden = !viewersPanel.hidden;
                paused = !viewersPanel.hidden;
                if (!paused) startedAt = performance.now() - pausedProgress * seconds * 1000;
            };
        }

        // Tell the owner that I saw it
        if (item.viewUrl && !item.viewers) {
            fetch(item.viewUrl, { method: 'POST', headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken } }).catch(() => {});
        }
        viewer.querySelector('[data-status-name]').textContent = group.user.name;
        viewer.querySelector('[data-status-ago]').textContent = item.ago;
        deleteForm.hidden = !item.deleteUrl;
        if (item.deleteUrl) deleteForm.action = item.deleteUrl;

        bars.innerHTML = group.items.map((_, index) => `<div class="status-bar"><span style="width:${index < itemIndex ? 100 : 0}%"></span></div>`).join('');
        stage.style.background = item.media ? '#000' : item.bg;
        content.innerHTML = '';

        if (item.media && item.type === 'video') {
            const video = Object.assign(document.createElement('video'), { src: item.media, autoplay: true, muted: false, playsInline: true });
            video.className = 'max-h-full max-w-full';
            content.appendChild(video);
        } else if (item.media) {
            const image = Object.assign(document.createElement('img'), { src: item.media, alt: '' });
            image.className = 'max-h-full max-w-full object-contain';
            content.appendChild(image);
        }
        if (item.body) {
            const text = document.createElement('p');
            text.dir = 'auto';
            text.className = item.media ? 'absolute inset-x-6 bottom-10 rounded-xl bg-black/50 p-3 text-lg text-white' : 'text-2xl font-semibold text-white';
            text.textContent = item.body;
            content.appendChild(text);
        }

        cancelAnimationFrame(timer);
        startedAt = performance.now();
        const bar = bars.children[itemIndex]?.firstElementChild;
        paused = false;
        const tick = (now) => {
            if (paused) {
                startedAt = now - pausedProgress * seconds * 1000;
                timer = requestAnimationFrame(tick);
                return;
            }
            const progress = Math.min(1, (now - startedAt) / (seconds * 1000));
            pausedProgress = progress;
            if (bar) bar.style.width = `${progress * 100}%`;
            if (progress >= 1) return next();
            timer = requestAnimationFrame(tick);
        };
        timer = requestAnimationFrame(tick);
    };

    const next = () => {
        if (itemIndex < groups[groupIndex].items.length - 1) itemIndex++;
        else if (groupIndex < groups.length - 1) { groupIndex++; itemIndex = 0; }
        else return close();
        show();
    };
    const previous = () => {
        if (itemIndex > 0) itemIndex--;
        else if (groupIndex > 0) { groupIndex--; itemIndex = groups[groupIndex].items.length - 1; }
        show();
    };

    document.querySelectorAll('[data-status-open]').forEach((button) => {
        button.addEventListener('click', () => {
            groupIndex = Number(button.dataset.statusOpen);
            itemIndex = 0;
            viewer.classList.remove('hidden');
            viewer.classList.add('flex');
            show();
        });
    });
    viewer.querySelector('[data-status-next]').addEventListener('click', next);
    viewer.querySelector('[data-status-prev]').addEventListener('click', previous);
    viewer.querySelector('[data-status-close]').addEventListener('click', close);
    document.addEventListener('keydown', (event) => {
        if (viewer.classList.contains('hidden')) return;
        if (event.key === 'Escape') close();
        if (event.key === 'ArrowRight') (document.documentElement.dir === 'rtl' ? previous : next)();
        if (event.key === 'ArrowLeft') (document.documentElement.dir === 'rtl' ? next : previous)();
    });
}

// Add-status window: the preview follows the chosen color
document.querySelectorAll('[data-status-color]').forEach((radio) => {
    radio.addEventListener('change', () => {
        const preview = document.querySelector('[data-status-preview]');
        if (preview) preview.style.background = radio.value;
    });
});

/* ---------------------------------------------------------------
 * 14) Welcome tour: highlights the main tools, with Skip / Back / Next
 * ------------------------------------------------------------- */
const tourData = document.getElementById('tour-steps');
const tour = document.getElementById('tour');

if (tourData && tour) {
    const allSteps = JSON.parse(tourData.textContent);
    const spotlight = tour.querySelector('[data-tour-spotlight]');
    const card = tour.querySelector('[data-tour-card]');
    const nextButton = tour.querySelector('[data-tour-next]');
    const backButton = tour.querySelector('[data-tour-back]');
    let steps = [];
    let index = 0;

    const targetFor = (step) => {
        const selector = step.target === 'actions' ? '[data-quote-actions]' : `[data-tour="${step.target}"]`;
        return [...document.querySelectorAll(selector)].find((element) => element.getClientRects().length > 0);
    };

    const place = () => {
        const target = targetFor(steps[index]);
        if (!target) return;
        target.scrollIntoView({ block: 'center', behavior: reduceMotion ? 'auto' : 'smooth' });

        setTimeout(() => {
            const rect = target.getBoundingClientRect();
            const pad = 8;
            Object.assign(spotlight.style, {
                top: `${rect.top - pad}px`, left: `${rect.left - pad}px`,
                width: `${rect.width + pad * 2}px`, height: `${rect.height + pad * 2}px`,
            });

            const cardRect = card.getBoundingClientRect();
            const below = rect.bottom + 16 + cardRect.height < window.innerHeight;
            const top = below ? rect.bottom + 16 : Math.max(12, rect.top - cardRect.height - 16);
            const left = Math.min(window.innerWidth - cardRect.width - 12, Math.max(12, rect.left + rect.width / 2 - cardRect.width / 2));
            Object.assign(card.style, { top: `${top}px`, left: `${left}px` });
        }, reduceMotion ? 0 : 350);
    };

    const render = () => {
        const step = steps[index];
        tour.querySelector('[data-tour-count]').textContent = `${index + 1} / ${steps.length}`;
        tour.querySelector('[data-tour-title]').textContent = step.title;
        tour.querySelector('[data-tour-text]').textContent = step.text;
        backButton.hidden = index === 0;
        nextButton.textContent = index === steps.length - 1 ? nextButton.dataset.labelDone : nextButton.dataset.labelNext;
        place();
    };

    const finish = () => {
        tour.classList.add('hidden');
        fetch(tourData.dataset.completeUrl, {
            method: 'POST',
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrfToken },
        }).catch(() => {});
    };

    const start = () => {
        steps = allSteps.filter((step) => targetFor(step));
        if (steps.length === 0) return;
        index = 0;
        tour.classList.remove('hidden');
        render();
    };

    nextButton.addEventListener('click', () => (index < steps.length - 1 ? (index++, render()) : finish()));
    backButton.addEventListener('click', () => { if (index > 0) { index--; render(); } });
    tour.querySelector('[data-tour-skip]').addEventListener('click', finish);
    window.addEventListener('resize', () => !tour.classList.contains('hidden') && place());
    document.querySelectorAll('[data-tour-start]').forEach((button) => button.addEventListener('click', start));

    if (tourData.dataset.autoStart === 'true') {
        const splashVisible = splash && !splash.classList.contains('is-hidden');
        if (splashVisible) afterSplash = start;
        else setTimeout(start, 600);
    }
}

/* ---------------------------------------------------------------
 * 15) Translate a quote (Arabic <-> English) right under it
 * ------------------------------------------------------------- */
async function translateQuote(card, target) {
    const box = card.querySelector('[data-translation]');
    const output = box.querySelector('[data-translation-text]');
    const text = card.querySelector('[data-translate]').dataset.translate;

    box.hidden = false;
    output.textContent = '…';
    box.querySelectorAll('[data-translate-to]').forEach((chip) => {
        chip.classList.toggle('font-bold', chip.dataset.translateTo === target);
        chip.classList.toggle('bg-black/15', chip.dataset.translateTo === target);
    });

    try {
        const url = `${document.body.dataset.translateUrl}?to=${target}&text=${encodeURIComponent(text)}`;
        const response = await fetch(url, { headers: { Accept: 'application/json' } });
        const data = await response.json();
        if (!response.ok) throw new Error(data.message);
        output.textContent = data.translation;
        output.dir = target === 'ar' ? 'rtl' : 'ltr';
    } catch (error) {
        output.textContent = error.message || i18n.translateFailed;
        output.dir = 'auto';
    }
}

document.addEventListener('click', (event) => {
    // the "عربي / English" chips: translate to that language
    const chip = event.target.closest('[data-translate-to]');
    if (chip) {
        translateQuote(chip.closest('article'), chip.dataset.translateTo);
        return;
    }

    const button = event.target.closest('[data-translate]');
    if (!button) return;

    const card = button.closest('article');
    const box = card.querySelector('[data-translation]');
    if (!box.hidden) {
        box.hidden = true;
        return;
    }

    // Arabic text -> English, any other text -> Arabic (whatever the page language is)
    translateQuote(card, /[\u0600-\u06FF]/.test(button.dataset.translate) ? 'en' : 'ar');
});

/* ---------------------------------------------------------------
 * 16) Comments: open under the quote, add and delete without reloading
 * ------------------------------------------------------------- */
async function loadComments(box) {
    try {
        const html = await (await fetch(box.dataset.url, { headers: { Accept: 'text/html', 'X-Requested-With': 'XMLHttpRequest' } })).text();
        box.innerHTML = html;
        updateCommentCount(box);
    } catch {
        /* try again when opened next time */
    }
}

function updateCommentCount(box) {
    const count = box.querySelector('[data-comments-list]')?.dataset.count;
    const counter = box.closest('article')?.querySelector('[data-comments-count]');
    if (counter && count !== undefined) counter.textContent = count;
}

document.addEventListener('click', (event) => {
    const toggle = event.target.closest('[data-comments-toggle]');
    if (!toggle) return;

    const box = toggle.closest('article').querySelector('[data-comments]');
    box.hidden = !box.hidden;
    toggle.setAttribute('aria-expanded', String(!box.hidden));
    if (!box.hidden) {
        loadComments(box);
        setTimeout(() => box.querySelector('textarea')?.focus(), 300);
    }
});

document.querySelectorAll('[data-comments-autoload]').forEach(loadComments);

document.addEventListener('submit', async (event) => {
    const form = event.target.closest('form[data-comment-form]');
    if (!form) return;
    event.preventDefault();
    if (form.dataset.confirm && !confirm(form.dataset.confirm)) return;

    const box = form.closest('[data-comments]');
    try {
        const response = await fetch(form.action, {
            method: 'POST',
            headers: { Accept: 'text/html', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrfToken },
            body: new FormData(form),
        });
        if (response.status === 401) return (window.location.href = document.body.dataset.loginUrl);
        if (!response.ok) throw new Error();
        box.innerHTML = await response.text();
        updateCommentCount(box);
    } catch {
        form.submit();
    }
});

// Enter sends the comment, Shift+Enter makes a new line
document.addEventListener('keydown', (event) => {
    const textarea = event.target.closest('form[data-comment-form] textarea');
    if (textarea && event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        textarea.form.requestSubmit();
    }
});
