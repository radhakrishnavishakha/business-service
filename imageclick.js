
document.addEventListener("DOMContentLoaded", function () {
    function searchWorkers(searchQuery) {
        let resultsDiv = document.getElementById("worker-results");
        let searchInput = document.getElementById("worker-search");

        if (searchQuery.length > 1) {
            // Update the input field with the search term
            searchInput.value = searchQuery;

            // Perform AJAX fetch request to get worker data
            fetch("find-worker.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: "search=" + encodeURIComponent(searchQuery)
            })
            .then(response => response.text())
            .then(data => {
                // Display the result inside the worker-results div
                resultsDiv.innerHTML = data;
            })
            .catch(error => console.error("Error:", error));
        } else {
            resultsDiv.innerHTML = "<p>Please enter a valid worker type.</p>";
        }
    }

    // Handle click event on service links
    document.querySelectorAll(".service-link").forEach(link => {
        link.addEventListener("click", function (event) {
            event.preventDefault(); // Prevent default behavior

            // Get category from data attribute
            let category = link.getAttribute("data-category");

            // Check if we're on find-worker.php or index.php
            if (document.getElementById("worker-search")) {
                // If search bar exists (we're in find-worker.php), update the search bar
                searchWorkers(category);
            } else {
                // If search bar does NOT exist (we're in index.php), redirect to find-worker.php with search parameter
                window.location.href = "find-worker.php?search=" + encodeURIComponent(category);
            }
        });
    });

    // Handle form submission in find-worker.php
    let searchForm = document.getElementById("worker-search-form");
    if (searchForm) {
        searchForm.addEventListener("submit", function (event) {
            event.preventDefault(); // Prevent normal form submission

            let searchQuery = document.getElementById("worker-search").value.trim();
            if (searchQuery) {
                searchWorkers(searchQuery);
            }
        });
    }

    // Auto-trigger search if `search` parameter exists in URL (for index.php or report.php redirection)
    const urlParams = new URLSearchParams(window.location.search);
    const searchParam = urlParams.get("search");
    if (searchParam && document.getElementById("worker-search")) {
        searchWorkers(searchParam);
    }
});
