import { initializeApp } from 'firebase/app';
import { getAuth, GoogleAuthProvider, signInWithPopup } from 'firebase/auth';

function getConfig() {
  const config = window.__FIREBASE_WEB_CONFIG__;
  if (!config?.apiKey || !config?.authDomain || !config?.projectId || !config?.appId) {
    return null;
  }

  return config;
}

function csrfToken() {
  return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
}

function postIdToken(idToken, continueUrl) {
  const form = document.createElement('form');
  form.method = 'POST';
  form.action = '/auth/firebase';
  form.style.display = 'none';

  const fields = {
    _token: csrfToken(),
    id_token: idToken,
  };

  if (continueUrl) {
    fields.continue = continueUrl;
  }

  for (const [name, value] of Object.entries(fields)) {
    const input = document.createElement('input');
    input.type = 'hidden';
    input.name = name;
    input.value = value;
    form.appendChild(input);
  }

  document.body.appendChild(form);
  form.submit();
}

function setBusy(button, busy) {
  button.disabled = busy;
  button.setAttribute('aria-busy', busy ? 'true' : 'false');
}

async function signInWithGoogle(button) {
  const config = getConfig();
  if (!config) {
    window.alert('Google sign-in is not configured on this site yet.');
    return;
  }

  const continueUrl = button.dataset.continue?.trim() || '';

  setBusy(button, true);

  try {
    const app = initializeApp(config);
    const auth = getAuth(app);
    const provider = new GoogleAuthProvider();
    provider.setCustomParameters({ prompt: 'select_account' });

    const result = await signInWithPopup(auth, provider);
    const idToken = await result.user.getIdToken(true);

    postIdToken(idToken, continueUrl);
  } catch (error) {
    setBusy(button, false);

    const code = error?.code ?? '';
    if (code === 'auth/popup-closed-by-user' || code === 'auth/cancelled-popup-request') {
      return;
    }

    const message =
      error?.message ??
      'Google sign-in failed. Try again, or sign in with your username and password.';
    window.alert(message);
  }
}

function bindGoogleButtons() {
  document.querySelectorAll('[data-firebase-google-sign-in]').forEach((button) => {
    if (button.dataset.firebaseBound === '1') {
      return;
    }

    button.dataset.firebaseBound = '1';
    button.addEventListener('click', () => {
      signInWithGoogle(button);
    });
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', bindGoogleButtons);
} else {
  bindGoogleButtons();
}

document.addEventListener('alpine:initialized', bindGoogleButtons);
