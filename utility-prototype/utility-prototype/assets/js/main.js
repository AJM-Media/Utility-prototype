(function () {
  const $ = (sel, ctx = document) => ctx.querySelector(sel);
  const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));

  
  function formatGBP(value) {
    const n = Number(value) || 0;

    try {
      return new Intl.NumberFormat('en-GB', {
        style: 'currency',
        currency: 'GBP',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
      }).format(n);
    } catch (e) {
      const sym = (window.UtilityPrototype && UtilityPrototype.currencySymbol) || '£';
      const fixed = (Math.round(n * 100) / 100).toFixed(2);
      const parts = fixed.split('.');
      parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ',');
      return sym + parts.join('.');
    }
  }

  function readProductData() {
    const el = document.getElementById('utility-product-data');
    if (!el) return null;
    try { return JSON.parse(el.textContent || ''); } catch (e) { return null; }
  }

  function toast(msg) {
    const t = $('#toast');
    const m = $('#toastMsg');
    if (!t || !m) return;

    m.textContent = msg;
    t.hidden = false;
    window.clearTimeout(window.__uToastT);
    window.__uToastT = window.setTimeout(() => { t.hidden = true; }, 2600);
  }

  function initGallery() {
    const stageImg = $('#galleryImage');
    const thumbs = $$('.thumb');
    if (!stageImg || !thumbs.length) return;

    thumbs.forEach((btn) => {
      btn.addEventListener('click', () => {
        const src = btn.dataset.src || '';
        const alt = btn.dataset.alt || '';
        if (!src) return;

        stageImg.src = src;
        stageImg.alt = alt;

        thumbs.forEach((b) => b.classList.remove('is-active'));
        btn.classList.add('is-active');
      });
    });
  }

  function setGalleryImageFromOption(selectEl) {
    const stageImg = $('#galleryImage');
    if (!stageImg || !selectEl) return;

    const opt = selectEl.selectedOptions && selectEl.selectedOptions[0];
    const img = opt && opt.dataset ? (opt.dataset.image || '') : '';
    if (!img) return;

    stageImg.src = img;
  }

  function getSelections(form) {
    return $$('select[data-group]', form).map((sel) => {
      const group = sel.dataset.group || sel.name || '';
      const opt = sel.selectedOptions && sel.selectedOptions[0];

      const value = sel.value || '';
      const label = opt
        ? (opt.dataset && opt.dataset.label ? opt.dataset.label : (opt.textContent || '').trim())
        : '';

      const price = opt && opt.dataset ? (Number(opt.dataset.price) || 0) : 0;

      return { group, value, label, price };
    });
  }

  function getAddons(selections) {
    return selections.reduce((sum, s) => sum + (Number(s.price) || 0), 0);
  }

  function escapeHtml(str) {
    return String(str)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  function updateSummary(selections) {
    const list = $('#selectionSummary');
    if (!list) return;

    const picked = selections.filter((s) => s.value !== '');

    if (!picked.length) {
      list.hidden = true;
      list.innerHTML = '';
      return;
    }

    const labelMap = {
      fabric: 'Fabric',
      colour: 'Colour',
      legFinish: 'Leg Finish',
      seatCushion: 'Optional seat cushion'
    };

    list.innerHTML = picked.map((s) => {
      const groupLabel = labelMap[s.group] || s.group;
      const pricePart = s.price > 0 ? ` <span class="muted">(+ ${formatGBP(s.price)})</span>` : '';
      return `<li>${groupLabel}: ${escapeHtml(s.label)}${pricePart}</li>`;
    }).join('');

    list.hidden = false;
  }

  function updatePrices(base, qty, addons) {
    const totalEl = $('#priceTotal');
    const unitEl = $('#priceUnit');
    const unitRow = $('#unitRow');

    const unit = base + addons;

    if (unitEl) unitEl.textContent = formatGBP(unit);
    if (unitRow) unitRow.hidden = !(addons > 0);

    if (totalEl) totalEl.textContent = formatGBP(unit * qty);
  }

  function initQty(recalc) {
    const qtyEl = $('#qty');
    const minus = $('#qtyMinus');
    const plus = $('#qtyPlus');

    if (!qtyEl) return;

    const clamp = () => {
      const v = Math.max(1, parseInt(qtyEl.value || '1', 10) || 1);
      qtyEl.value = String(v);
      recalc();
    };

    qtyEl.addEventListener('input', clamp);
    qtyEl.addEventListener('blur', clamp);

    if (minus) {
      minus.addEventListener('click', () => {
        qtyEl.value = String(Math.max(1, (parseInt(qtyEl.value || '1', 10) || 1) - 1));
        recalc();
      });
    }

    if (plus) {
      plus.addEventListener('click', () => {
        qtyEl.value = String((parseInt(qtyEl.value || '1', 10) || 1) + 1);
        recalc();
      });
    }
  }

  function initLike() {
    const btn = $('#likeBtn');
    if (!btn) return;

    btn.addEventListener('click', () => {
      const on = btn.getAttribute('aria-pressed') === 'true';
      btn.setAttribute('aria-pressed', on ? 'false' : 'true');
      btn.classList.toggle('is-active', !on);
      toast(on ? 'Removed from favourites' : 'Saved to favourites');
    });
  }

  function initShare() {
    const nativeBtn = $('#shareNative');
    const copyBtn = $('#shareCopy');

    if (nativeBtn) {
      nativeBtn.addEventListener('click', async () => {
        const url = window.location.href;
        const title = document.title || 'Product';

        if (navigator.share) {
          try {
            await navigator.share({ title, url });
          } catch (e) {}
        } else {
          toast('Sharing not supported on this device');
        }
      });
    }

    if (copyBtn) {
      copyBtn.addEventListener('click', async () => {
        const url = window.location.href;

        try {
          if (navigator.clipboard && navigator.clipboard.writeText) {
            await navigator.clipboard.writeText(url);
            toast('Link copied');
            return;
          }
        } catch (e) {}

        const t = document.createElement('textarea');
        t.value = url;
        t.setAttribute('readonly', '');
        t.style.position = 'absolute';
        t.style.left = '-9999px';
        document.body.appendChild(t);
        t.select();
        document.execCommand('copy');
        document.body.removeChild(t);
        toast('Link copied');
      });
    }
  }

  function initSwatchSelects(form) {
    const selects = $$('select[data-group]', form);

    function getOptLabel(opt) {
      if (!opt) return '';
      if (opt.dataset && opt.dataset.label) return opt.dataset.label;
      return (opt.textContent || '').trim();
    }

    function getOptPrice(opt) {
      if (!opt || !opt.dataset) return 0;
      return Number(opt.dataset.price) || 0;
    }

    function priceLine(opt) {
      const p = getOptPrice(opt);
      return p > 0 ? `+ ${formatGBP(p)}` : '';
    }

    selects.forEach((sel) => {
      const options = Array.from(sel.options || []);
      const placeholderOpt = options.find((o) => o.disabled && o.value === '');

      const wrap = document.createElement('div');
      wrap.className = 'u-select';

      sel.classList.add('u-select__native');

      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'u-select__button';
      btn.setAttribute('aria-haspopup', 'listbox');
      btn.setAttribute('aria-expanded', 'false');

      const value = document.createElement('span');
      value.className = 'u-select__value';

      const sw = document.createElement('span');
      sw.className = 'u-select__swatch';
      sw.hidden = true;

      const textWrap = document.createElement('span');
      textWrap.className = 'u-select__text';

      const txt = document.createElement('span');
      txt.className = 'u-select__label';
      txt.textContent = placeholderOpt ? (placeholderOpt.textContent || 'Select') : 'Select';

      const meta = document.createElement('span');
      meta.className = 'u-select__meta';
      meta.hidden = true;

      textWrap.appendChild(txt);
      textWrap.appendChild(meta);

      value.appendChild(sw);
      value.appendChild(textWrap);

const chev = document.createElement('span');
chev.className = 'u-select__chev';
chev.setAttribute('aria-hidden', 'true');


      btn.appendChild(value);
      btn.appendChild(chev);

      const list = document.createElement('ul');
      list.className = 'u-select__list';
      list.hidden = true;
      list.setAttribute('role', 'listbox');

      function closeList() {
        list.hidden = true;
        btn.setAttribute('aria-expanded', 'false');
      }

      function openList() {
        list.hidden = false;
        btn.setAttribute('aria-expanded', 'true');
        const current = list.querySelector('[aria-selected="true"]') || list.querySelector('[role="option"]');
        current && current.focus();
      }

      function setValueFromOption(optEl) {
        txt.textContent = getOptLabel(optEl);

        const pLine = priceLine(optEl);
        if (pLine) {
          meta.textContent = pLine;
          meta.hidden = false;
        } else {
          meta.textContent = '';
          meta.hidden = true;
        }

        const swatch = optEl.dataset ? (optEl.dataset.swatch || '') : '';
        if (swatch) {
          sw.style.backgroundImage = `url("${swatch}")`;
          sw.hidden = false;
        } else {
          sw.hidden = true;
          sw.style.backgroundImage = '';
        }
      }

      options.forEach((opt) => {
        if (!opt.value) return;

        const li = document.createElement('li');
        li.className = 'u-select__option';
        li.setAttribute('role', 'option');
        li.setAttribute('tabindex', '-1');
        li.dataset.value = opt.value;

        const swOpt = document.createElement('span');
        swOpt.className = 'u-select__swatch';

        const swatch = opt.dataset ? (opt.dataset.swatch || '') : '';
        if (swatch) swOpt.style.backgroundImage = `url("${swatch}")`;
        else swOpt.hidden = true;

        const text = document.createElement('span');
        text.className = 'u-select__text';

        const label = document.createElement('span');
        label.className = 'u-select__label';
        label.textContent = getOptLabel(opt);

        const metaLine = document.createElement('span');
        metaLine.className = 'u-select__meta';
        const pLine = priceLine(opt);
        if (pLine) {
          metaLine.textContent = pLine;
          metaLine.hidden = false;
        } else {
          metaLine.hidden = true;
        }

        text.appendChild(label);
        text.appendChild(metaLine);

        li.appendChild(swOpt);
        li.appendChild(text);

        li.setAttribute('aria-selected', sel.value === opt.value ? 'true' : 'false');

        li.addEventListener('click', () => {
          sel.value = opt.value;
          sel.dispatchEvent(new Event('change', { bubbles: true }));

          list.querySelectorAll('[role="option"]').forEach((n) => n.setAttribute('aria-selected', 'false'));
          li.setAttribute('aria-selected', 'true');

          setValueFromOption(opt);
          closeList();
          btn.focus();
        });

        li.addEventListener('keydown', (e) => {
          const items = Array.from(list.querySelectorAll('[role="option"]'));
          const idx = items.indexOf(li);

          if (e.key === 'ArrowDown') {
            e.preventDefault();
            items[Math.min(items.length - 1, idx + 1)]?.focus();
          } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            items[Math.max(0, idx - 1)]?.focus();
          } else if (e.key === 'Enter' || e.key === ' ') {
            e.preventDefault();
            li.click();
          } else if (e.key === 'Escape') {
            e.preventDefault();
            closeList();
            btn.focus();
          }
        });

        list.appendChild(li);
      });

      btn.addEventListener('click', () => {
        const open = btn.getAttribute('aria-expanded') === 'true';
        open ? closeList() : openList();
      });

      document.addEventListener('click', (e) => {
        if (!wrap.contains(e.target)) closeList();
      });

      sel.parentNode.insertBefore(wrap, sel);
      wrap.appendChild(sel);
      wrap.appendChild(btn);
      wrap.appendChild(list);

      if (sel.value) {
        const opt = sel.selectedOptions && sel.selectedOptions[0];
        opt && setValueFromOption(opt);
        const current = list.querySelector(`[data-value="${sel.value}"]`);
        current && current.setAttribute('aria-selected', 'true');
      }
    });
  }

  function areAllSelectionsComplete(form) {
  const selects = $$('select[data-group]', form);
  return selects.every((sel) => sel.value !== '');
}


  document.addEventListener('DOMContentLoaded', () => {
    const product = readProductData() || {};
    const form = $('#configForm');
    const baseFrom = $('#priceFrom');

    if (!form || !baseFrom) return;

    const base = Number(product.basePrice) || Number(baseFrom.textContent.replace(/[^0-9.]/g, '')) || 0;

const addBtn = $('#addToBasket');

const recalc = () => {
  const selections = getSelections(form);
  const addons = getAddons(selections);
  const qty = Math.max(1, parseInt($('#qty')?.value || '1', 10) || 1);

  updateSummary(selections);
  updatePrices(base, qty, addons);

  const ready = areAllSelectionsComplete(form);
  if (addBtn) addBtn.disabled = !ready;
};


    $$('.thumb').length && initGallery();

    initSwatchSelects(form);

    $$('select[data-group]', form).forEach((sel) => {
      sel.addEventListener('change', () => {
        setGalleryImageFromOption(sel);
        recalc();
      });

      if (sel.value) setGalleryImageFromOption(sel);
    });

    initQty(recalc);
    initLike();
    initShare();

    form.addEventListener('submit', (e) => {
      e.preventDefault();
      toast('Added to basket (prototype)');
    });

    recalc();
  });
})();


