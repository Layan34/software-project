document.addEventListener("DOMContentLoaded", function () {
    const bookForm = document.getElementById("bookForm");
    const booksList = document.getElementById("booksList");
    const addBookBtn = document.getElementById("addBookBtn");
    const deleteAllBtn = document.getElementById("deleteAllBtn");
    const imageInput = document.getElementById("bookImage");
    const previewImage = document.getElementById("previewImage");
    const imageText = document.getElementById("imageText");

    // Create alert element
    function showAlert(message) {
        let alertBox = document.createElement("div");
        alertBox.classList.add("alert");
        alertBox.innerHTML = `✅ ${message}`;
        document.body.appendChild(alertBox);

        setTimeout(() => {
            alertBox.classList.add("show");
        }, 100);

        setTimeout(() => {
            alertBox.classList.remove("show");
            setTimeout(() => alertBox.remove(), 500);
        }, 3000);
    }

    // Display books if "My Books" page is open
    if (booksList) {
        displayBooks();
    }

    // Update preview image when a new image is selected
    if (imageInput) {
        imageInput.addEventListener("change", function (event) {
            const file = event.target.files[0];

            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImage.src = e.target.result;
                    imageText.textContent = "Image Selected";
                };
                reader.readAsDataURL(file);
            }
        });
    }

    // ✅ Add a new book to MyBooks and Dashboard
    if (bookForm) {
        bookForm.addEventListener("submit", function (event) {
            event.preventDefault();
            
            const title = document.getElementById("bookTitle").value.trim();
            const description = document.getElementById("bookDescription").value.trim();
            const price = document.getElementById("bookPrice").value.trim();
            const imageFile = imageInput.files[0];

            if (title && description && price && imageFile) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    addBookToStorage(title, description, price, e.target.result);
                    showAlert("Book added successfully!");
                    setTimeout(() => {
                        window.location.href = "MyBooks.html";
                    }, 1500);
                };
                reader.readAsDataURL(imageFile);
            } else {
                showAlert("⚠️ Please fill in all fields and upload an image.");
            }
        });
    }

    // ✅ Save books in Local Storage and update Dashboard with redirection
    function addBookToStorage(title, description, price, imageSrc) {
        let books = JSON.parse(localStorage.getItem("books")) || [];
        books.push({ title, description, price, imageSrc });
        localStorage.setItem("books", JSON.stringify(books));

        // 🔹 Update Dashboard
        localStorage.setItem("updateDashboard", Date.now());

        // ✅ Show success notification
        showAlert("Book added successfully!");

        // ✅ Redirect to MyBooks.html after 1.5 seconds
        setTimeout(() => {
            window.location.href = "MyBooks.html";
        }, 1500);
    }

    // ✅ Display stored books
    function displayBooks() {
        booksList.innerHTML = "";
        let books = JSON.parse(localStorage.getItem("books")) || [];

        if (books.length === 0) {
            booksList.innerHTML = "<p>No books added yet.</p>";
            return;
        }

        books.forEach((book, index) => {
            const bookItem = document.createElement("div");
            bookItem.classList.add("book-item");

            bookItem.innerHTML = `
                <img src="${book.imageSrc}" alt="${book.title}">
                <div class="book-details">
                    <div class="book-title">${book.title}</div>
                    <div class="book-description">${book.description}</div>
                    <div class="book-price"> ${book.price} SAR</div>
                </div>
                <button class="edit-btn" onclick="editBook(${index})">Edit</button>
                <button class="delete-btn" onclick="deleteBook(${index})">Delete</button>
            `;
            booksList.appendChild(bookItem);
        });
    }

    // ✅ Edit book details
    window.editBook = function (index) {
        let books = JSON.parse(localStorage.getItem("books")) || [];
        let book = books[index];

        const newTitle = prompt("Enter new book title:", book.title);
        if (newTitle) book.title = newTitle;

        const newDescription = prompt("Enter new book description:", book.description);
        if (newDescription) book.description = newDescription;

        const newPrice = prompt("Enter new book price:", book.price);
        if (newPrice) book.price = newPrice;

        books[index] = book;
        localStorage.setItem("books", JSON.stringify(books));

        // 🔹 Update Dashboard after editing
        localStorage.setItem("updateDashboard", Date.now());

        displayBooks();
        showAlert(`"${book.title}" updated successfully!`);
    };

    // ✅ Delete a book and update Dashboard
    window.deleteBook = function (index) {
        let books = JSON.parse(localStorage.getItem("books")) || [];
        let bookTitle = books[index].title;

        let confirmation = confirm(`Are you sure you want to delete "${bookTitle}"?`);
        if (!confirmation) return;

        books.splice(index, 1);
        localStorage.setItem("books", JSON.stringify(books));

        // 🔹 Update Dashboard after deletion
        localStorage.setItem("updateDashboard", Date.now());

        displayBooks();
        showAlert(`"${bookTitle}" deleted successfully!`);
    };

    // ✅ Delete all books and update Dashboard
    if (deleteAllBtn) {
        deleteAllBtn.addEventListener("click", function () {
            if (confirm("Are you sure you want to delete all books?")) {
                localStorage.removeItem("books");

                // 🔹 Update Dashboard after bulk deletion
                localStorage.setItem("updateDashboard", Date.now());

                displayBooks();
                showAlert("All books deleted!");
            }
        });
    }

    // ✅ Add new book button action
    if (addBookBtn) {
        addBookBtn.addEventListener("click", function () {
            window.location.href = "AddMyBook.html";
        });
    }
});
