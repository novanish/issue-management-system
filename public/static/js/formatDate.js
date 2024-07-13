function onLoad(fn) {
  if (document.readyState !== "loading") return fn();
  document.addEventListener("DOMContentLoaded", function loadHandler() {
    fn();
    document.removeEventListener("load", loadHandler);
  });
}

function removeClasses(el) {
  if (!el.hasAttribute("data-remove-class")) return;

  const classNamesToRemove = el.dataset.removeClass
    .split(",")
    .map((s) => s.trim());
  el.classList.remove(...classNamesToRemove);
  el.removeAttribute("style");
  el.removeAttribute("data-remove-class");
}

function removeAllElementClasses() {
  document.querySelectorAll("[data-remove-class]").forEach(removeClasses);
}

function formatDate() {
  document.querySelectorAll("[data-date]").forEach((el) => {
    const dataDate = el.dataset.date;
    const dataTimezone = el.dataset.timezone;
    const format = el.dataset.dateFormat;

    const dateFormat = {
      month: "long",
      year: "numeric",
      day: "2-digit",
    };

    const dateTimeFormat = Object.assign(
      {
        hour: "2-digit",
        hour12: true,
        minute: "2-digit",
      },
      dateFormat
    );

    el.innerText = new Date(dataDate + " " + dataTimezone).toLocaleDateString(
      "en-US",
      format === "DATETIME" ? dateTimeFormat : dateFormat
    );

    removeClasses(el);
  });
}

onLoad(formatDate);
onLoad(removeAllElementClasses);
