(function () {
  var deferredPrompt = null;
  var installButton = null;

  function isMobile() {
    return window.matchMedia('(max-width: 576px)').matches ||
      /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
  }

  function isStandalone() {
    return window.matchMedia('(display-mode: standalone)').matches ||
      window.navigator.standalone === true;
  }

  function showInstallButton() {
    if (installButton && isMobile() && !isStandalone()) {
      installButton.classList.add('is-visible');
    }
  }

  function hideInstallButton() {
    if (installButton) {
      installButton.classList.remove('is-visible');
    }
  }

  function ready(callback) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', callback);
      return;
    }

    callback();
  }

  if ('serviceWorker' in navigator) {
    window.addEventListener('load', function () {
      navigator.serviceWorker.register('./service-worker.js').catch(function (error) {
        console.warn('Falha ao registrar o service worker:', error);
      });
    });
  }

  window.addEventListener('beforeinstallprompt', function (event) {
    event.preventDefault();
    deferredPrompt = event;
    showInstallButton();
  });

  window.addEventListener('appinstalled', function () {
    deferredPrompt = null;
    hideInstallButton();
  });

  ready(function () {
    installButton = document.getElementById('pwaInstallButton');

    if (!installButton) {
      return;
    }

    showInstallButton();

    installButton.addEventListener('click', function () {
      if (deferredPrompt) {
        deferredPrompt.prompt();
        deferredPrompt.userChoice.then(function (choiceResult) {
          if (choiceResult.outcome === 'accepted') {
            hideInstallButton();
          }
          deferredPrompt = null;
        });
        return;
      }

      if (/iPhone|iPad|iPod/i.test(navigator.userAgent)) {
        window.alert('Para instalar no iPhone, toque em Compartilhar e depois em Adicionar a Tela de Inicio.');
        return;
      }

      window.alert('Para instalar, abra o menu do navegador e escolha Instalar app ou Adicionar a tela inicial.');
    });
  });
})();
