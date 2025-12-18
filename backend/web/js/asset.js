// update custom file input label
document.addEventListener("change", function (e) {
  if (e.target.classList.contains("custom-file-input")) {
    if (e.target.files.length > 0) {
      e.target.nextElementSibling.innerText = e.target.files[0].name;
    }
  }
});
