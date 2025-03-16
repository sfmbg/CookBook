document.addEventListener("DOMContentLoaded", function () {
  var resetButton = document.getElementById("resetButton");
  if (resetButton) {
    resetButton.addEventListener("click", function () {
      window.location.href = "index.php";
    });
  }
});