(function () {
  const toast = document.getElementById("mini-toast");
  const closeBtn = toast?.querySelector("[data-close-mini]");
  if (!toast) return;

  const SHOW_DELAY_MS = 5000;
  const AUTO_CLOSE_MS = 10000;
  const KEY_SHOWN = "miniToastShown";

  let lastFocused = null;
  let autoCloseTimer = null;
  let remainingTime = AUTO_CLOSE_MS;
  let startTime = null;

  function startAutoClose() {
    clearTimeout(autoCloseTimer);
    startTime = Date.now();

    autoCloseTimer = setTimeout(() => {
      closeToast();
    }, remainingTime);
  }

  function pauseAutoClose() {
    if (!startTime) return;
    remainingTime -= Date.now() - startTime;
    clearTimeout(autoCloseTimer);
  }

  function resumeAutoClose() {
    if (remainingTime <= 0) {
      closeToast();
      return;
    }
    startAutoClose();
  }

  function openToast() {
    if (toast.classList.contains("is-open")) return;

    lastFocused = document.activeElement;
    remainingTime = AUTO_CLOSE_MS;

    toast.classList.add("is-open");
    toast.setAttribute("aria-hidden", "false");

    setTimeout(() => (closeBtn || toast).focus(), 0);

    document.addEventListener("keydown", onKeydown);

    startAutoClose();
  }

  function closeToast() {
    toast.classList.remove("is-open");
    toast.setAttribute("aria-hidden", "true");

    document.removeEventListener("keydown", onKeydown);
    clearTimeout(autoCloseTimer);

    if (lastFocused && typeof lastFocused.focus === "function") {
      lastFocused.focus();
    }
  }

  function onKeydown(e) {
    if (e.key === "Escape") closeToast();
  }

  closeBtn?.addEventListener("click", (e) => {
    e.preventDefault();
    closeToast();
  });

  toast.addEventListener("mouseenter", pauseAutoClose);
  toast.addEventListener("mouseleave", resumeAutoClose);

  toast.addEventListener("focusin", pauseAutoClose);
  toast.addEventListener("focusout", resumeAutoClose);

  if (!sessionStorage.getItem(KEY_SHOWN)) {
    setTimeout(() => {
      openToast();
      sessionStorage.setItem(KEY_SHOWN, "1");
    }, SHOW_DELAY_MS);
  }
})();
