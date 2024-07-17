function fetchPartials(url, onSucess) {
  const xhr = new XMLHttpRequest();

  xhr.open("GET", url);

  xhr.addEventListener("load", () => {
    onSucess(xhr.responseText);
  });

  xhr.send();
}

function fetchIssues(path) {
  const url = new URL(path, window.location.origin);
  url.searchParams.set("partial", "true");
  fetchPartials(url.toString(), onSucess);
  window.history.replaceState(null, "", path);
}

const issuesEl = document.querySelector("#issues");
document.addEventListener("click", (event) => {
  const target = event.target;
  const partialLink = target.closest("[data-partial]");
  if (!partialLink) return;

  const path = partialLink.getAttribute("href");
  if (!path) return;

  event.preventDefault();
  fetchIssues(path);
});

function onSucess(partial) {
  issuesEl.innerHTML = partial;

  if (window.formatDate) formatDate();
  if (window.removeAllElementClasses) removeAllElementClasses();
}

const form = document.querySelector("#filter-form");

form.addEventListener(
  "input",
  debounce(() => {
    const formData = new FormData(form);
    const searchParams = new URLSearchParams();

    for (const [key, value] of formData.entries()) {
      if (value) searchParams.set(key, value);
    }

    const path = window.location.pathname + "?" + searchParams.toString();
    fetchIssues(path);
  })
);

function debounce(fn, ms = 600) {
  let timeoutId = null;

  return (...args) => {
    if (timeoutId) clearTimeout(id);
    id = setTimeout(() => {
      fn(...args);
    }, ms);
  };
}
