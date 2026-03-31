document.addEventListener('DOMContentLoaded', () => {
    console.log('Script loaded - Happy Homes backend ready');

    // ========================================
    // 0. FETCH ROOM VACANCIES (AJAX to bypass WAF)
    // ========================================
    const vacancyDisplays = document.querySelectorAll('.vacancy-display');
    if (vacancyDisplays.length > 0) {
        fetch('api_vacancies.php')
            .then(response => response.json())
            .then(data => {
                vacancyDisplays.forEach(display => {
                    const roomId = display.dataset.roomId;
                    if (data[roomId]) {
                        const info = data[roomId];
                        const btn = display.nextElementSibling.tagName === 'BUTTON'
                            ? display.nextElementSibling
                            : display.nextElementSibling.querySelector('button');

                        if (info.sold_out) {
                            display.textContent = 'Sold Out';
                            display.style.color = '#dc2626';
                            if (btn) {
                                btn.textContent = 'Unavailable';
                                btn.disabled = true;
                                btn.style.background = '#ccc';
                                btn.style.cursor = 'not-allowed';
                            }
                            // also dim the card slightly
                            const card = display.closest('.room-card');
                            if (card) card.classList.add('sold-out');
                        } else {
                            display.textContent = info.available + ' room(s) available';
                            display.style.color = '#16a34a';
                        }
                    }
                });
            })
            .catch(error => console.error('Error fetching vacancies:', error));
    }

    // ========================================
    // 1. BOOK NOW BUTTONS (PHP Backend)
    // ========================================
    const bookBtns = document.querySelectorAll('.book-btn:not(a button)');
    bookBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();

            // Get room name from data-room OR h3 text
            let roomName = btn.dataset.room;
            if (!roomName) {
                const roomCard = btn.closest('.room-card');
                roomName = roomCard ? roomCard.querySelector('h3').textContent.trim() : 'Deluxe Room';
            }

            console.log('Booking room:', roomName);

            // Redirect to booking page with room name
            const url = `booking.php?room=${encodeURIComponent(roomName)}`;
            window.location.href = url;
        });
    });

    // ========================================
    // 2. SMOOTH SCROLL NAVIGATION
    // ========================================
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // ========================================
    // 3. ACTIVE NAV LINK HIGHLIGHT
    // ========================================
    const currentPage = window.location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('.nav-links a');
    navLinks.forEach(link => {
        if (link.getAttribute('href') === currentPage ||
            link.getAttribute('href') === currentPage.replace('.php', '')) {
            link.style.color = '#38bdf8';
            link.style.fontWeight = '600';
        }
    });

    // ========================================
    // 4. DATE PICKER ENHANCEMENTS (Booking page)
    // ========================================
    const checkinInput = document.querySelector('input[name="check_in"]');
    const checkoutInput = document.querySelector('input[name="check_out"]');

    if (checkinInput) {
        checkinInput.min = new Date().toISOString().split('T')[0];
        checkinInput.addEventListener('change', function () {
            const checkin = new Date(this.value);
            const tomorrow = new Date(checkin);
            tomorrow.setDate(checkin.getDate() + 1);
            checkoutInput.min = tomorrow.toISOString().split('T')[0];
        });
    }

    // ========================================
    // 5. FORM VALIDATION ENHANCEMENTS
    // ========================================
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function (e) {
            const requiredInputs = this.querySelectorAll('[required]');
            let isValid = true;

            requiredInputs.forEach(input => {
                if (!input.value.trim()) {
                    isValid = false;
                    input.style.borderColor = '#ef4444';
                } else {
                    input.style.borderColor = '#e2e8f0';
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Please fill all required fields');
            }
        });
    });

    // ========================================
    // 6. MOBILE NAVBAR TOGGLE
    // ========================================
    const menuToggle = document.getElementById('mobile-menu');
    const navLinksList = document.querySelector('.nav-links');

    if (menuToggle && navLinksList) {
        menuToggle.addEventListener('click', () => {
            navLinksList.classList.toggle('active');

            // Toggle between bars and X icon
            const icon = menuToggle.querySelector('i');
            if (icon.classList.contains('fa-bars')) {
                icon.classList.replace('fa-bars', 'fa-xmark');
            } else {
                icon.classList.replace('fa-xmark', 'fa-bars');
            }
        });

        // Close menu when a link is clicked
        navLinksList.querySelectorAll('a').forEach(link => {
            link.addEventListener('click', () => {
                navLinksList.classList.remove('active');
                const icon = menuToggle.querySelector('i');
                if (icon) icon.classList.replace('fa-xmark', 'fa-bars');
            });
        });
    }

    // ========================================
    // 7. LOADING ANIMATIONS
    // ========================================
    const buttons = document.querySelectorAll('button');
    buttons.forEach(btn => {
        btn.addEventListener('click', function () {
            this.style.transform = 'scale(0.95)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 150);
        });
    });

    console.log('All features loaded successfully');
});