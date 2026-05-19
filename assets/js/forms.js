/**
 * CVAT Brasil — forms.js
 * Validação em tempo real e submit via API PHP para o formulário
 * de diagnóstico. Sem reload.
 */

/* ── REGRAS DE VALIDAÇÃO ────────────────────────────────────── */

const RULES = {
  nome: {
    required: true,
    minLength: 3,
    messages: {
      required:  'Por favor, informe seu nome completo.',
      minLength: 'O nome deve ter ao menos 3 caracteres.',
    },
  },
  email: {
    required: true,
    pattern:  /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/,
    messages: {
      required: 'Por favor, informe seu e-mail corporativo.',
      pattern:  'Insira um e-mail válido (ex: voce@empresa.com.br).',
    },
  },
  telefone: {
    required: true,
    pattern:  /^\(?\d{2}\)?\s?9?\d{4}[-\s]?\d{4}$/,
    messages: {
      required: 'Por favor, informe seu WhatsApp.',
      pattern:  'Formato inválido. Ex: (11) 9 9999-9999.',
    },
  },
  cargo: {
    required: true,
    messages: { required: 'Selecione seu cargo.' },
  },
  empresa: {
    required: true,
    minLength: 2,
    messages: {
      required:  'Por favor, informe o nome da empresa.',
      minLength: 'Nome muito curto.',
    },
  },
  setor: {
    required: true,
    messages: { required: 'Selecione o setor de atuação.' },
  },
  colaboradores: {
    required: true,
    messages: { required: 'Selecione o número de colaboradores.' },
  },
  interesse: {
    required: true,
    messages: { required: 'Selecione seu interesse principal.' },
  },
  lgpd: {
    required: true,
    messages: { required: 'Você precisa aceitar a política de privacidade para continuar.' },
  },
};

/* ── HELPERS ────────────────────────────────────────────────── */

function getField(name) {
  return document.querySelector(`[name="${name}"]`);
}

function getError(name) {
  return document.getElementById(`${name}-error`);
}

function setFieldState(field, valid, message = '') {
  if (!field) return;

  field.classList.toggle('error',   !valid);
  field.classList.toggle('success',  valid);

  const errorEl = getError(field.name || field.id?.replace('-error', ''));
  if (errorEl) {
    errorEl.textContent = valid ? '' : message;
    errorEl.classList.toggle('visible', !valid);
  }
}

function validateField(name) {
  const rule  = RULES[name];
  if (!rule) return true;

  const field = getField(name);
  if (!field) return true;

  const value = field.type === 'checkbox'
    ? field.checked
    : field.value.trim();

  if (rule.required && !value) {
    setFieldState(field, false, rule.messages.required);
    return false;
  }

  if (rule.minLength && typeof value === 'string' && value.length < rule.minLength) {
    setFieldState(field, false, rule.messages.minLength);
    return false;
  }

  if (rule.pattern && typeof value === 'string' && !rule.pattern.test(value)) {
    setFieldState(field, false, rule.messages.pattern);
    return false;
  }

  setFieldState(field, true);
  return true;
}

/* Valida grupo de radio buttons */
function validateRadioGroup(name) {
  const rule    = RULES[name];
  const checked = document.querySelector(`[name="${name}"]:checked`);
  const errorEl = document.getElementById(`${name}-error`);

  if (rule?.required && !checked) {
    if (errorEl) {
      errorEl.textContent = rule.messages.required;
      errorEl.classList.add('visible');
    }
    return false;
  }

  if (errorEl) {
    errorEl.textContent = '';
    errorEl.classList.remove('visible');
  }
  return true;
}

/* ── MÁSCARA DE TELEFONE ─────────────────────────────────────── */

function maskPhone(value) {
  const digits = value.replace(/\D/g, '').slice(0, 11);
  if (digits.length <= 2)  return `(${digits}`;
  if (digits.length <= 7)  return `(${digits.slice(0,2)}) ${digits.slice(2)}`;
  if (digits.length <= 11) return `(${digits.slice(0,2)}) ${digits.slice(2,7)}-${digits.slice(7)}`;
  return value;
}

/* ── BARRA DE PROGRESSO ─────────────────────────────────────── */

function updateProgress() {
  const totalFields = Object.keys(RULES).length;
  let filled = 0;

  Object.keys(RULES).forEach(name => {
    const field = getField(name);
    if (!field) return;

    if (field.type === 'checkbox') {
      if (field.checked) filled++;
    } else if (field.type === 'radio') {
      if (document.querySelector(`[name="${name}"]:checked`)) filled++;
    } else if (field.value.trim()) {
      filled++;
    }
  });

  const pct = Math.round((filled / totalFields) * 100);
  const bar  = document.getElementById('diag-progress-bar');
  const wrap = document.getElementById('diag-progress');
  if (bar)  bar.style.width = `${pct}%`;
  if (wrap) wrap.setAttribute('aria-valuenow', pct);
}

