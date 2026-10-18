(function () {
  'use strict';
  
  const defaults = {
    primary_color: '#0d6efd',
    button_color: '#0d6efd',
    header_color: '#ffffff',
    card_color: '#ffffff',
    background_color: '#f5f8fc',
    text_color: '#172b4d',
    font_color: '#172b4d',
    icon_color: '#0d6efd',
    border_color: '#e5eaf0'
  };

  const presets = {
    default: { ...defaults },
    ocean: { primary_color: '#087e8b', button_color: '#087e8b', header_color: '#ffffff', card_color: '#ffffff', background_color: '#edfafa', text_color: '#153b43', border_color: '#d3e9e9' },
    forest: { primary_color: '#28734b', button_color: '#28734b', header_color: '#ffffff', card_color: '#ffffff', background_color: '#f0f7f1', text_color: '#1e3929', border_color: '#d9e8dc' },
    royal: { primary_color: '#6246a5', button_color: '#6246a5', header_color: '#ffffff', card_color: '#ffffff', background_color: '#f5f2fc', text_color: '#2d2345', border_color: '#e4dcf4' },
    warm: { primary_color: '#b45309', button_color: '#b45309', header_color: '#ffffff', card_color: '#ffffff', background_color: '#fff8ed', text_color: '#422f1d', border_color: '#f0e2cd' },
    dark: { primary_color: '#60a5fa', button_color: '#2563eb', header_color: '#1e293b', card_color: '#1e293b', background_color: '#0f172a', text_color: '#e5edf8', border_color: '#334155' }
  };

  Object.keys(presets).forEach(name => {
    presets[name].font_color = presets[name].text_color;
    presets[name].icon_color = presets[name].primary_color;
  });

  const form = document.getElementById('sdmsThemeForm');
  if (!form) return;

  const keys = Object.keys(defaults);
  const status = document.getElementById('sdmsThemeClientStatus');
  let selectedPreset = 'default';

  function readTheme() { 
    const t = {}; 
    keys.forEach(k => {
      const input = form.elements.namedItem(k); 
      if (input) t[k] = input.value.toLowerCase();
    }); 
    return t; 
  }

  function showStatus(message, kind = 'info') { 
    if (!status) return; 
    status.className = 'sdms-theme-client-status ' + kind; 
    status.textContent = message; 
    status.hidden = false; 
  }

  function markSelected(name) { 
    selectedPreset = name; 
    document.querySelectorAll('.sdms-theme-preset').forEach(b => {
      const selected = b.dataset.preset === name;
      b.classList.toggle('selected', selected);
      b.setAttribute('aria-pressed', selected ? 'true' : 'false');
    }); 
  }

  function setTheme(theme, name = null) { 
    keys.forEach(k => {
      const input = form.elements.namedItem(k); 
      if (input && theme[k]) input.value = theme[k];
    }); 
    markSelected(name); 
    updatePreview(); 
    showStatus(name ? 'Template selected. Click “Apply and Save Theme” to apply it to everyone.' : 'Custom colors selected. Save to apply them to everyone.', 'info'); 
  }

  function contrastText(hex) {
    const n = parseInt(hex.slice(1), 16), r = n >> 16, g = (n >> 8) & 255, b = n & 255;
    return (0.299 * r + 0.587 * g + 0.114 * b) > 150 ? '#172b4d' : '#ffffff';
  }

  function updatePreview() {
    const t = readTheme();
    const preview = document.getElementById('sdmsThemePreview');
    if (preview) {
      preview.style.background = t.background_color;
      preview.style.color = t.text_color;
      preview.style.borderColor = t.border_color;
      
      const h = preview.querySelector('h3');
      const p = preview.querySelector('p');
      const button = preview.querySelector('button');
      const span = preview.querySelector('span');

      if (h) h.style.color = t.text_color;
      if (p) p.style.color = t.text_color;
      if (button) {
        button.style.background = t.button_color;
        button.style.color = contrastText(t.button_color);
      }
      if (span) span.style.color = t.primary_color; 
      preview.querySelectorAll('i').forEach(i => i.style.color = t.icon_color);
    }
    
    if (selectedPreset && presets[selectedPreset]) { 
      const matches = keys.every(k => presets[selectedPreset][k].toLowerCase() === t[k]); 
      if (!matches) markSelected(null); 
    }
  }

  document.querySelectorAll('.sdms-theme-preset').forEach(btn => 
    btn.addEventListener('click', () => {
      const name = btn.dataset.preset;
      if (presets[name]) setTheme(presets[name], name);
    })
  );

  document.querySelectorAll('[data-background]').forEach(btn => 
    btn.addEventListener('click', () => {
      const input = form.elements.namedItem('background_color');
      if (input) {
        input.value = btn.dataset.background;
        markSelected(null);
        updatePreview();
        showStatus('Background selected. Save to apply it to everyone.', 'info');
      }
    })
  );

  keys.forEach(k => {
    const input = form.elements.namedItem(k);
    if (input) {
      input.addEventListener('input', () => {
        markSelected(null);
        updatePreview();
        showStatus('Unsaved color changes. Save to apply them to everyone.', 'info');
      });
    }
  });

  form.addEventListener('submit', () => {
    const button = form.querySelector('button[type="submit"]');
    if (button) {
      button.disabled = true;
      button.dataset.originalText = button.innerHTML;
      button.textContent = 'Saving theme…';
    }
    showStatus('Saving theme settings…', 'info');
  });

  const resetForm = document.querySelector('form input[name="action"][value="reset"]')?.form;
  if (resetForm) {
    resetForm.addEventListener('submit', () => showStatus('Restoring the original theme…', 'info'));
  }

  const saved = document.querySelector('[data-theme-result="success"]');
  if (saved) { showStatus(saved.textContent.trim(), 'success'); }

  const failed = document.querySelector('[data-theme-result="error"]');
  if (failed) { showStatus(failed.textContent.trim(), 'error'); }

  const current = readTheme();
  const matching = Object.keys(presets).find(name => keys.every(k => presets[name][k].toLowerCase() === current[k]));
  markSelected(matching || null);
  updatePreview();
})();