function fetchPartials(url, onSucess) {
  const xhr = new XMLHttpRequest();

  xhr.open("GET", url);

  xhr.addEventListener("load", () => {
    onSucess(xhr.responseText);
  });

  xhr.send();
}

const issuesEl = document.querySelector("#issues");
document.addEventListener("click", (event) => {
  const target = event.target;
  const partialLink = target.closest("[data-partial]");
  if (!partialLink) return;

  const path = partialLink.getAttribute("href");
  if (!path) return;

  event.preventDefault();
  const url = new URL(path, window.location.origin);
  url.searchParams.set("partial", "true");

  fetchPartials(url.toString(), onSucess, onLoading);

  window.history.replaceState(null, "", path);
});

function onSucess(partial) {
  issuesEl.innerHTML = partial;

  if (window.formatDate) formatDate();
  if (window.removeAllElementClasses) removeAllElementClasses();
}
