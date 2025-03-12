document.addEventListener("DOMContentLoaded", function () {	
    const requestSection = document.querySelector(".books-container:nth-of-type(1) .title");
    const historySection = document.querySelector(".books-container:nth-of-type(2) .title");

    requestSection.addEventListener("click", function (event) {
        if (event.target.tagName === "BUTTON") {
            const bookCard = event.target.closest(".book-card");
            if (!bookCard) return;

            const action = event.target.textContent;
            const confirmation = action === "Accept" 
                ? confirm("Are you sure you want to accept this exchange request?") 
                : confirm("Are you sure you want to decline this exchange request?");

            if (!confirmation) return; // Stop if the user cancels the action

            bookCard.querySelectorAll("button").forEach(button => button.remove());

            const statusButton = document.createElement("button");
            statusButton.textContent = action === "Accept" ? "Accepted" : "Declined";
            statusButton.classList.add("state");
			statusButton.disabled = true;
            bookCard.appendChild(statusButton);

            historySection.appendChild(bookCard);
        }
    });
});