
  const toggleButton = document.getElementById("toggleSidebar");
  const closeButton = document.getElementById("closeSidebar");
  const sidebar = document.getElementById("sidebar");

  toggleButton.addEventListener("click", () => {
    sidebar.classList.add("open");
  });

  closeButton.addEventListener("click", () => {
    sidebar.classList.remove("open");
  });
