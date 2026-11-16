(function () {
  document.querySelectorAll("[data-balance-toggle]").forEach(function (btn) {
    btn.addEventListener("click", function () {
      document.querySelectorAll("[data-balance-value]").forEach(function (el) {
        if (el.dataset.hidden === "0") {
          el.textContent = "****";
          el.dataset.hidden = "1";
        } else {
          el.textContent = "NGN " + el.getAttribute("data-balance-value");
          el.dataset.hidden = "0";
        }
      });
    });
  });

  function loadLgas(stateId, target, selected) {
    if (!stateId || !target) return;
    var select = typeof target === "string" ? document.getElementById(target) : target;
    if (!select) return;
    var base = window.BASE_URL || "";
    fetch(base + "/api/lgas.php?state_id=" + encodeURIComponent(stateId), { headers: { Accept: "application/json" } })
      .then(function (r) { return r.json(); })
      .then(function (data) {
        select.innerHTML = '<option value="">Select LGA</option>';
        (data.lgas || []).forEach(function (lga) {
          var opt = document.createElement("option");
          opt.value = lga.id;
          opt.textContent = lga.name;
          if (String(selected) === String(lga.id)) opt.selected = true;
          select.appendChild(opt);
        });
      })
      .catch(function () {});
  }

  document.querySelectorAll("[data-lga-target]").forEach(function (el) {
    el.addEventListener("change", function () {
      loadLgas(el.value, el.getAttribute("data-lga-target"));
    });
  });

  var verifyBtn = document.getElementById("verifyAccount");
  if (verifyBtn) {
    verifyBtn.addEventListener("click", function () {
      var bank = document.getElementById("bank_id");
      var acct = document.getElementById("account_number");
      var name = document.getElementById("account_name");
      var result = document.getElementById("verifyResult");
      var verified = document.getElementById("account_verified");
      var opt = bank.options[bank.selectedIndex];
      var code = opt ? opt.getAttribute("data-code") : "";
      result.textContent = "Verifying...";
      fetch((window.BASE_URL || "") + "/api/resolve-account.php", {
        method: "POST",
        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": document.querySelector("input[name=_csrf]").value },
        body: JSON.stringify({ account_number: acct.value, bank_code: code })
      })
        .then(function (r) { return r.json(); })
        .then(function (data) {
          if (data.status && data.data && data.data.account_name) {
            name.value = data.data.account_name;
            verified.value = "1";
            result.textContent = "Account name resolved: " + data.data.account_name + (data.demo ? " (development verification)" : "");
            result.className = "small text-success";
          } else {
            result.textContent = data.message || "Unable to verify account.";
            result.className = "small text-danger";
          }
        })
        .catch(function () {
          result.textContent = "Verification request failed.";
          result.className = "small text-danger";
        });
    });
  }
})();