/* ── CONTADOR DE CARACTERES ─────────────────────────────────── */

function initCharCounter() {
  const textarea = document.getElementById('desafio');
  const counter  = document.getElementById('desafio-count');
  if (!textarea || !counter) return;

  textarea.addEventListener('input', () => {
    counter.textContent = `${textarea.value.length} / 500`;
  });
}

/* ── INTERATIVIDADE DOS INTEREST CARDS ──────────────────────── */

function initInterestCards() {
  document.querySelectorAll('.diag-interest-option input[type="radio"]').forEach(radio => {
    radio.addEventListener('change', () => {
      document.querySelectorAll('.diag-interest-card').forEach(card => {
        card.classList.remove('selected');
      });
      radio.nextElementSibling?.classList.add('selected');
      validateRadioGroup('interesse');
      updateProgress();
    });
  });
}

/* ── SUBMIT ─────────────────────────────────────────────────── */

async function handleSubmit(e) {
  e.preventDefault();
  const form = e.target;

  /* Valida todos os campos */
  const textFields   = ['nome','email','telefone','cargo','empresa','setor','colaboradores','lgpd'];
  const radioFields  = ['interesse'];

  const textValid  = textFields.every(validateField);
  const radioValid = radioFields.every(validateRadioGroup);

  if (!textValid || !radioValid) {
    /* Foca o primeiro campo inválido */
    const firstError = form.querySelector('.form-input.error, .form-select.error, .form-textarea.error, input[required]:not(:checked)');
    firstError?.focus();
    return;
  }

  /* Bloqueia botão */
  const btn     = document.getElementById('diag-submit');
  const btnText = document.getElementById('diag-submit-text');
  if (btn) {
    btn.disabled = true;
    btn.classList.add('loading');
  }
  if (btnText) btnText.textContent = 'Enviando…';

  /* Coleta dados */
  const data = Object.fromEntries(new FormData(form).entries());
  delete data['_gotcha'];

  try {
    const res = await fetch(form.action, {
      method:  'POST',
      headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
      body:    JSON.stringify(data),
    });

    if (res.ok) {
      showSuccess();
    } else {
      const body = await res.json().catch(() => ({}));
      throw new Error(body?.error || `HTTP ${res.status}`);
    }
  } catch (err) {
    console.error('[CVAT forms]', err);
    if (window.CVAT?.showToast) {
      window.CVAT.showToast('Erro ao enviar. Tente novamente em instantes.', 'error');
    }
    if (btn) {
      btn.disabled = false;
      btn.classList.remove('loading');
    }
    if (btnText) btnText.textContent = 'Solicitar diagnóstico gratuito';
  }
}

function showSuccess() {
  const form    = document.getElementById('diag-form');
  const success = document.getElementById('diag-success');
  if (form)    form.classList.add('hidden');
  if (success) success.classList.remove('hidden');

  success?.scrollIntoView({ behavior: 'smooth', block: 'center' });
  success?.focus();

  /* Registra conversão se gtag disponível */
  if (typeof gtag === 'function') {
    gtag('event', 'lead', { event_category: 'diagnostico', event_label: 'form_submit' });
  }
}

/* ── INIT ────────────────────────────────────────────────────── */

function initDiagForm() {
  const form = document.getElementById('diag-form');
  if (!form) return;

  /* Validação em tempo real */
  ['nome','email','empresa'].forEach(name => {
    getField(name)?.addEventListener('blur',  () => { validateField(name); updateProgress(); });
    getField(name)?.addEventListener('input', () => {
      if (getField(name)?.classList.contains('error')) validateField(name);
      updateProgress();
    });
  });

  ['cargo','setor','colaboradores'].forEach(name => {
    getField(name)?.addEventListener('change', () => { validateField(name); updateProgress(); });
  });

  /* Máscara telefone */
  const tel = getField('telefone');
  if (tel) {
    tel.addEventListener('input', () => {
      tel.value = maskPhone(tel.value);
      if (tel.classList.contains('error')) validateField('telefone');
      updateProgress();
    });
    tel.addEventListener('blur', () => validateField('telefone'));
  }

  /* Checkbox LGPD */
  const lgpd = getField('lgpd');
  if (lgpd) {
    lgpd.addEventListener('change', () => { validateField('lgpd'); updateProgress(); });
  }

  /* Submit */
  form.addEventListener('submit', handleSubmit);

  /* Módulos auxiliares */
  initInterestCards();
  initCharCounter();
}

/* Executa após o DOM */
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initDiagForm);
} else {
  initDiagForm();
}
