document.addEventListener("DOMContentLoaded", () => {
  const el = document.getElementById("contentChart");
  if (!el) return;

  const data = JSON.parse(el.dataset.chart);

  new Chart(el.getContext("2d"), {
    type: "doughnut",
    data: {
      labels: ["Tracks", "Albums", "Playlists"],
      datasets: [
        {
          data: data,
          backgroundColor: ["#f39c12", "#00a65a", "#dd4b39"],
        },
      ],
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { position: "bottom" },
      },
    },
  });
});
