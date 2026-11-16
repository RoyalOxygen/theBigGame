(function () {
  var deferredPrompt = null;
  var installed = window.matchMedia("(display-mode: standalone)").matches || window.navigator.standalone === true;

  function $(sel, root) {
    return (root || document).querySelector(sel);
  }

  function $all(sel, root) {
    return Array.prototype.slice.call((root || document).querySelectorAll(sel));
  }

  function isIos() {
    return /iphone|ipad|ipod/i.test(navigator.userAgent) || (navigator.platform === "MacIntel" && navigator.maxTouchPoints > 1);
  }

  function isSafari() {
    return /safari/i.test(navigator.userAgent) && !/crios|fxios|edgios|chrome|android/i.test(navigator.userAgent);
  }

  function setStatus(state) {
    $all("[data-install-app]").forEach(function (btn) {
      if (state === "installed") {
        btn.hidden = true;
      } else {
        btn.hidden = false;
        btn.disabled = false;
        btn.removeAttribute("aria-disabled");
      }
    });
    var banner = $("#installBanner");
    if (banner) {
      if (state === "available" && !sessionStorage.getItem("narfcard-install-dismissed")) {
        banner.hidden = false;
      } else if (state === "installed") {
        banner.hidden = true;
      }
    }
    var ios = $("#iosInstallHint");
    if (ios) {
      ios.hidden = !(state === "ios" && !installed);
    }
    var installedNote = $("#installInstalled");
    if (installedNote) {
      installedNote.hidden = state !== "installed";
    }
  }

  function showIosHelp() {
    var el = $("#iosInstallModal");
    if (el && window.bootstrap) {
      bootstrap.Modal.getOrCreateInstance(el).show();
    } else if (el) {
      el.hidden = false;
    }
  }

  function triggerInstall() {
    if (installed) {
      return;
    }
    if (deferredPrompt) {
      deferredPrompt.prompt();
      deferredPrompt.userChoice.then(function (choice) {
        deferredPrompt = null;
        if (choice && choice.outcome === "accepted") {
          setStatus("installed");
        }
      });
      return;
    }
    if (isIos()) {
      showIosHelp();
      return;
    }
    showIosHelp();
  }

  window.addEventListener("beforeinstallprompt", function (event) {
    event.preventDefault();
    deferredPrompt = event;
    if (!installed) {
      setStatus("available");
    }
  });

  window.addEventListener("appinstalled", function () {
    installed = true;
    deferredPrompt = null;
    setStatus("installed");
  });

  document.addEventListener("click", function (event) {
    var btn = event.target.closest("[data-install-app]");
    if (btn) {
      event.preventDefault();
      triggerInstall();
    }
    if (event.target.closest("[data-install-dismiss]")) {
      var banner = $("#installBanner");
      if (banner) {
        banner.hidden = true;
      }
      sessionStorage.setItem("narfcard-install-dismissed", "1");
    }
  });

  if ("serviceWorker" in navigator) {
    navigator.serviceWorker.register((window.BASE_URL || "") + "/sw.js").catch(function () {});
  }

  if (installed) {
    setStatus("installed");
  } else if (isIos()) {
    setStatus("ios");
  }
})();
